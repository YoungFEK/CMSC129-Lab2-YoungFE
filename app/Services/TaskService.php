<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Category;
use App\Repositories\TaskRepository;
use Carbon\Carbon;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    ) {}

    // ── Used by TaskController (web) ──────────────────────

    public function getPaginatedTasks(array $filters): object
    {
        return $this->taskRepository->getPaginated($filters);
    }

    public function createTask(array $data): Task
    {
        return $this->taskRepository->create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        return $this->taskRepository->update($task, $data);
    }

    public function deleteTask(Task $task): void
    {
        $this->taskRepository->softDelete($task);
    }

    public function restoreTask(int $id): Task
    {
        return $this->taskRepository->restore($id);
    }

    public function forceDeleteTask(int $id): void
    {
        $this->taskRepository->forceDelete($id);
    }

    public function getTrashedTasks(): object
    {
        return $this->taskRepository->getTrashed();
    }

    public function getAllCategories()
    {
        return $this->taskRepository->getAllCategories();
    }

    // ── Used by ChatService (AI) ──────────────────────────

    public function getTasksForAI(array $filters = []): array
    {
        $tasks = $this->taskRepository->getAll($filters);

        return [
            'tasks' => $tasks->map(fn($t) => $this->formatTaskForAI($t))->toArray(),
            'count' => $tasks->count(),
        ];
    }

    public function getStats(): array
    {
        return $this->taskRepository->getStats();
    }

    public function getCategoriesForAI(): array
    {
        $categories = $this->taskRepository->getCategoriesWithTaskCount();

        return [
            'categories' => $categories->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'task_count' => $c->tasks_count,
            ])->toArray(),
        ];
    }

    public function createTaskFromAI(array $args): array
    {
        $categoryId = $this->resolveCategoryId($args);

        $task = $this->taskRepository->create([
            'title'       => $args['title'],
            'description' => $args['description'] ?? null,
            'due_date'    => !empty($args['due_date']) ? Carbon::parse($args['due_date']) : null,
            'status'      => $args['status']   ?? 'pending',
            'priority'    => $args['priority'] ?? 'medium',
            'category_id' => $categoryId,
        ]);

        return [
            'success' => true,
            'message' => "Task \"{$task->title}\" created successfully!",
            'task'    => $this->formatTaskForAI($task),
        ];
    }

    public function updateTaskFromAI(array $args): array
    {
        $task = null;

        if (!empty($args['id'])) {
            $task = $this->taskRepository->findById($args['id']);
        } elseif (!empty($args['task_title'])) {
            $task = $this->taskRepository->findByTitle($args['task_title']);
        }

        if (!$task) {
            $identifier = !empty($args['id']) ? "ID {$args['id']}" : "title '{$args['task_title']}'";
            return ['error' => "Task with {$identifier} not found."];
        }

        if (!empty($args['category_name']) && empty($args['category_id'])) {
            $args['category_id'] = $this->resolveCategoryId($args);
        }

        $updateData = array_filter([
            'title'       => $args['title']       ?? null,
            'description' => $args['description'] ?? null,
            'due_date'    => !empty($args['due_date']) ? Carbon::parse($args['due_date']) : null,
            'status'      => $args['status']      ?? null,
            'priority'    => $args['priority']    ?? null,
            'category_id' => $args['category_id'] ?? null,
        ], fn($v) => $v !== null);

        $task = $this->taskRepository->update($task, $updateData);

        return [
            'success' => true,
            'message' => "Task \"{$task->title}\" updated successfully!",
            'task'    => $this->formatTaskForAI($task),
        ];
    }

    public function deleteTaskFromAI(int $id): array
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return ['error' => "Task with ID {$id} not found."];
        }

        $title = $task->title;
        $this->taskRepository->softDelete($task);

        return [
            'success' => true,
            'message' => "Task \"{$title}\" moved to trash.",
        ];
    }

    public function bulkDeleteTasksFromAI(array $ids): array
    {
        $tasks  = $this->taskRepository->bulkSoftDelete($ids);
        $titles = $tasks->pluck('title')->toArray();

        return [
            'success'       => true,
            'message'       => 'Deleted ' . count($titles) . ' task(s): ' . implode(', ', $titles),
            'deleted_count' => count($titles),
        ];
    }

    // ── Private Helpers ───────────────────────────────────

    private function resolveCategoryId(array $args): ?int
    {
        if (!empty($args['category_id'])) {
            return $args['category_id'];
        }

        if (!empty($args['category_name'])) {
            $category = $this->taskRepository->findCategoryByName($args['category_name']);
            return $category?->id;
        }

        return null;
    }

    private function formatTaskForAI(Task $task): array
    {
        return [
            'id'          => $task->id,
            'title'       => $task->title,
            'description' => $task->description,
            'due_date'    => $task->due_date?->format('Y-m-d'),
            'status'      => $task->status,
            'priority'    => $task->priority,
            'category'    => $task->category?->name ?? 'None',
            'category_id' => $task->category_id,
        ];
    }
}
