@php
    $initialToasts = collect([
        session('success') ? [
            'title' => 'Success',
            'message' => session('success'),
            'type' => 'success',
        ] : null,
        session('status') ? [
            'title' => 'Saved',
            'message' => session('status'),
            'type' => 'success',
        ] : null,
        session('error') ? [
            'title' => 'Could not complete action',
            'message' => session('error'),
            'type' => 'error',
        ] : null,
    ])->filter()->values();
@endphp

<div
    x-cloak
    x-data="{
        toasts: [],
        nextId: 1,
        recentKeys: new Map(),
        init() {
            window.harvianaToast = (title, message = '', type = 'success') => {
                if (typeof title === 'object' && title !== null) {
                    this.add(title);
                    return;
                }

                this.add({ title, message, type });
            };

            @foreach($initialToasts as $toast)
                this.add(@js($toast));
            @endforeach
        },
        add(toast) {
            const type = toast.type || 'success';
            const key = [type, toast.title || '', toast.message || ''].join('|');
            const now = Date.now();
            const lastShownAt = this.recentKeys.get(key) || 0;

            if (now - lastShownAt < 1000) {
                return;
            }

            this.recentKeys.set(key, now);

            const item = {
                id: this.nextId++,
                title: toast.title || (type === 'error' ? 'Could not complete action' : 'Success'),
                message: toast.message || '',
                type,
            };

            this.toasts.push(item);

            setTimeout(() => {
                this.remove(item.id);
            }, toast.duration || 4500);

            setTimeout(() => {
                this.recentKeys.delete(key);
            }, 1000);
        },
        remove(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
        toastClasses(type) {
            if (type === 'error') return 'border-red-100 bg-white';
            if (type === 'warning') return 'border-amber-100 bg-white';
            return 'border-emerald-100 bg-white';
        },
        titleClasses(type) {
            if (type === 'error') return 'text-red-700';
            if (type === 'warning') return 'text-amber-700';
            return 'text-emerald-700';
        }
    }"
    x-init="init()"
    @harviana-toast.window="add($event.detail || {})"
    class="fixed left-4 right-4 top-24 z-[80] space-y-2 sm:left-auto sm:right-6 sm:top-6 sm:w-96"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-4"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-4"
            class="rounded-xl border px-4 py-3 shadow-lg"
            :class="toastClasses(toast.type)"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold" :class="titleClasses(toast.type)" x-text="toast.title"></p>
                    <p x-show="toast.message" class="mt-0.5 text-sm text-gray-600" x-text="toast.message"></p>
                </div>
                <button type="button" @click="remove(toast.id)" class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close notification">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
