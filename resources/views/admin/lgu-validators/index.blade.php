<x-admin-layout>
    @php
        $filters = $filters ?? [];
        $barangaysByMunicipality = $barangaysByMunicipality ?? [];
        $selectedFilterMunicipality = $filters['municipality'] ?? '';
        $selectedFilterBarangay = $filters['barangay'] ?? '';
        $filterBarangays = $selectedFilterMunicipality && isset($barangaysByMunicipality[$selectedFilterMunicipality])
            ? $barangaysByMunicipality[$selectedFilterMunicipality]
            : [];
    @endphp

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6 space-y-5">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">User Administration</p>
                        <h1 class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">LGU Validators</h1>
                        <p class="mt-1 text-sm text-gray-500">Create and manage municipality or barangay-scoped LGU validator staff accounts.</p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-100">
                            All Users
                        </a>
                        <a href="{{ route('admin.lgu-validators.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Validator
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Active</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inactive</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['inactive']) }}</p>
                </div>
                <div class="rounded-lg border border-teal-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Municipalities</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['municipalities']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Barangay Scoped</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['barangay_scoped']) }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.lgu-validators.index') }}" class="grid gap-3 lg:grid-cols-[minmax(180px,1.4fr)_minmax(130px,0.9fr)_minmax(130px,0.9fr)_minmax(130px,0.7fr)_auto] lg:items-end" data-lgu-validator-filter-form>
                    <div>
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Name, email, barangay" autocomplete="off" data-lgu-filter-search class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="municipality" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Municipality</label>
                        <select id="municipality" name="municipality" data-lgu-filter-municipality class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All municipalities</option>
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality }}" @selected(($filters['municipality'] ?? '') === $municipality)>{{ ucwords(strtolower($municipality)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="barangay" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Barangay</label>
                        <select id="barangay" name="barangay" data-lgu-filter-barangay data-selected-barangay="{{ $selectedFilterBarangay }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All barangays</option>
                            @foreach($filterBarangays as $barangay)
                                <option value="{{ $barangay }}" @selected($selectedFilterBarangay === $barangay)>{{ ucwords(strtolower($barangay)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select id="status" name="status" data-lgu-filter-status class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="flex">
                        <a href="{{ route('admin.lgu-validators.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 lg:flex-none">Reset</a>
                    </div>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Accounts</h2>
                        <p class="text-sm text-gray-500">{{ number_format($validators->total()) }} {{ \Illuminate\Support\Str::plural('record', $validators->total()) }} found</p>
                    </div>
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role and Scope</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Created</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($validators as $validator)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-sm font-bold text-white">
                                                {{ strtoupper(substr($validator->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900">{{ $validator->name }}</p>
                                                <p class="truncate text-sm text-gray-500">{{ $validator->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-teal-100 px-2.5 py-1 text-xs font-semibold text-teal-800">
                                            LGU Validator
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $validator->lgu_municipality ? ucwords(strtolower($validator->lgu_municipality)) : 'No municipality' }}
                                            / {{ $validator->lgu_barangay ? ucwords(strtolower($validator->lgu_barangay)) : 'All barangays' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $validator->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $validator->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $validator->created_at?->format('M d, Y') }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.lgu-validators.edit', $validator) }}" class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                                            <form method="POST" action="{{ route('admin.lgu-validators.active', $validator) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $validator->is_active ? 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100' : 'border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                    {{ $validator->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-700">No LGU validator accounts found</p>
                                        <p class="mt-1 text-sm text-gray-500">Try changing the search or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100 md:hidden">
                    @forelse($validators as $validator)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-sm font-bold text-white">
                                    {{ strtoupper(substr($validator->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="break-words text-sm font-semibold text-gray-900">{{ $validator->name }}</p>
                                    <p class="break-all text-sm text-gray-500">{{ $validator->email }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full bg-teal-100 px-2.5 py-1 text-xs font-semibold text-teal-800">LGU Validator</span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $validator->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $validator->is_active ? 'Active' : 'Inactive' }}</span>
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $validator->created_at?->format('M d, Y') }}</span>
                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ $validator->lgu_municipality ? ucwords(strtolower($validator->lgu_municipality)) : 'No municipality' }}
                                / {{ $validator->lgu_barangay ? ucwords(strtolower($validator->lgu_barangay)) : 'All barangays' }}
                            </p>

                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                <a href="{{ route('admin.lgu-validators.edit', $validator) }}" class="inline-flex items-center justify-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                                <form method="POST" action="{{ route('admin.lgu-validators.active', $validator) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $validator->is_active ? 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100' : 'border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">{{ $validator->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <p class="text-sm font-semibold text-gray-700">No LGU validator accounts found</p>
                            <p class="mt-1 text-sm text-gray-500">Try changing the search or filters.</p>
                        </div>
                    @endforelse
                </div>

                @if($validators->hasPages())
                    <div class="border-t border-gray-100 px-4 py-4">
                        {{ $validators->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-lgu-validator-filter-form]');
            const searchInput = form?.querySelector('[data-lgu-filter-search]');
            const municipalitySelect = form?.querySelector('[data-lgu-filter-municipality]');
            const barangaySelect = form?.querySelector('[data-lgu-filter-barangay]');
            const statusSelect = form?.querySelector('[data-lgu-filter-status]');
            const barangaysByMunicipality = @json($barangaysByMunicipality);

            if (!form || !municipalitySelect || !barangaySelect || barangaySelect.dataset.bound === 'true') {
                return;
            }

            barangaySelect.dataset.bound = 'true';
            let filterSubmitTimer;

            const submitFilters = (delay = 0) => {
                window.clearTimeout(filterSubmitTimer);

                filterSubmitTimer = window.setTimeout(() => {
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }, delay);
            };

            const formatName = (name) => name.toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());

            const renderBarangays = () => {
                const selectedMunicipality = municipalitySelect.value;
                const selectedBarangay = barangaySelect.dataset.selectedBarangay || barangaySelect.value;
                const barangays = barangaysByMunicipality[selectedMunicipality] || [];

                barangaySelect.innerHTML = '';

                const allOption = document.createElement('option');
                allOption.value = '';
                allOption.textContent = 'All barangays';
                barangaySelect.appendChild(allOption);

                barangays.forEach((barangay) => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = formatName(barangay);
                    option.selected = selectedBarangay === barangay;
                    barangaySelect.appendChild(option);
                });

                barangaySelect.disabled = !selectedMunicipality;
                barangaySelect.dataset.selectedBarangay = barangaySelect.value;
            };

            municipalitySelect.addEventListener('change', () => {
                barangaySelect.dataset.selectedBarangay = '';
                renderBarangays();
                submitFilters();
            });

            barangaySelect.addEventListener('change', () => {
                barangaySelect.dataset.selectedBarangay = barangaySelect.value;
                submitFilters();
            });

            statusSelect?.addEventListener('change', () => {
                submitFilters();
            });

            searchInput?.addEventListener('input', () => {
                submitFilters(450);
            });

            searchInput?.addEventListener('search', () => {
                submitFilters();
            });

            renderBarangays();
        });
    </script>
</x-admin-layout>
