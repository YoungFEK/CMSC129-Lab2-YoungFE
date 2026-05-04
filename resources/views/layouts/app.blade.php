<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TodoList') - Laravel MVC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .badge-pending { background-color: #fbbf24; }
        .badge-in_progress, .badge-in-progress { background-color: #60a5fa; }
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
        .action-group {
            display: inline-flex;
            overflow: hidden;
            border-radius: 0.7rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }
        .action-group > * {
            display: flex;
            margin: 0;
        }
        .action-group > * + * .action-icon {
            border-left: 1px solid rgba(255, 255, 255, 0.75);
        }
        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0;
            font-size: 1rem;
            line-height: 1;
            transition: filter 0.15s ease, background-color 0.15s ease;
        }
        .action-icon:hover {
            filter: brightness(0.96);
        }
        .action-view { background-color: #dbeafe; color: #2563eb; }
        .action-edit { background-color: #fef3c7; color: #d97706; }
        .action-delete { background-color: #fee2e2; color: #dc2626; }
        .action-restore { background-color: #d1fae5; color: #059669; }
        .action-force-delete { background-color: #fecaca; color: #991b1b; }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 50;
        }
        .modal-backdrop.active {
            display: flex;
        }
        .modal-panel {
            width: 100%;
            max-width: 28rem;
            background: #ffffff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
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

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-panel">
            <div class="flex items-start gap-3">
                <div class="text-2xl">⚠️</div>
                <div class="flex-1">
                    <h2 id="confirmModalTitle" class="text-xl font-bold text-gray-800">Please confirm</h2>
                    <p id="confirmModalMessage" class="text-gray-600 mt-2">Are you sure you want to continue?</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="confirmModalCancel" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                <button type="button" id="confirmModalConfirm" class="btn-danger text-white px-4 py-2 rounded-lg font-semibold">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 text-center py-6 mt-12 border-t">
        <p class="text-gray-600">© 2026 Task Manager - CMSC129 Lab 2 | Built with Laravel & Blade</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('confirmModal');

            if (!modal) {
                return;
            }

            const title = document.getElementById('confirmModalTitle');
            const message = document.getElementById('confirmModalMessage');
            const confirmButton = document.getElementById('confirmModalConfirm');
            const cancelButton = document.getElementById('confirmModalCancel');
            let activeForm = null;

            function closeModal() {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                activeForm = null;
            }

            function openModal(form) {
                activeForm = form;
                title.textContent = form.dataset.confirmTitle || 'Please confirm';
                message.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
                confirmButton.textContent = form.dataset.confirmButton || 'Confirm';
                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
            }

            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        form.dataset.confirmed = 'false';
                        return;
                    }

                    event.preventDefault();
                    openModal(form);
                });
            });

            confirmButton.addEventListener('click', function () {
                if (!activeForm) {
                    return;
                }

                activeForm.dataset.confirmed = 'true';
                activeForm.submit();
                closeModal();
            });

            cancelButton.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
