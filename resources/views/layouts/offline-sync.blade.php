@auth
    @if(auth()->user()->isFarmer() || auth()->user()->isLguValidator())
        <div
            id="harviana-offline-sync"
            data-user-id="{{ auth()->id() }}"
            data-user-role="{{ auth()->user()->role }}"
            data-context-url="{{ route('offline-sync.context') }}"
            data-state="online"
            class="fixed bottom-[max(1rem,env(safe-area-inset-bottom))] left-4 z-[65] lg:left-[17rem]"
        >
            <button
                type="button"
                data-open-sync
                class="inline-flex min-h-11 items-center gap-2 rounded-full border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                aria-haspopup="dialog"
                aria-controls="harviana-sync-dialog"
            >
                <span data-sync-dot class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span data-sync-status aria-live="polite">Online</span>
                <span data-sync-count hidden class="min-w-5 rounded-full bg-emerald-100 px-1.5 py-0.5 text-center text-xs text-emerald-800">0</span>
            </button>

            <dialog id="harviana-sync-dialog" data-sync-dialog class="m-auto w-[min(92vw,32rem)] rounded-2xl border-0 p-0 shadow-2xl backdrop:bg-gray-900/50">
                <section class="max-h-[min(80vh,42rem)] overflow-hidden rounded-2xl bg-white">
                    <header class="flex items-start justify-between gap-4 border-b border-gray-100 px-4 py-4 sm:px-5">
                        <div>
                            <p class="text-base font-semibold text-gray-900">Offline records</p>
                            <p class="mt-1 text-xs text-gray-500">Last synced: <span data-last-sync>Not yet synced</span></p>
                            <p class="mt-1 text-xs text-gray-500">Queued attachments: <span data-sync-attachments>0 photos, 0 MB</span></p>
                        </div>
                        <button type="button" data-close-sync class="min-h-11 min-w-11 rounded-lg text-xl text-gray-500 hover:bg-gray-100" aria-label="Close sync records">&times;</button>
                    </header>

                    <div data-sync-list class="max-h-[50vh] space-y-3 overflow-y-auto px-4 py-4 sm:px-5" aria-live="polite"></div>

                    <footer class="flex flex-col-reverse gap-2 border-t border-gray-100 px-4 py-4 sm:flex-row sm:justify-end sm:px-5">
                        <button type="button" data-close-sync class="min-h-11 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Close</button>
                        <button type="button" data-sync-now class="min-h-11 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Sync now</button>
                    </footer>
                </section>
            </dialog>
        </div>
    @endif
@endauth
