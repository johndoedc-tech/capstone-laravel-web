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
                <p class="text-sm font-semibold inline-flex items-center gap-2">
                    <span>Harviana Assistant</span>
                    <span class="inline-flex items-center rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white/90">Beta</span>
                </p>
                <p class="text-xs text-primary-100">Farmer support chat</p>
            </div>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    id="farmer-chatbot-minimize"
                    class="hidden sm:inline-flex w-8 h-8 items-center justify-center rounded-lg hover:bg-primary-700 transition-colors"
                    aria-label="Minimize chatbot"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12" />
                    </svg>
                </button>
                <button
                    type="button"
                    id="farmer-chatbot-close"
                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg hover:bg-primary-700 transition-colors sm:hidden"
                    aria-label="Close chatbot"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
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
                    class="flex-1 min-w-0 rounded-lg border-gray-300 text-base sm:text-sm focus:border-primary focus:ring-primary"
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
        class="w-14 h-14 rounded-full bg-primary text-white shadow-xl hover:bg-primary-700 transition-colors inline-flex items-center justify-center opacity-0 pointer-events-none"
        aria-label="Open chatbot"
    >
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m-7 6l3.5-3H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h.5L6 20z" />
        </svg>
    </button>
</div>

<style>
    #farmer-chatbot-panel {
        will-change: transform, opacity;
    }

    #farmer-chatbot-launcher {
        transition: opacity 180ms ease, transform 180ms ease, background-color 180ms ease;
    }

    .chatbot-panel-enter {
        animation: farmerChatbotPanelEnter 220ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .chatbot-panel-exit {
        animation: farmerChatbotPanelExit 220ms cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    .chatbot-launcher-pop {
        animation: farmerChatbotLauncherPop 180ms cubic-bezier(0.2, 1.1, 0.2, 1);
    }

    .chatbot-message-enter {
        animation: farmerChatbotMessageEnter 240ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .chatbot-typing-dot {
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 9999px;
        background-color: #7f8ea3;
        animation: farmerChatbotTypingDot 1s ease-in-out infinite;
    }

    .chatbot-typing-dot:nth-child(2) {
        animation-delay: 120ms;
    }

    .chatbot-typing-dot:nth-child(3) {
        animation-delay: 240ms;
    }

    @keyframes farmerChatbotPanelEnter {
        from {
            opacity: 0;
            transform: translateY(14px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes farmerChatbotPanelExit {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        to {
            opacity: 0;
            transform: translateY(14px) scale(0.98);
        }
    }

    @keyframes farmerChatbotLauncherPop {
        from {
            transform: scale(0.88);
        }

        to {
            transform: scale(1);
        }
    }

    @keyframes farmerChatbotMessageEnter {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes farmerChatbotTypingDot {
        0%, 80%, 100% {
            transform: translateY(0);
            opacity: 0.45;
        }

        40% {
            transform: translateY(-3px);
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #farmer-chatbot-launcher {
            transition: none;
        }

        .chatbot-panel-enter,
        .chatbot-panel-exit,
        .chatbot-launcher-pop,
        .chatbot-message-enter,
        .chatbot-typing-dot {
            animation: none !important;
        }
    }
</style>

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
    const minimizeButton = document.getElementById('farmer-chatbot-minimize');
    const closeButton = document.getElementById('farmer-chatbot-close');
    const form = document.getElementById('farmer-chatbot-form');
    const input = document.getElementById('farmer-chatbot-input');
    const messagesContainer = document.getElementById('farmer-chatbot-messages');
    const sendButton = document.getElementById('farmer-chatbot-send');
    const statusLabel = document.getElementById('farmer-chatbot-status');
    const resetButton = document.getElementById('farmer-chatbot-reset');

    if (!panel || !launcher || !minimizeButton || !closeButton || !form || !input || !messagesContainer || !sendButton || !statusLabel || !resetButton) {
        return;
    }

    const mobileStorageKey = 'harviana_farmer_chatbot_open';
    const desktopStorageKey = 'harviana_farmer_chatbot_minimized';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const historyUrl = root.dataset.historyUrl;
    const messageUrl = root.dataset.messageUrl;
    const resetUrl = root.dataset.resetUrl;
    const appMain = document.querySelector('main');
    const mobileQuery = window.matchMedia('(max-width: 639px)');
    const visualViewport = window.visualViewport;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const lockState = {
        htmlOverflow: '',
        bodyOverflow: '',
        mainOverflow: '',
        active: false,
    };
    const PANEL_ANIMATION_MS = 220;
    const REQUEST_TIMEOUT_MS = 20000;
    const CONTINUE_PROMPT_EN = 'Continue your previous answer from where it stopped. Do not repeat earlier steps.';
    const CONTINUE_PROMPT_FIL = 'Ipagpatuloy mo ang naunang sagot mula sa huling bahagi. Huwag ulitin ang naunang mga hakbang.';
    let panelHideTimer = null;
    let viewportSyncTimer = null;
    let typingIndicatorElement = null;

    const state = {
        isOpen: localStorage.getItem(mobileStorageKey) === '1',
        isMinimized: localStorage.getItem(desktopStorageKey) === '1',
        isLoading: false,
        activeRequestId: 0,
        requestSequence: 0,
        hasUserActivity: false,
        lastFailedRequest: null,
        messages: [],
    };

    function createRequestToken() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        const randomFragment = Math.random().toString(36).slice(2, 11);
        return `req-${Date.now()}-${randomFragment}`;
    }

    function resolveRequestToken(messageText) {
        const lastFailedRequest = state.lastFailedRequest;

        if (
            lastFailedRequest
            && lastFailedRequest.message === messageText
            && (Date.now() - lastFailedRequest.at) <= 120000
        ) {
            return lastFailedRequest.requestId;
        }

        return createRequestToken();
    }

    function setStatus(text) {
        statusLabel.textContent = text;
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;
        input.disabled = isLoading;
        sendButton.disabled = isLoading;
        resetButton.disabled = isLoading;
        resetButton.classList.toggle('opacity-60', isLoading);
        resetButton.classList.toggle('pointer-events-none', isLoading);
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
            panel.style.top = '';
            panel.style.bottom = '';
            panel.style.height = '';
            panel.style.maxHeight = '';
            return;
        }

        const layoutHeight = window.innerHeight;
        const viewportHeight = visualViewport ? Math.floor(visualViewport.height) : layoutHeight;
        const viewportTop = visualViewport ? Math.floor(visualViewport.offsetTop) : 0;
        const keyboardInset = visualViewport
            ? Math.max(0, layoutHeight - Math.floor(visualViewport.height + visualViewport.offsetTop))
            : 0;
        const topOffset = 56;
        const computedHeight = Math.max(280, viewportHeight - topOffset);

        panel.style.top = `${viewportTop + topOffset}px`;
        panel.style.bottom = `${keyboardInset}px`;

        panel.style.height = `${computedHeight}px`;
        panel.style.maxHeight = `${computedHeight}px`;
    }

    function syncViewportAfterKeyboardChange(frameCount = 4) {
        if (!isMobileViewport()) {
            return;
        }

        let remainingFrames = frameCount;

        function tick() {
            updateMobilePanelHeight();
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            remainingFrames -= 1;

            if (remainingFrames > 0) {
                window.requestAnimationFrame(tick);
            }
        }

        window.requestAnimationFrame(tick);
    }

    function clearPanelHideTimer() {
        if (panelHideTimer !== null) {
            window.clearTimeout(panelHideTimer);
            panelHideTimer = null;
        }
    }

    function animatePanelIn(animate) {
        clearPanelHideTimer();
        panel.classList.remove('chatbot-panel-exit');
        panel.classList.remove('hidden');

        if (!animate || prefersReducedMotion.matches) {
            panel.classList.remove('chatbot-panel-enter');
            return;
        }

        panel.classList.remove('chatbot-panel-enter');
        void panel.offsetWidth;
        panel.classList.add('chatbot-panel-enter');

        window.setTimeout(() => {
            panel.classList.remove('chatbot-panel-enter');
        }, PANEL_ANIMATION_MS);
    }

    function animatePanelOut(animate) {
        clearPanelHideTimer();

        if (panel.classList.contains('hidden')) {
            return;
        }

        panel.classList.remove('chatbot-panel-enter');

        if (!animate || prefersReducedMotion.matches) {
            panel.classList.add('hidden');
            panel.classList.remove('chatbot-panel-exit');
            return;
        }

        panel.classList.add('chatbot-panel-exit');
        panelHideTimer = window.setTimeout(() => {
            panel.classList.add('hidden');
            panel.classList.remove('chatbot-panel-exit');
            panelHideTimer = null;
        }, PANEL_ANIMATION_MS);
    }

    function animateLauncherPop(shouldAnimate) {
        if (!shouldAnimate || prefersReducedMotion.matches) {
            return;
        }

        launcher.classList.remove('chatbot-launcher-pop');
        void launcher.offsetWidth;
        launcher.classList.add('chatbot-launcher-pop');
    }

    function applyViewportMode(options = {}) {
        const animate = options.animate === true;
        const mobileViewport = isMobileViewport();
        const launcherWasVisible = !launcher.classList.contains('opacity-0');

        if (mobileViewport) {
            if (state.isOpen) {
                animatePanelIn(animate);
            } else {
                animatePanelOut(animate);
            }

            launcher.classList.toggle('opacity-0', state.isOpen);
            launcher.classList.toggle('pointer-events-none', state.isOpen);
            setDocumentScrollLock(state.isOpen);
        } else {
            if (state.isMinimized) {
                animatePanelOut(animate);
            } else {
                animatePanelIn(animate);
            }

            launcher.classList.toggle('opacity-0', !state.isMinimized);
            launcher.classList.toggle('pointer-events-none', !state.isMinimized);
            setDocumentScrollLock(false);
        }

        const launcherIsVisible = !launcher.classList.contains('opacity-0');
        animateLauncherPop(launcherIsVisible && !launcherWasVisible);

        updateMobilePanelHeight();

        if (!mobileViewport || state.isOpen) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    function setOpen(isOpen, options = {}) {
        const wasOpen = state.isOpen;
        state.isOpen = isOpen;

        if (isMobileViewport()) {
            localStorage.setItem(mobileStorageKey, isOpen ? '1' : '0');
        }

        applyViewportMode(options);

        if (isMobileViewport() && isOpen && !wasOpen) {
            requestAnimationFrame(() => {
                input.focus();
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            });
        }
    }

    function setMinimized(isMinimized, options = {}) {
        if (isMobileViewport()) {
            return;
        }

        const wasMinimized = state.isMinimized;
        state.isMinimized = isMinimized;
        localStorage.setItem(desktopStorageKey, isMinimized ? '1' : '0');

        applyViewportMode(options);

        if (wasMinimized && !isMinimized) {
            requestAnimationFrame(() => {
                input.focus();
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
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

    function looksLikeFilipinoText(text) {
        if (typeof text !== 'string') {
            return false;
        }

        return /\b(ang|ng|sa|mga|para|hindi|pwede|paano|ano|ito|iyan|ikaw|ka|ko|mo|natin|tanim|pagtatanim|ipagpatuloy|hakbang)\b/i.test(text);
    }

    function resolveContinuePrompt(text) {
        return looksLikeFilipinoText(text) ? CONTINUE_PROMPT_FIL : CONTINUE_PROMPT_EN;
    }

    function markLatestAssistantAsTruncated(replyText = '') {
        for (let index = state.messages.length - 1; index >= 0; index -= 1) {
            const message = state.messages[index];

            if (!message || message.role !== 'assistant') {
                continue;
            }

            const basisText = typeof replyText === 'string' && replyText.trim() !== ''
                ? replyText
                : message.text;

            message.truncated = true;
            message.continuePrompt = resolveContinuePrompt(basisText);
            return;
        }
    }

    function createBubble(message, options = {}) {
        const wrapper = document.createElement('div');
        wrapper.className = message.role === 'assistant' ? 'flex justify-start mb-2' : 'flex justify-end mb-2';

        if (options.animate === true) {
            wrapper.classList.add('chatbot-message-enter');
        }

        const bubble = document.createElement('div');
        bubble.className = message.role === 'assistant'
            ? 'max-w-[90%] rounded-xl px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 whitespace-pre-line break-words'
            : 'max-w-[90%] rounded-xl px-3 py-2 text-sm bg-primary text-white';

        const messageText = message.role === 'assistant'
            ? sanitizeAssistantText(message.text)
            : (typeof message.text === 'string' ? message.text : '');

        bubble.textContent = messageText;

        if (message.role === 'assistant' && message.truncated === true) {
            const actionRow = document.createElement('div');
            actionRow.className = 'mt-2 flex';

            const continueButton = document.createElement('button');
            continueButton.type = 'button';
            continueButton.className = 'inline-flex items-center rounded-md border border-primary/30 bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20 transition-colors';
            continueButton.textContent = 'Continue';
            continueButton.addEventListener('click', () => {
                if (state.isLoading) {
                    return;
                }

                const continuePrompt = typeof message.continuePrompt === 'string' && message.continuePrompt.trim() !== ''
                    ? message.continuePrompt
                    : CONTINUE_PROMPT_EN;

                sendMessage(continuePrompt);
            });

            actionRow.appendChild(continueButton);
            bubble.appendChild(actionRow);
        }

        wrapper.appendChild(bubble);

        return wrapper;
    }

    function createTypingBubble() {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-start mb-2 chatbot-message-enter';

        const bubble = document.createElement('div');
        bubble.className = 'max-w-[90%] rounded-xl px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 inline-flex items-center gap-1.5';

        for (let i = 0; i < 3; i += 1) {
            const dot = document.createElement('span');
            dot.className = 'chatbot-typing-dot';
            bubble.appendChild(dot);
        }

        wrapper.appendChild(bubble);
        return wrapper;
    }

    function showTypingIndicator() {
        removeTypingIndicator();
        typingIndicatorElement = createTypingBubble();
        messagesContainer.appendChild(typingIndicatorElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function removeTypingIndicator() {
        if (typingIndicatorElement && typingIndicatorElement.parentNode) {
            typingIndicatorElement.parentNode.removeChild(typingIndicatorElement);
        }

        typingIndicatorElement = null;
    }

    function renderMessages(options = {}) {
        const animateLast = options.animateLast === true;
        removeTypingIndicator();
        messagesContainer.innerHTML = '';

        if (!state.messages.length) {
            state.messages.push({
                role: 'assistant',
                text: 'Hello. I am Harviana Assistant. Ask me about crops, map insights, or prediction results.',
            });
        }

        state.messages.forEach((message, index) => {
            const isLastMessage = index === state.messages.length - 1;

            messagesContainer.appendChild(createBubble(message, {
                animate: animateLast && isLastMessage,
            }));
        });

        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function loadHistory() {
        if (!historyUrl) {
            renderMessages();
            return;
        }

        const historyRequestSequence = state.requestSequence;

        try {
            const response = await fetch(historyUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();
            const canApplyHistory = !state.hasUserActivity
                && !state.isLoading
                && historyRequestSequence === state.requestSequence;

            if (canApplyHistory && response.ok && data.success && Array.isArray(data.history)) {
                state.messages = data.history
                    .map((message) => ({
                        role: message?.role === 'user' ? 'user' : 'assistant',
                        text: message?.role === 'assistant'
                            ? sanitizeAssistantText(message?.text)
                            : (typeof message?.text === 'string' ? message.text : ''),
                        truncated: false,
                        continuePrompt: null,
                    }))
                    .filter((message) => message.text !== '');
            }
        } catch (error) {
            console.error('Failed to load chatbot history.', error);
        }

        if (!state.hasUserActivity && !state.isLoading && historyRequestSequence === state.requestSequence) {
            renderMessages();
        }
    }

    async function sendMessage(messageText) {
        if (state.isLoading) {
            return;
        }

        if (!messageUrl || !csrfToken) {
            setStatus('Chatbot is not configured yet.');
            return;
        }

        const requestId = ++state.requestSequence;
        const requestToken = resolveRequestToken(messageText);
        state.activeRequestId = requestId;
        state.hasUserActivity = true;
        setLoading(true);
        setStatus('Thinking...');

        const pendingUserMessage = {
            role: 'user',
            text: messageText,
        };

        state.messages.push(pendingUserMessage);
        renderMessages({ animateLast: true });
        showTypingIndicator();

        const abortController = new AbortController();
        const timeoutId = window.setTimeout(() => {
            abortController.abort();
        }, REQUEST_TIMEOUT_MS);

        try {
            const response = await fetch(messageUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Request-ID': requestToken,
                },
                credentials: 'same-origin',
                signal: abortController.signal,
                body: JSON.stringify({
                    message: messageText,
                    request_id: requestToken,
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (requestId !== state.activeRequestId) {
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to contact the assistant right now.');
            }

            state.lastFailedRequest = null;
            const wasTruncated = data?.metadata?.truncated === true;

            if (Array.isArray(data.history)) {
                state.messages = data.history
                    .map((message) => ({
                        role: message?.role === 'user' ? 'user' : 'assistant',
                        text: message?.role === 'assistant'
                            ? sanitizeAssistantText(message?.text)
                            : (typeof message?.text === 'string' ? message.text : ''),
                        truncated: false,
                        continuePrompt: null,
                    }))
                    .filter((message) => message.text !== '');

                if (wasTruncated) {
                    markLatestAssistantAsTruncated(typeof data.reply === 'string' ? data.reply : '');
                }
            } else if (typeof data.reply === 'string' && data.reply.trim() !== '') {
                state.messages.push({
                    role: 'assistant',
                    text: sanitizeAssistantText(data.reply),
                    truncated: wasTruncated,
                    continuePrompt: wasTruncated ? resolveContinuePrompt(data.reply) : null,
                });
            }

            renderMessages({ animateLast: true });
            setStatus(wasTruncated ? 'Reply shortened. Tap Continue for more.' : 'Ready');
        } catch (error) {
            if (requestId !== state.activeRequestId) {
                return;
            }

            const isTimeoutError = (typeof error === 'object' && error !== null && error.name === 'AbortError');
            const errorMessage = error instanceof Error ? error.message : String(error || '');

            state.messages.push({
                role: 'assistant',
                text: isTimeoutError
                    ? 'The assistant request timed out. Please try again.'
                    : (errorMessage || 'Assistant is temporarily unavailable. Please try again.'),
            });

            state.lastFailedRequest = {
                requestId: requestToken,
                message: messageText,
                at: Date.now(),
            };

            renderMessages({ animateLast: true });
            setStatus('Last request failed');
        } finally {
            window.clearTimeout(timeoutId);

            if (requestId !== state.activeRequestId) {
                return;
            }

            state.activeRequestId = 0;
            removeTypingIndicator();
            setLoading(false);
        }
    }

    async function resetConversation() {
        if (state.isLoading) {
            setStatus('Please wait for the current reply to finish.');
            return;
        }

        if (!resetUrl || !csrfToken) {
            return;
        }

        state.hasUserActivity = true;

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
        if (isMobileViewport()) {
            setOpen(!state.isOpen, { animate: true });
            return;
        }

        setMinimized(false, { animate: true });
    });

    minimizeButton.addEventListener('click', () => {
        if (isMobileViewport()) {
            setOpen(false, { animate: true });
            return;
        }

        setMinimized(true, { animate: true });
    });

    closeButton.addEventListener('click', () => {
        if (!isMobileViewport()) {
            return;
        }

        setOpen(false, { animate: true });
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

        syncViewportAfterKeyboardChange(10);
    });

    input.addEventListener('blur', () => {
        if (!isMobileViewport()) {
            return;
        }

        syncViewportAfterKeyboardChange(6);
    });

    resetButton.addEventListener('click', () => {
        if (!confirm('Reset this conversation?')) {
            return;
        }

        resetConversation();
    });

    function syncViewportState() {
        applyViewportMode({ animate: false });
    }

    function scheduleViewportSync(delay = 100) {
        if (viewportSyncTimer !== null) {
            window.clearTimeout(viewportSyncTimer);
        }

        viewportSyncTimer = window.setTimeout(() => {
            viewportSyncTimer = null;
            syncViewportState();
        }, delay);
    }

    window.addEventListener('resize', () => {
        scheduleViewportSync(100);
    });
    window.addEventListener('orientationchange', () => {
        scheduleViewportSync(140);
    });

    if (visualViewport) {
        visualViewport.addEventListener('resize', () => {
            scheduleViewportSync(80);
        });
    }

    setOpen(state.isOpen, { animate: false });
    loadHistory();
})();
</script>
