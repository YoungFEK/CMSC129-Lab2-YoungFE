@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-6">📋 My Tasks</h1>

        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('tasks.index') }}" class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or description..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full text-white px-4 py-2 rounded-lg font-semibold">
                        🔍 Filter
                    </button>
                    <a href="{{ route('tasks.index') }}" class="w-full text-center bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-400">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Tasks Table -->
        @if($tasks->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Title</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Category</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Priority</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Due Date</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-purple-600 font-semibold hover:underline">
                                        {{ $task->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $task->category->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="badge-{{ $task->status }} px-3 py-1 rounded-full text-white text-sm font-semibold">
                                        {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="badge-{{ $task->priority }} px-3 py-1 rounded-full text-white text-sm font-semibold">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="action-group" role="group" aria-label="Task actions">
                                        <a href="{{ route('tasks.show', $task) }}" class="action-icon action-view" title="View task" aria-label="View {{ $task->title }}">📄</a>
                                        <a href="{{ route('tasks.edit', $task) }}" class="action-icon action-edit" title="Edit task" aria-label="Edit {{ $task->title }}">✏️</a>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline" data-confirm data-confirm-title="Move task to trash?" data-confirm-message="This task will be moved to the trash and can still be restored later." data-confirm-button="Move to Trash">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon action-delete" title="Delete task" aria-label="Delete {{ $task->title }}">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tasks->appends(request()->query())->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No tasks found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your search or filter criteria.</p>
                <a href="{{ route('tasks.create') }}" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                    Create Your First Task
                </a>
            </div>
        @endif
    </div>

    <!-- AI Chat Widget -->
    <x-chat-widget />
@endsection
