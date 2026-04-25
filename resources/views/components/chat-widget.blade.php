{{--
     CHAT WIDGET COMPONENT
     Floating AI assistant — bottom right corner
--}}

<!-- Toggle Button -->
<button id="chat-toggle"
    onclick="toggleChat()"
    class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl transition-all duration-300 hover:scale-110"
    style="background: linear-gradient(135deg, #7c3aed, #4f46e5);"
    title="Open AI Assistant">
    <span id="chat-toggle-icon">🤖</span>
</button>

<!-- Chat Window -->
<div id="chat-window"
    class="fixed bottom-24 right-6 z-50 w-96 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300 hidden"
    style="height: 520px; border: 1px solid #e5e7eb;">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 text-white"
        style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
        <div class="flex items-center gap-2">
            <span class="text-xl">🤖</span>
            <div>
                <div class="font-semibold text-sm">Task Assistant</div>
                <div class="text-xs opacity-80">Ask me anything about your tasks</div>
            </div>
        </div>
        <button onclick="toggleChat()" class="text-white opacity-70 hover:opacity-100 text-xl leading-none">✕</button>
    </div>

    <!-- Messages -->
    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
        <!-- Welcome message -->
        <div class="flex gap-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm flex-shrink-0"
                style="background:#ede9fe;">🤖</div>
            <div class="bg-white rounded-2xl rounded-tl-sm px-3 py-2 text-sm text-gray-700 shadow-sm max-w-xs">
                Hi! I'm your Task Assistant 👋<br>
                Ask me about your tasks or tell me to create, update, or delete them!<br><br>
                <span class="text-gray-400 text-xs">Try: "Show me high priority tasks"</span>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog (hidden by default) -->
    <div id="confirm-bar" class="hidden bg-amber-50 border-t border-amber-200 p-3">
        <div id="confirm-text" class="text-xs text-amber-800 mb-2"></div>
        <div class="flex gap-2">
            <button onclick="confirmAction(true)"
                class="flex-1 bg-red-500 text-white text-xs py-1.5 rounded-lg font-semibold hover:bg-red-600">
                ✓ Yes, confirm
            </button>
            <button onclick="confirmAction(false)"
                class="flex-1 bg-gray-200 text-gray-700 text-xs py-1.5 rounded-lg font-semibold hover:bg-gray-300">
                ✕ Cancel
            </button>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-3 bg-white border-t border-gray-100">
        <div class="flex gap-2 items-end">
            <textarea id="chat-input"
                placeholder="Ask about tasks or give a command..."
                rows="1"
                class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-purple-400"
                style="max-height: 80px;"
                onkeydown="handleKeyDown(event)"
                oninput="autoResize(this)"></textarea>
            <button id="send-btn"
                onclick="sendMessage()"
                class="w-9 h-9 rounded-xl flex items-center justify-center text-white flex-shrink-0 transition hover:opacity-90"
                style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// ─── State ───────────────────────────────────────────
let chatHistory     = [];       // [{role, content}]
let pendingConf     = null;     // pending confirmation data
let isLoading       = false;

// ─── Toggle ──────────────────────────────────────────
function toggleChat() {
    const win  = document.getElementById('chat-window');
    const icon = document.getElementById('chat-toggle-icon');
    const open = win.classList.contains('hidden');
    win.classList.toggle('hidden', !open);
    icon.textContent = open ? '✕' : '🤖';
    if (open) document.getElementById('chat-input').focus();
}

// ─── Send Message ─────────────────────────────────────
async function sendMessage() {
    const input = document.getElementById('chat-input');
    const text  = input.value.trim();
    if (!text || isLoading) return;

    // Handle yes/no confirmation replies
    const lower = text.toLowerCase();
    if (pendingConf && (lower.includes('yes') || lower.includes('confirm') || lower.includes('ok'))) {
        input.value = '';
        autoResize(input);
        confirmAction(true);
        return;
    }
    if (pendingConf && (lower.includes('no') || lower.includes('cancel'))) {
        input.value = '';
        autoResize(input);
        confirmAction(false);
        return;
    }

    appendMessage('user', text);
    chatHistory.push({ role: 'user', content: text });
    input.value = '';
    autoResize(input);
    setLoading(true);

    try {
        const res = await fetch('{{ route("chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ message: text, history: chatHistory.slice(-10) }),
        });

        const data = await res.json();

        if (data.error) {
            appendMessage('assistant', '⚠️ ' + data.error);
        } else if (data.awaiting_confirmation) {
            // Show confirmation UI
            pendingConf = data.pending_confirmations;
            appendMessage('assistant', data.reply);
            showConfirmBar(data.reply);
        } else {
            appendMessage('assistant', data.reply);
            chatHistory.push({ role: 'assistant', content: data.reply });
            if (data.task_update) refreshTaskList();
        }
    } catch (e) {
        appendMessage('assistant', '⚠️ Connection error. Please try again.');
    } finally {
        setLoading(false);
    }
}

// ─── Confirm Action ───────────────────────────────────
async function confirmAction(confirmed) {
    hideConfirmBar();

    if (!confirmed) {
        appendMessage('assistant', '❌ Operation cancelled. No changes were made.');
        chatHistory.push({ role: 'assistant', content: 'Operation cancelled.' });
        pendingConf = null;
        return;
    }

    setLoading(true);
    appendMessage('assistant-loading', '⏳ Executing...');

    try {
        const res = await fetch('{{ route("chat.confirm") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ confirmations: pendingConf, history: chatHistory.slice(-6) }),
        });

        removeLoadingMessage();
        const data = await res.json();

        appendMessage('assistant', data.reply || 'Done!');
        chatHistory.push({ role: 'assistant', content: data.reply });

        if (data.task_update) refreshTaskList();
    } catch (e) {
        removeLoadingMessage();
        appendMessage('assistant', '⚠️ Failed to execute the operation.');
    } finally {
        setLoading(false);
        pendingConf = null;
    }
}

// ─── Refresh task list without full page reload ────────
function refreshTaskList() {
    // Reload the page to reflect DB changes on the task table
    setTimeout(() => window.location.reload(), 800);
}

// ─── DOM Helpers ──────────────────────────────────────
function appendMessage(role, text) {
    const container = document.getElementById('chat-messages');
    const isUser    = role === 'user';
    const isLoading = role === 'assistant-loading';

    const wrapper = document.createElement('div');
    wrapper.className = `flex gap-2 ${isUser ? 'flex-row-reverse' : ''}`;
    if (isLoading) wrapper.id = 'loading-msg';

    const avatar = document.createElement('div');
    avatar.className = 'w-7 h-7 rounded-full flex items-center justify-center text-sm flex-shrink-0';
    avatar.style.background = isUser ? '#ddd6fe' : '#ede9fe';
    avatar.textContent = isUser ? '👤' : '🤖';

    const bubble = document.createElement('div');
    bubble.className = `rounded-2xl px-3 py-2 text-sm max-w-xs shadow-sm whitespace-pre-wrap ${
        isUser
            ? 'text-white rounded-tr-sm'
            : 'bg-white text-gray-700 rounded-tl-sm'
    }`;
    if (isUser) bubble.style.background = 'linear-gradient(135deg, #7c3aed, #4f46e5)';

    // Simple markdown: **bold**, newlines
    bubble.innerHTML = formatMessage(text);

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function formatMessage(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
}

function showConfirmBar(text) {
    const bar  = document.getElementById('confirm-bar');
    const txt  = document.getElementById('confirm-text');
    txt.textContent = 'Confirm this action?';
    bar.classList.remove('hidden');
}

function hideConfirmBar() {
    document.getElementById('confirm-bar').classList.add('hidden');
}

function removeLoadingMessage() {
    const el = document.getElementById('loading-msg');
    if (el) el.remove();
}

function setLoading(state) {
    isLoading = state;
    const btn = document.getElementById('send-btn');
    btn.style.opacity   = state ? '0.5' : '1';
    btn.style.cursor    = state ? 'not-allowed' : 'pointer';

    if (state) {
        const container = document.getElementById('chat-messages');
        const loadEl    = document.createElement('div');
        loadEl.id       = 'typing-indicator';
        loadEl.className= 'flex gap-2';
        loadEl.innerHTML= `
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm flex-shrink-0" style="background:#ede9fe;">🤖</div>
            <div class="bg-white rounded-2xl rounded-tl-sm px-3 py-2 shadow-sm flex gap-1 items-center">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>`;
        container.appendChild(loadEl);
        container.scrollTop = container.scrollHeight;
    } else {
        document.getElementById('typing-indicator')?.remove();
    }
}

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 80) + 'px';
}
</script>
