@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-800 mb-6">✏️ Edit Task</h1>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <h3 class="font-bold mb-2">Validation Errors:</h3>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="bg-white rounded-lg shadow-md p-8">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Task Title *</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" placeholder="Enter task title..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Description</label>
                <textarea name="description" placeholder="Enter task description..." rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('description') border-red-500 @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Category *</label>
                <select name="category_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('category_id') border-red-500 @enderror">
                    <option value="">-- Select a Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $task->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Due Date -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('due_date') border-red-500 @enderror">
                @error('due_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status & Priority -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Status *</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Priority *</label>
                    <select name="priority" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('priority') border-red-500 @enderror">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary flex-1 text-white px-6 py-3 rounded-lg font-semibold">
                    💾 Update Task
                </button>
                <a href="{{ route('tasks.show', $task) }}" class="flex-1 text-center bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
