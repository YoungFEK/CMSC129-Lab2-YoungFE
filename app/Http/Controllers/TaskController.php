<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'category']);

        $tasks      = $this->taskService->getPaginatedTasks($filters);
        $categories = $this->taskService->getAllCategories();
        $statuses   = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Done'];

        return view('tasks.index', compact('tasks', 'categories', 'statuses') + $filters);
    }

    public function create()
    {
        $categories = $this->taskService->getAllCategories();
        return view('tasks.create', compact('categories'));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->taskService->createTask($request->validated());

        return redirect()->route('tasks.index')
                         ->with('success', 'Task created successfully!');
    }

    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $categories = $this->taskService->getAllCategories();
        return view('tasks.edit', compact('task', 'categories'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->taskService->updateTask($task, $request->validated());

        return redirect()->route('tasks.show', $task)
                         ->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $this->taskService->deleteTask($task);

        return redirect()->route('tasks.index')
                         ->with('success', 'Task moved to trash.');
    }

    public function trash()
    {
        $tasks = $this->taskService->getTrashedTasks();
        return view('tasks.trash', compact('tasks'));
    }

    public function restore($id)
    {
        $this->taskService->restoreTask($id);

        return redirect()->route('tasks.index')
                         ->with('success', 'Task restored successfully!');
    }

    public function forceDelete($id)
    {
        $this->taskService->forceDeleteTask($id);

        return redirect()->route('tasks.trash')
                         ->with('success', 'Task permanently deleted!');
    }
}
