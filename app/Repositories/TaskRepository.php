<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\Category;
use Carbon\Carbon;

class TaskRepository
{
    // ── Task Queries ─────────────────────────────────────

    public function getAll(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Task::with('category');

        if (!empty($filters['status']))
            $query->where('status', $filters['status']);

        if (!empty($filters['priority']))
            $query->where('priority', $filters['priority']);

        if (!empty($filters['category_id']))
            $query->where('category_id', $filters['category_id']);

        if (!empty($filters['search']))
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });

        if (!empty($filters['due_today']))
            $query->whereDate('due_date', Carbon::today());

        if (!empty($filters['overdue']))
            $query->whereDate('due_date', '<', Carbon::today())
                  ->where('status', '!=', 'done');

        return $query->orderByDesc('created_at')->get();
    }

    public function findById(int $id): ?Task
    {
        return Task::with('category')->find($id);
    }

    public function findByIdWithTrashed(int $id): ?Task
    {
        return Task::withTrashed()->findOrFail($id);
    }

    public function getPaginated(array $filters = [], int $perPage = 10)
    {
        return Task::query()
            ->search($filters['search'] ?? null)
            ->byStatus($filters['status'] ?? null)
            ->byCategory($filters['category'] ?? null)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getTrashed(int $perPage = 10)
    {
        return Task::onlyTrashed()
            ->with('category')
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total'        => Task::count(),
            'pending'      => Task::where('status', 'pending')->count(),
            'in_progress'  => Task::where('status', 'in_progress')->count(),
            'done'         => Task::where('status', 'done')->count(),
            'high_priority'=> Task::where('priority', 'high')->count(),
            'overdue'      => Task::whereDate('due_date', '<', Carbon::today())
                                  ->where('status', '!=', 'done')->count(),
            'due_today'    => Task::whereDate('due_date', Carbon::today())->count(),
        ];
    }

    public function create(array $data): Task
    {
        $task = Task::create($data);
        $task->load('category');
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        $task->load('category');
        return $task;
    }

    public function softDelete(Task $task): void
    {
        $task->delete();
    }

    public function bulkSoftDelete(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        $tasks = Task::whereIn('id', $ids)->get();
        Task::whereIn('id', $ids)->delete();
        return $tasks;
    }

    public function restore(int $id): Task
    {
        $task = $this->findByIdWithTrashed($id);
        $task->restore();
        return $task;
    }

    public function forceDelete(int $id): void
    {
        $task = $this->findByIdWithTrashed($id);
        $task->forceDelete();
    }

    // ── Category Queries ──────────────────────────────────

    public function getAllCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::all();
    }

    public function getCategoriesWithTaskCount(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount('tasks')->get();
    }

    public function findCategoryByName(string $name): ?Category
    {
        return Category::where('name', 'like', "%{$name}%")->first();
    }
}
