<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private readonly TaskService    $taskService,
        private readonly TaskRepository $taskRepository
    ) {
        $this->apiKey = env('GROQ_API_KEY', '');
        $this->model  = env('GROQ_MODEL', 'llama-3.3-70b-versatile');
    }

    // ── Public Methods (called by ChatController) ─────────

    public function handleMessage(string $userMessage, array $history, string $mode = 'chatbot'): array
    {
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->buildSystemPrompt($mode)]],
            array_slice($history, -10),
            [['role' => 'user', 'content' => $userMessage]]
        );

        $response = $this->callGroq($messages, $this->getToolDefinitions($mode));

        if (!$response) {
            return ['error' => 'AI service unavailable. Please try again.'];
        }

        $choice  = $response['choices'][0];
        $message = $choice['message'];

        if (!empty($message['tool_calls'])) {
            return $this->handleToolCalls($message, $messages);
        }

        return [
            'reply'       => $message['content'],
            'tool_calls'  => null,
            'task_update' => false,
        ];
    }

    public function handleConfirmedActions(array $confirmations, array $history, string $mode = 'chatbot'): array
    {
        $toolResults = [];

        foreach ($confirmations as $confirmation) {
            $result = $this->executeTool($confirmation['function'], $confirmation['args']);

            $toolResults[] = [
                'role'         => 'tool',
                'tool_call_id' => $confirmation['tool_call_id'],
                'content'      => json_encode($result),
            ];
        }

        $summaryMessages = array_merge(
            [['role' => 'system', 'content' => $this->buildSystemPrompt($mode)]],
            array_slice($history, -6),
            $toolResults,
            [['role' => 'user', 'content' => 'Please summarize what was just completed in a friendly way.']]
        );

        $summary = $this->callGroq($summaryMessages, null);

        if (!isset($summary['choices'][0]['message']['content'])) {
            $reply = $this->buildToolResultSummary($toolResults);
        } else {
            $reply = $summary['choices'][0]['message']['content'];
        }

        return [
            'reply'       => $reply,
            'task_update' => true,
        ];
    }

    // ── Tool Call Handler ─────────────────────────────────

    private function handleToolCalls(array $assistantMessage, array $messages): array
    {
        $toolCalls            = $assistantMessage['tool_calls'];
        $destructiveOps       = ['update_task', 'delete_task', 'bulk_delete_tasks'];
        $pendingConfirmations = [];

        foreach ($toolCalls as $toolCall) {
            $funcName = $toolCall['function']['name'];
            $args     = json_decode($toolCall['function']['arguments'], true) ?? [];

            if (in_array($funcName, $destructiveOps)) {
                $pendingConfirmations[] = [
                    'tool_call_id' => $toolCall['id'],
                    'function'     => $funcName,
                    'args'         => $args,
                    'description'  => $this->describeAction($funcName, $args),
                ];
            }
        }

        if (!empty($pendingConfirmations)) {
            return [
                'reply'                 => $this->buildConfirmationMessage($pendingConfirmations),
                'pending_confirmations' => $pendingConfirmations,
                'task_update'           => false,
                'awaiting_confirmation' => true,
            ];
        }

        // Execute non-destructive tools
        $toolResults = [];
        $taskUpdate  = false;

        foreach ($toolCalls as $toolCall) {
            $funcName = $toolCall['function']['name'];
            $args     = json_decode($toolCall['function']['arguments'], true) ?? [];
            $result   = $this->executeTool($funcName, $args);

            $toolResults[] = [
                'role'         => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content'      => json_encode($result),
            ];

            if ($funcName === 'create_task') {
                $taskUpdate = true;
            }
        }

        $messagesWithResults = array_merge(
            $messages,
            [['role' => 'assistant', 'content' => null, 'tool_calls' => $toolCalls]],
            $toolResults
        );

        $finalResponse = $this->callGroq($messagesWithResults, null);
        $finalText     = $finalResponse['choices'][0]['message']['content'] ?? 'Done!';

        return [
            'reply'       => $finalText,
            'tool_calls'  => array_map(fn($tc) => $tc['function']['name'], $toolCalls),
            'task_update' => $taskUpdate,
        ];
    }

    // ── Tool Executor ─────────────────────────────────────

    private function executeTool(string $name, array $args): array
    {
        try {
            return match($name) {
                'get_tasks'         => $this->taskService->getTasksForAI($args),
                'get_task_stats'    => $this->taskService->getStats(),
                'get_categories'    => $this->taskService->getCategoriesForAI(),
                'create_task'       => $this->taskService->createTaskFromAI($args),
                'update_task'       => $this->taskService->updateTaskFromAI($args),
                'delete_task'       => $this->taskService->deleteTaskFromAI($args['id']),
                'bulk_delete_tasks' => $this->taskService->bulkDeleteTasksFromAI($args['ids']),
                default             => ['error' => "Unknown tool: {$name}"],
            };
        } catch (\Exception $e) {
            Log::error("Tool execution error: {$name}", ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    // ── Groq API ──────────────────────────────────────────

    private function callGroq(array $messages, ?array $tools): ?array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.3,
        ];

        if ($tools) {
            $payload['tools']       = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type'  => 'application/json',
        ])->timeout(30)->post($this->apiUrl, $payload);

        if ($response->failed()) {
            Log::error('Groq API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    // ── System Prompt ─────────────────────────────────────

    private function buildSystemPrompt(string $mode = 'chatbot'): string
    {
        $today      = Carbon::today()->format('Y-m-d');
        $categories = $this->taskRepository->getAllCategories();
        $catList    = $categories->map(fn($c) => "{$c->name} (id:{$c->id})")->implode(', ');

        if ($mode === 'assistant') {
            return <<<PROMPT
            You are a task assistant for a Laravel To-Do app. You may only perform CRUD operations.
            Today's date is {$today}.

            ## Database Schema
            - Tasks: id, title, description, due_date (YYYY-MM-DD), status (pending|in_progress|done), priority (low|medium|high), category_id
            - Categories: id, name — Available: {$catList}

            ## Your Capabilities
            - You can create tasks with create_task.
            - You can update tasks with update_task (identify the task by id or task_title).
            - You can delete tasks with delete_task or bulk_delete_tasks.

            ## Rules
            1. Use ONLY the provided CRUD tools: create_task, update_task, delete_task, bulk_delete_tasks.
            2. Do NOT provide task summaries, statistics, category listings, or search results.
            3. If the user asks for anything other than create/update/delete, reply: "Use Chatbot mode for summaries, search, and task queries."
            4. Ask only for missing fields needed to complete the task operation.
            5. Never expose raw database errors to the user.
            6. Be concise and courteous.
            PROMPT;
        }

        return <<<PROMPT
        You are a helpful task management assistant for a Laravel To-Do app.
        Today's date is {$today}.

        ## Database Schema
        - Tasks: id, title, description, due_date (YYYY-MM-DD), status (pending|in_progress|done), priority (low|medium|high), category_id
        - Categories: id, name — Available: {$catList}

        ## Your Capabilities
        You can QUERY and MODIFY tasks using the provided tools.
        - For questions/inquiries: use get_tasks, get_task_stats, or get_categories
        - For creating: use create_task
        - For updating: use update_task (requires task id)
        - For deleting: use delete_task or bulk_delete_tasks

        ## Rules
        1. ALWAYS use tools to get real data — never make up task information.
        2. UPDATE and DELETE operations require user confirmation. The system handles this automatically.
        3. When a user says "tomorrow", calculate from today ({$today}).
        4. When listing tasks, format them neatly with their ID, title, status, and priority.
        5. If a user asks a follow-up like "which of those are high priority?", filter from previous context.
        6. Be concise but friendly. Use emojis sparingly (✅ ⚠️ 📋).
        7. If you need a task ID to update/delete but don't have it, call get_tasks first.
        8. Never expose raw database errors to the user.
        PROMPT;
    }

    // ── Helpers ───────────────────────────────────────────

    private function describeAction(string $funcName, array $args): string
    {
        return match($funcName) {
            'update_task'       => "Update task ID {$args['id']}: " . json_encode(array_diff_key($args, ['id' => ''])),
            'delete_task'       => "Delete task ID {$args['id']}",
            'bulk_delete_tasks' => "Delete " . count($args['ids']) . " tasks (IDs: " . implode(', ', $args['ids']) . ")",
            default             => $funcName,
        };
    }

    private function buildToolResultSummary(array $toolResults): string
    {
        $lines = [];

        foreach ($toolResults as $toolResult) {
            $payload = json_decode($toolResult['content'], true);

            if (is_array($payload) && isset($payload['message'])) {
                $lines[] = $payload['message'];
            } elseif (is_array($payload) && isset($payload['error'])) {
                $lines[] = 'Error: ' . $payload['error'];
            } else {
                $lines[] = 'The requested operation completed successfully.';
            }
        }

        return implode("\n", $lines);
    }

    private function buildConfirmationMessage(array $confirmations): string
    {
        $lines = ["⚠️ I need your confirmation before proceeding:\n"];
        foreach ($confirmations as $i => $c) {
            $lines[] = ($i + 1) . ". " . $c['description'];
        }
        $lines[] = "\nReply **Yes, confirm** to proceed or **No, cancel** to abort.";
        return implode("\n", $lines);
    }

    // ── Tool Definitions ──────────────────────────────────

    private function getToolDefinitions(string $mode = 'chatbot'): array
    {
        $crudTools = [
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'create_task',
                    'description' => 'Create a new task.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'title'         => ['type' => 'string',  'description' => 'Task title (required)'],
                            'description'   => ['type' => 'string',  'description' => 'Task details'],
                            'due_date'      => ['type' => 'string',  'description' => 'Due date as YYYY-MM-DD'],
                            'status'        => ['type' => 'string',  'enum' => ['pending', 'in_progress', 'done']],
                            'priority'      => ['type' => 'string',  'enum' => ['low', 'medium', 'high']],
                            'category_id'   => ['type' => 'integer', 'description' => 'Category ID'],
                            'category_name' => ['type' => 'string',  'description' => 'Category name (alternative to ID)'],
                        ],
                        'required' => ['title'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'update_task',
                    'description' => 'Update an existing task by ID or task_title. Only provide fields to change.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'id'            => ['type' => 'integer', 'description' => 'Task ID to update'],
                            'task_title'    => ['type' => 'string',  'description' => 'Task title to identify the task when ID is not provided'],
                            'title'         => ['type' => 'string',  'description' => 'New title for the task'],
                            'description'   => ['type' => 'string'],
                            'due_date'      => ['type' => 'string',  'description' => 'YYYY-MM-DD'],
                            'status'        => ['type' => 'string',  'enum' => ['pending', 'in_progress', 'done']],
                            'priority'      => ['type' => 'string',  'enum' => ['low', 'medium', 'high']],
                            'category_id'   => ['type' => 'integer'],
                            'category_name' => ['type' => 'string'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'delete_task',
                    'description' => 'Soft-delete (move to trash) a single task by ID.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'Task ID to delete'],
                        ],
                        'required' => ['id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'bulk_delete_tasks',
                    'description' => 'Soft-delete multiple tasks at once.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'ids' => [
                                'type'  => 'array',
                                'items' => ['type' => 'integer'],
                                'description' => 'Array of task IDs to delete',
                            ],
                        ],
                        'required' => ['ids'],
                    ],
                ],
            ],
        ];

        if ($mode === 'assistant') {
            return $crudTools;
        }

        return array_merge($crudTools, [
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_tasks',
                    'description' => 'Retrieve tasks from the database with optional filters.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'status'      => ['type' => 'string', 'enum' => ['pending', 'in_progress', 'done']],
                            'priority'    => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                            'category_id' => ['type' => 'integer', 'description' => 'Filter by category ID'],
                            'search'      => ['type' => 'string',  'description' => 'Search in title/description'],
                            'due_today'   => ['type' => 'boolean', 'description' => 'Only tasks due today'],
                            'overdue'     => ['type' => 'boolean', 'description' => 'Only overdue incomplete tasks'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_task_stats',
                    'description' => 'Get summary statistics: total, counts by status/priority, overdue, due today.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_categories',
                    'description' => 'Get all available categories with task counts.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
            ],
        ]);
    }
}
