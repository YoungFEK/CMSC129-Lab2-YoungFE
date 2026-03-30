@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-4xl font-bold text-gray-800">{{ $task->title }}</h1>
                <div class="flex gap-2">
                    <a href="{{ route('tasks.edit', $task) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-yellow-600">
                        ✏️ Edit
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" style="display:inline;" onsubmit="return confirm('Move this task to trash?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger text-white px-4 py-2 rounded-lg font-semibold">
                            🗑️ Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Metadata -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Category</p>
                    <p class="text-lg text-gray-800">{{ $task->category->name ?? 'Uncategorized' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Status</p>
                    <span class="badge-{{ $task->status }} px-3 py-1 rounded-full text-white inline-block">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Priority</p>
                    <span class="badge-{{ $task->priority }} px-3 py-1 rounded-full text-white inline-block">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Due Date</p>
                    <p class="text-lg text-gray-800">{{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}</p>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-700 mb-3">Description</h2>
                <p class="text-gray-700 leading-relaxed">{{ $task->description ?? 'No description provided.' }}</p>
            </div>

            <!-- Timestamps -->
            <div class="border-t pt-4 text-sm text-gray-500">
                <p>Created: {{ $task->created_at->format('M d, Y g:i A') }}</p>
                <p>Last Updated: {{ $task->updated_at->format('M d, Y g:i A') }}</p>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center">
            <a href="{{ route('tasks.index') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                ← Back to All Tasks
            </a>
        </div>
    </div>
@endsection
