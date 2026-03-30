@extends('layouts.app')

@section('title', 'Trash')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-6">🗑️ Trash</h1>

        @if($tasks->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Title</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Category</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Deleted At</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="border-b hover:bg-gray-50 transition opacity-75">
                                <td class="px-6 py-4 text-gray-700">{{ $task->title }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $task->category->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $task->deleted_at->format('M d, Y g:i A') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="action-group" role="group" aria-label="Trash actions">
                                        <a href="{{ route('tasks.restore', $task->id) }}" class="action-icon action-restore" title="Restore task" aria-label="Restore {{ $task->title }}">♻️</a>
                                        <form method="POST" action="{{ route('tasks.forceDelete', $task->id) }}" class="inline" data-confirm data-confirm-title="Permanently delete task?" data-confirm-message="This action cannot be undone. The task will be removed forever." data-confirm-button="Delete Permanently">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon action-force-delete" title="Permanent delete" aria-label="Permanently delete {{ $task->title }}">❌</button>
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
                {{ $tasks->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Trash is empty</h3>
                <p class="text-gray-600">All your deleted tasks will appear here.</p>
            </div>
        @endif

        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="{{ route('tasks.index') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                ← Back to All Tasks
            </a>
        </div>
    </div>
@endsection
