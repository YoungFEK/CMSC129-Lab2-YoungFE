<!-- AI Chat Widget Component -->
<div id="ai-chat-widget" class="fixed bottom-4 right-4 z-40">
    <!-- Chat Button -->
    <button id="chat-toggle-btn" class="btn btn-circle btn-lg btn-primary shadow-lg" title="Open AI Chat">
        💬
    </button>

    <!-- Chat Modal -->
    <div id="chat-modal" class="hidden fixed bottom-20 right-4 w-96 h-96 bg-white rounded-lg shadow-2xl flex flex-col z-50 border border-gray-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold">🤖 Task Assistant</h3>
                <p class="text-sm text-purple-100">Ask me anything about your tasks</p>
            </div>
            <button id="close-chat-btn" class="btn btn-ghost btn-sm btn-circle">✕</button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b bg-gray-50">
            <button id="inquire-tab" class="inquiry-tab flex-1 py-2 text-sm font-semibold border-b-2 border-purple-600 bg-white text-purple-600">
                💭 Inquiry
            </button>
            <button id="crud-tab" class="crud-tab flex-1 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-800">
                ✏️ Assistant
            </button>
        </div>

        <!-- Messages Container -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            <div id="welcome-inquiry" class="text-center text-gray-500 text-sm py-8">
                <p>💭 Hello! I'm your AI Task Chatbot.</p>
                <p class="text-xs mt-2">Ask me questions about your tasks, categories, and priorities. I can help you understand your to-do list!</p>
            </div>
            <div id="welcome-crud" class="text-center text-gray-500 text-sm py-8 hidden">
                <p>✏️ Hello! I'm your AI Task Assistant.</p>
                <p class="text-xs mt-2">I can help you create, update, and manage your tasks. Try commands like "Create a new task" or "Mark task as done"!</p>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="hidden px-4 py-2 text-center">
            <div class="inline-block">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>
        </div>

        <!-- Input Form -->
        <form id="chat-form" class="border-t p-3 bg-white rounded-b-lg flex gap-2">
            <input
                id="chat-input"
                type="text"
                placeholder="Ask me something..."
                class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                maxlength="2000"
                required
            >
            <button
                type="submit"
                class="btn btn-primary btn-sm"
                id="send-btn"
            >
                📤
            </button>
        </form>

        <!-- Error Display -->
        <div id="error-message" class="hidden bg-red-50 border-t border-red-200 p-3 text-red-700 text-sm rounded-b-lg">
            <span id="error-text"></span>
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #chat-modal:not(.hidden) {
        animation: slideUp 0.3s ease-out;
    }

    .chat-message {
        animation: slideUp 0.3s ease-out;
    }

    .user-message {
        @apply bg-purple-600 text-white rounded-lg px-4 py-2 max-w-xs ml-auto text-sm;
    }

    .assistant-message {
        @apply bg-gray-200 text-gray-900 rounded-lg px-4 py-2 max-w-xs text-sm;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const closeChatBtn = document.getElementById('close-chat-btn');
    const chatModal = document.getElementById('chat-modal');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const loadingIndicator = document.getElementById('loading-indicator');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    const inquireTab = document.getElementById('inquire-tab');
    const crudTab = document.getElementById('crud-tab');
    const welcomeInquiry = document.getElementById('welcome-inquiry');
    const welcomeCrud = document.getElementById('welcome-crud');

    let currentMode = 'inquiry';
    let chatHistories = {
        inquiry: [],
        crud: []
    };

    // Toggle chat modal
    chatToggleBtn.addEventListener('click', () => {
        chatModal.classList.toggle('hidden');
        if (!chatModal.classList.contains('hidden')) {
            chatInput.focus();
            loadChatHistory();
        }
    });

    // Close chat
    closeChatBtn.addEventListener('click', () => {
        chatModal.classList.add('hidden');
    });

    // Tab switching
    inquireTab.addEventListener('click', () => {
        if (currentMode !== 'inquiry') {
            currentMode = 'inquiry';
            updateTabs();
            loadChatHistory();
        }
    });

    crudTab.addEventListener('click', () => {
        if (currentMode !== 'crud') {
            currentMode = 'crud';
            updateTabs();
            loadChatHistory();
        }
    });

    function updateTabs() {
        inquireTab.classList.remove('border-purple-600', 'bg-white', 'text-purple-600');
        inquireTab.classList.add('border-transparent', 'text-gray-600');
        crudTab.classList.remove('border-purple-600', 'bg-white', 'text-purple-600');
        crudTab.classList.add('border-transparent', 'text-gray-600');

        if (currentMode === 'inquiry') {
            inquireTab.classList.add('border-purple-600', 'bg-white', 'text-purple-600');
        } else {
            crudTab.classList.add('border-purple-600', 'bg-white', 'text-purple-600');
        }
    }

    // Load chat history for current mode
    function loadChatHistory() {
        fetch(`/api/ai/history?mode=${currentMode}`)
            .then(response => response.json())
            .then(data => {
                // Update local history
                chatHistories[currentMode] = data.history || [];

                if (chatHistories[currentMode].length === 0) {
                    // Show appropriate welcome message
                    showWelcomeMessage();
                } else {
                    // Show chat history
                    chatMessages.innerHTML = '';
                    chatHistories[currentMode].forEach(msg => {
                        addMessageToUI(msg.message, msg.role);
                    });
                }
            })
            .catch(err => {
                console.error('Error loading history:', err);
                showWelcomeMessage();
            });
    }

    function showWelcomeMessage() {
        chatMessages.innerHTML = '';
        if (currentMode === 'inquiry') {
            chatMessages.innerHTML = `
                <div id="welcome-inquiry" class="text-center text-gray-500 text-sm py-8">
                    <p>💭 Hello! I'm your AI Task Chatbot.</p>
                    <p class="text-xs mt-2">Ask me questions about your tasks, categories, and priorities. I can help you understand your to-do list!</p>
                </div>
            `;
        } else {
            chatMessages.innerHTML = `
                <div id="welcome-crud" class="text-center text-gray-500 text-sm py-8">
                    <p>✏️ Hello! I'm your AI Task Assistant.</p>
                    <p class="text-xs mt-2">I can help you create, update, and manage your tasks. Try commands like "Create a new task" or "Mark task as done"!</p>
                </div>
            `;
        }
    }

    // Add message to UI
    function addMessageToUI(text, role) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-message ${role === 'user' ? 'user-message' : 'assistant-message'}`;
        msgDiv.textContent = text;
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Handle form submission
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = chatInput.value.trim();

        if (!message) return;

        // Add user message to UI and local history
        addMessageToUI(message, 'user');
        chatHistories[currentMode].push({ message, role: 'user', type: currentMode });

        chatInput.value = '';
        loadingIndicator.classList.remove('hidden');
        errorMessage.classList.add('hidden');

        try {
            const response = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    message: message,
                    mode: currentMode,
                }),
            });

            const data = await response.json();
            loadingIndicator.classList.add('hidden');

            if (data.success) {
                addMessageToUI(data.message, 'assistant');
                chatHistories[currentMode].push({ message: data.message, role: 'assistant', type: currentMode });
            } else {
                showError(data.message || 'Failed to get response');
            }
        } catch (error) {
            loadingIndicator.classList.add('hidden');
            showError('Network error. Please try again.');
            console.error('Error:', error);
        }
    });

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
    }
});
</script>
