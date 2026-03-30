<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks (with search/filter).
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $category = $request->get('category');

        $tasks = Task::query()
            ->search($search)
            ->byStatus($status)
            ->byCategory($category)
            ->with('category')
            ->latest('created_at')
            ->paginate(10);

        $categories = Category::all();
        $statuses = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Done'];

        return view('tasks.index', compact('tasks', 'categories', 'statuses', 'search', 'status', 'category'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $categories = Category::all();
        return view('tasks.create', compact('categories'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        Task::create($request->validated());

        return redirect()->route('tasks.index')
                        ->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $categories = Category::all();
        return view('tasks.edit', compact('task', 'categories'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return redirect()->route('tasks.show', $task)
                        ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task from storage (soft delete).
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
                        ->with('success', 'Task moved to trash.');
    }

    /**
     * Display all soft-deleted tasks (trash).
     */
    public function trash()
    {
        $tasks = Task::trashed()
                     ->with('category')
                     ->latest('deleted_at')
                     ->paginate(10);

        return view('tasks.trash', compact('tasks'));
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        return redirect()->route('tasks.index')
                        ->with('success', 'Task restored successfully!');
    }

    /**
     * Permanently delete a soft-deleted task.
     */
    public function forceDelete($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->forceDelete();

        return redirect()->route('tasks.trash')
                        ->with('success', 'Task permanently deleted!');
    }
}
