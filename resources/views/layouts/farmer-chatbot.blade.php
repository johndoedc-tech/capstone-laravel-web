<div
    id="farmer-chatbot-root"
    class="fixed bottom-3 right-3 sm:bottom-6 sm:right-6"
    style="z-index: 45; padding-right: env(safe-area-inset-right, 0px); padding-bottom: env(safe-area-inset-bottom, 0px);"
    data-history-url="{{ route('farmer.chatbot.history') }}"
    data-message-url="{{ route('farmer.chatbot.message') }}"
    data-reset-url="{{ route('farmer.chatbot.reset') }}"
>
    <div
        id="farmer-chatbot-panel"
        class="hidden flex flex-col bg-white overflow-hidden fixed left-0 right-0 top-14 rounded-t-2xl border border-gray-200 border-b-0 shadow-2xl h-[calc(100dvh-3.5rem)] max-h-[calc(100dvh-3.5rem)] sm:static sm:mb-3 sm:rounded-2xl sm:border sm:w-[22rem] sm:max-w-[calc(100vw-3rem)] sm:h-[70vh] sm:max-h-[42rem]"
    >
        <div class="px-4 py-3 bg-primary text-white flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold">Harviana Assistant</p>
                <p class="text-xs text-primary-100">Farmer support chat</p>
            </div>
            <button
                type="button"
                id="farmer-chatbot-close"
                class="w-8 h-8 inline-flex items-center justify-center rounded-lg hover:bg-primary-700 transition-colors"
                aria-label="Close chatbot"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="farmer-chatbot-messages" class="p-3 bg-gray-50 overflow-y-auto flex-1 min-h-0 overscroll-contain"></div>

        <div class="px-3 pt-2 border-t border-gray-200 bg-white" style="padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px));">
            <form id="farmer-chatbot-form" class="flex items-center gap-1.5 sm:gap-2">
                <input
                    id="farmer-chatbot-input"
                    type="text"
                    name="message"
                    maxlength="2000"
                    placeholder="Ask about crops, map, or predictions"
                    class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-primary focus:ring-primary"
                    autocomplete="off"
                    required
                >
                <button
                    id="farmer-chatbot-send"
                    type="submit"
                    class="px-3 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-700 transition-colors shrink-0 min-w-[4.25rem] sm:min-w-[4.75rem]"
                >
                    Send
                </button>
            </form>

            <div class="mt-2 flex items-center justify-between gap-2">
                <p id="farmer-chatbot-status" class="text-xs text-gray-500">Ready</p>
                <button
                    type="button"
                    id="farmer-chatbot-reset"
                    class="text-xs font-medium text-red-600 hover:text-red-700"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>

    <button
        type="button"
        id="farmer-chatbot-launcher"
        class="w-14 h-14 rounded-full bg-primary text-white shadow-xl hover:bg-primary-700 transition-colors inline-flex items-center justify-center"
        aria-label="Open chatbot"
    >
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m-7 6l3.5-3H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h.5L6 20z" />
        </svg>
    </button>
</div>

<script>
(function () {
    if (window.__harvianaFarmerChatbotInitialized) {
        return;
    }

    window.__harvianaFarmerChatbotInitialized = true;

    const root = document.getElementById('farmer-chatbot-root');
    if (!root) {
        return;
    }

    const panel = document.getElementById('farmer-chatbot-panel');
    const launcher = document.getElementById('farmer-chatbot-launcher');
    const closeButton = document.getElementById('farmer-chatbot-close');
    const form = document.getElementById('farmer-chatbot-form');
    const input = document.getElementById('farmer-chatbot-input');
    const messagesContainer = document.getElementById('farmer-chatbot-messages');
    const sendButton = document.getElementById('farmer-chatbot-send');
    const statusLabel = document.getElementById('farmer-chatbot-status');
    const resetButton = document.getElementById('farmer-chatbot-reset');

    if (!panel || !launcher || !closeButton || !form || !input || !messagesContainer || !sendButton || !statusLabel || !resetButton) {
        return;
    }

    const storageKey = 'harviana_farmer_chatbot_open';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const historyUrl = root.dataset.historyUrl;
    const messageUrl = root.dataset.messageUrl;
    const resetUrl = root.dataset.resetUrl;
    const appMain = document.querySelector('main');
    const mobileQuery = window.matchMedia('(max-width: 639px)');
    const visualViewport = window.visualViewport;
    const lockState = {
        htmlOverflow: '',
        bodyOverflow: '',
        mainOverflow: '',
        active: false,
    };

    const state = {
        isOpen: localStorage.getItem(storageKey) === '1',
        isLoading: false,
        messages: [],
    };

    function setStatus(text) {
        statusLabel.textContent = text;
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;
        input.disabled = isLoading;
        sendButton.disabled = isLoading;
        sendButton.textContent = isLoading ? 'Sending...' : 'Send';
    }

    function isMobileViewport() {
        return mobileQuery.matches;
    }

    function setDocumentScrollLock(locked) {
        if (locked && !lockState.active) {
            lockState.htmlOverflow = document.documentElement.style.overflow;
            lockState.bodyOverflow = document.body.style.overflow;
            lockState.mainOverflow = appMain ? appMain.style.overflow : '';

            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';

            if (appMain) {
                appMain.style.overflow = 'hidden';
            }

            lockState.active = true;
            return;
        }

        if (!locked && lockState.active) {
            document.documentElement.style.overflow = lockState.htmlOverflow;
            document.body.style.overflow = lockState.bodyOverflow;

            if (appMain) {
                appMain.style.overflow = lockState.mainOverflow;
            }

            lockState.active = false;
        }
    }

    function updateMobilePanelHeight() {
        if (!isMobileViewport()) {
            panel.style.height = '';
            panel.style.maxHeight = '';
            return;
        }

        const viewportHeight = visualViewport ? Math.floor(visualViewport.height) : window.innerHeight;
        const topOffset = 56;
        const computedHeight = Math.max(320, viewportHeight - topOffset);

        panel.style.height = `${computedHeight}px`;
        panel.style.maxHeight = `${computedHeight}px`;
    }

    function setOpen(isOpen) {
        state.isOpen = isOpen;
        panel.classList.toggle('hidden', !isOpen);
        localStorage.setItem(storageKey, isOpen ? '1' : '0');

        launcher.classList.toggle('opacity-0', isOpen);
        launcher.classList.toggle('pointer-events-none', isOpen);

        const shouldLockBody = isOpen && isMobileViewport();
        setDocumentScrollLock(shouldLockBody);
        updateMobilePanelHeight();

        if (isOpen) {
            requestAnimationFrame(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                if (!isMobileViewport()) {
                    input.focus();
                }
            });
        }
    }

    function sanitizeAssistantText(text) {
        if (typeof text !== 'string') {
            return '';
        }

        return text
            .replace(/\*\*(.*?)\*\*/g, '$1')
            .replace(/`([^`]+)`/g, '$1')
            .replace(/^\s*#{1,6}\s*/gm, '')
            .replace(/^\s*[-*]\s+/gm, '')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '$1')
            .replace(/_{1,2}([^_]+)_{1,2}/g, '$1')
            .replace(/[ \t]+/g, ' ')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function createBubble(message) {
        const wrapper = document.createElement('div');
        wrapper.className = message.role === 'assistant' ? 'flex justify-start mb-2' : 'flex justify-end mb-2';

        const bubble = document.createElement('div');
        bubble.className = message.role === 'assistant'
            ? 'max-w-[90%] rounded-xl px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700'
            : 'max-w-[90%] rounded-xl px-3 py-2 text-sm bg-primary text-white';

        const messageText = message.role === 'assistant'
            ? sanitizeAssistantText(message.text)
            : (typeof message.text === 'string' ? message.text : '');

        bubble.textContent = messageText;
        wrapper.appendChild(bubble);

        return wrapper;
    }

    function renderMessages() {
        messagesContainer.innerHTML = '';

        if (!state.messages.length) {
            state.messages.push({
                role: 'assistant',
                text: 'Hello. I am Harviana Assistant. Ask me about crops, map insights, or prediction results.',
            });
        }

        state.messages.forEach((message) => {
            messagesContainer.appendChild(createBubble(message));
        });

        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function loadHistory() {
        if (!historyUrl) {
            renderMessages();
            return;
        }

        try {
            const response = await fetch(historyUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (response.ok && data.success && Array.isArray(data.history)) {
                state.messages = data.history
                    .map((message) => ({
                        role: message?.role === 'user' ? 'user' : 'assistant',
                        text: message?.role === 'assistant'
                            ? sanitizeAssistantText(message?.text)
                            : (typeof message?.text === 'string' ? message.text : ''),
                    }))
                    .filter((message) => message.text !== '');
            }
        } catch (error) {
            console.error('Failed to load chatbot history.', error);
        }

        renderMessages();
    }

    async function sendMessage(messageText) {
        if (state.isLoading) {
            return;
        }

        if (!messageUrl || !csrfToken) {
            setStatus('Chatbot is not configured yet.');
            return;
        }

        setLoading(true);
        setStatus('Thinking...');

        const pendingUserMessage = {
            role: 'user',
            text: messageText,
        };

        state.messages.push(pendingUserMessage);
        renderMessages();

        try {
            const response = await fetch(messageUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: messageText }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to contact the assistant right now.');
            }

            if (Array.isArray(data.history)) {
                state.messages = data.history
                    .map((message) => ({
                        role: message?.role === 'user' ? 'user' : 'assistant',
                        text: message?.role === 'assistant'
                            ? sanitizeAssistantText(message?.text)
                            : (typeof message?.text === 'string' ? message.text : ''),
                    }))
                    .filter((message) => message.text !== '');
            } else if (typeof data.reply === 'string' && data.reply.trim() !== '') {
                state.messages.push({
                    role: 'assistant',
                    text: sanitizeAssistantText(data.reply),
                });
            }

            renderMessages();
            setStatus('Ready');
        } catch (error) {
            state.messages.push({
                role: 'assistant',
                text: error.message || 'Assistant is temporarily unavailable. Please try again.',
            });
            renderMessages();
            setStatus('Last request failed');
        } finally {
            setLoading(false);
        }
    }

    async function resetConversation() {
        if (!resetUrl || !csrfToken) {
            return;
        }

        setStatus('Resetting...');

        try {
            const response = await fetch(resetUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                state.messages = [];
                renderMessages();
                setStatus('Conversation reset');
                return;
            }

            throw new Error(data.message || 'Could not reset conversation.');
        } catch (error) {
            setStatus('Reset failed');
        }
    }

    launcher.addEventListener('click', () => {
        setOpen(!state.isOpen);
    });

    closeButton.addEventListener('click', () => {
        setOpen(false);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (state.isLoading) {
            return;
        }

        const messageText = input.value.trim();

        if (messageText === '') {
            return;
        }

        input.value = '';
        sendMessage(messageText);
    });

    input.addEventListener('focus', () => {
        if (!isMobileViewport()) {
            return;
        }

        setTimeout(() => {
            updateMobilePanelHeight();
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 180);
    });

    resetButton.addEventListener('click', () => {
        if (!confirm('Reset this conversation?')) {
            return;
        }

        resetConversation();
    });

    function syncViewportState() {
        updateMobilePanelHeight();
        setDocumentScrollLock(state.isOpen && isMobileViewport());

        if (state.isOpen) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    window.addEventListener('resize', syncViewportState);
    window.addEventListener('orientationchange', () => {
        setTimeout(syncViewportState, 120);
    });

    if (visualViewport) {
        visualViewport.addEventListener('resize', syncViewportState);
    }

    setOpen(state.isOpen);
    loadHistory();
})();
</script>
