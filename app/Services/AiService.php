<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatHistory;
use App\Models\Task;
use App\Models\Category;
use Carbon\Carbon;

class AiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY is not set in environment variables');
        }
    }

    /**
     * Generate a chat response using Gemini API
     */
    public function chat(string $userMessage, string $sessionId, string $mode = 'inquiry'): array
    {
        try {
            // Store user message in chat history
            ChatHistory::create([
                'session_id' => $sessionId,
                'role' => 'user',
                'message' => $userMessage,
                'type' => $mode,
            ]);

            // Get recent conversation history (last 10 messages)
            $history = ChatHistory::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->reverse()
                ->values();

            // Build context from database
            $context = $this->buildContext();

            // Build conversation history for context
            $conversationHistory = [];
            foreach ($history as $msg) {
                if ($msg->role === 'user') {
                    $conversationHistory[] = [
                        'role' => 'user',
                        'parts' => [['text' => $msg->message]]
                    ];
                } else {
                    $conversationHistory[] = [
                        'role' => 'model',
                        'parts' => [['text' => $msg->message]]
                    ];
                }
            }

            // Build the system prompt
            $systemPrompt = $this->buildSystemPrompt($mode);

            // Prepare request payload
            $payload = [
                'contents' => $conversationHistory,
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt . "\n\n" . $context]]
                ]
            ];

            // Make API request to Gemini
            $response = Http::timeout(30)
                ->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

            if (!$response->successful()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();

            // Extract the response text
            $assistantMessage = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process that request.';

            // Store assistant response
            ChatHistory::create([
                'session_id' => $sessionId,
                'role' => 'assistant',
                'message' => $assistantMessage,
                'type' => $mode,
            ]);

            return [
                'success' => true,
                'message' => $assistantMessage,
                'type' => $mode,
            ];
        } catch (\Exception $e) {
            \Log::error('AI Service Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the system prompt based on mode
     */
    private function buildSystemPrompt(string $mode): string
    {
        if ($mode === 'crud') {
            return <<<'PROMPT'
You are an intelligent task management assistant. You help users manage their to-do list by answering questions about tasks and performing CRUD operations.

IMPORTANT RULES:
1. Always be helpful and clear in your responses.
2. When a user wants to CREATE a task, extract the title, description (optional), due_date (optional), status (default: pending), priority (default: medium), and category_id.
3. When a user wants to UPDATE a task, ask for clarification if needed or infer from context.
4. When a user wants to DELETE tasks, ALWAYS ask for confirmation first. Show what will be deleted.
5. For READ operations, provide clear, formatted responses.
6. Use the conversation history to understand context (e.g., "it" refers to previously mentioned tasks).
7. Format task information clearly with: ID, Title, Status, Priority, Category, Due Date.
8. Be conversational and helpful.

When suggesting to perform an action, format your response as:
- For CREATE: "I'll create a task: [details]. Is this correct?"
- For UPDATE: "I'll update [task]: [changes]. Confirm?"
- For DELETE: "I'll delete the following: [list]. Are you sure?"
- For READ: "Here are your tasks: [formatted list]"

Always maintain context from previous messages in the conversation.
PROMPT;
        }

        return <<<'PROMPT'
You are a helpful task management chatbot. You answer questions about tasks and help users understand their to-do list.

IMPORTANT RULES:
1. Answer questions about tasks, categories, due dates, priority levels, and completion status.
2. Provide clear, formatted responses with relevant information.
3. Use conversation history to understand context (e.g., "it" refers to previously mentioned tasks).
4. Format task information clearly with: Title, Status, Priority, Category, Due Date.
5. Be conversational and helpful.
6. If a query seems unclear, ask clarifying questions.
7. Do not hallucinate data - only reference tasks that exist in the database.

Example response format:
"Here are your tasks: [formatted list]"
"The most important tasks are: [list]"
"You have [number] pending tasks in [category]"
PROMPT;
    }

    /**
     * Build database context for the AI
     */
    private function buildContext(): string
    {
        // Get all tasks with categories
        $tasks = Task::with('category')->get();
        $categories = Category::all();

        $tasksSummary = "CURRENT TASKS IN DATABASE:\n";
        $tasksSummary .= "Total Tasks: " . $tasks->count() . "\n\n";

        // Group by status
        $byStatus = $tasks->groupBy('status');
        foreach ($byStatus as $status => $statusTasks) {
            $tasksSummary .= "### $status (" . $statusTasks->count() . " tasks)\n";
            foreach ($statusTasks as $task) {
                $dueDate = $task->due_date ? $task->due_date->format('M d, Y') : 'No due date';
                $categoryName = $task->category ? $task->category->name : 'None';
                $tasksSummary .= "- ID: {$task->id}, Title: {$task->title}, Priority: {$task->priority}, Category: {$categoryName}, Due: {$dueDate}\n";
            }
            $tasksSummary .= "\n";
        }

        $categoriesSummary = "AVAILABLE CATEGORIES:\n";
        foreach ($categories as $cat) {
            $taskCount = $cat->tasks()->count();
            $categoriesSummary .= "- {$cat->name}: {$taskCount} tasks - {$cat->description}\n";
        }

        return $tasksSummary . "\n" . $categoriesSummary;
    }

    /**
     * Get recent chat history for a session
     */
    public function getChatHistory(string $sessionId, int $limit = 10): array
    {
        return ChatHistory::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Clear chat history for a session
     */
    public function clearChatHistory(string $sessionId): bool
    {
        return ChatHistory::where('session_id', $sessionId)->delete() > 0;
    }
}
