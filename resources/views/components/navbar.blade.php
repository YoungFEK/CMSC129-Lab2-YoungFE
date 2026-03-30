<nav class="navbar shadow-lg">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center">
            <a href="{{ route('tasks.index') }}" class="text-white text-2xl font-bold">
                📋 Task Manager
            </a>
        </div>

        <div class="flex gap-6 items-center">
            <a href="{{ route('tasks.index') }}" class="text-white hover:text-gray-200">
                All Tasks
            </a>
            <a href="{{ route('tasks.trash') }}" class="text-white hover:text-gray-200">
                🗑️ Trash
            </a>
            <a href="{{ route('tasks.create') }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">
                + New Task
            </a>
        </div>
    </div>
</nav>
