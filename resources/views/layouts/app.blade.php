<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Task Manager') - Laravel MVC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .badge-pending { background-color: #fbbf24; }
        .badge-in-progress { background-color: #60a5fa; }
        .badge-done { background-color: #34d399; }
        .badge-low { background-color: #93c5fd; }
        .badge-medium { background-color: #f59e0b; }
        .badge-high { background-color: #ef4444; }
        .btn-primary { background-color: #667eea; }
        .btn-primary:hover { background-color: #5568d3; }
        .btn-danger { background-color: #ef4444; }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-success { background-color: #10b981; }
        .btn-success:hover { background-color: #059669; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    @include('components.navbar')

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        @if ($message = Session::get('success'))
            @include('components.alert', ['type' => 'success', 'message' => $message])
        @endif

        @if ($message = Session::get('error'))
            @include('components.alert', ['type' => 'error', 'message' => $message])
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 text-center py-6 mt-12 border-t">
        <p class="text-gray-600">© 2026 Task Manager - CMSC129 Lab 2 | Built with Laravel & Blade</p>
    </footer>
</body>
</html>
