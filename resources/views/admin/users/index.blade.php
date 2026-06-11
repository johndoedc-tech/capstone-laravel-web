<x-admin-layout>
    @php
        $stats = $stats ?? [
            'total' => $totalUsers ?? 0,
            'admins' => $adminCount ?? 0,
            'farmers' => $farmerCount ?? 0,
            'lgu_validators' => $lguValidatorCount ?? 0,
            'active' => 0,
            'inactive' => 0,
            'recent' => $recentUsers ?? 0,
        ];
        $filters = $filters ?? request()->only(['search', 'role', 'status', 'sort_by', 'sort_order']);

        $roleClasses = [
            'admin' => 'bg-purple-100 text-purple-800',
            'farmer' => 'bg-emerald-100 text-emerald-800',
            'lgu_validator' => 'bg-teal-100 text-teal-800',
        ];
        $roleLabels = [
            'admin' => 'Admin',
            'farmer' => 'Farmer',
            'lgu_validator' => 'LGU Validator',
        ];
    @endphp

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6 space-y-5">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">User Administration</p>
                        <h1 class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">User Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Create and manage farmer, admin, and LGU validator accounts.</p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('admin.lgu-validators.index') }}" class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-100">
                            LGU Validators
                        </a>
                        <button
                            type="button"
                            data-open-modal="addUserModal"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add User
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-lg border border-purple-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-purple-700">Admins</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['admins']) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Farmers</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['farmers']) }}</p>
                </div>
                <div class="rounded-lg border border-teal-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">LGU</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['lgu_validators']) }}</p>
                </div>
                <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Active</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">New 30 Days</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['recent']) }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 lg:grid-cols-[minmax(180px,1.6fr)_minmax(130px,0.8fr)_minmax(130px,0.8fr)_minmax(130px,0.8fr)_minmax(110px,0.6fr)_auto] lg:items-end" data-admin-user-filter-form>
                    <div>
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                        <input
                            id="search"
                            type="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Name, email, role, or LGU area"
                            autocomplete="off"
                            data-admin-user-filter-search
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <div>
                        <label for="role" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Role</label>
                        <select id="role" name="role" data-admin-user-filter-select class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All roles</option>
                            <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                            <option value="farmer" @selected(($filters['role'] ?? '') === 'farmer')>Farmer</option>
                            <option value="lgu_validator" @selected(($filters['role'] ?? '') === 'lgu_validator')>LGU Validator</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select id="status" name="status" data-admin-user-filter-select class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort_by" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Sort</label>
                        <select id="sort_by" name="sort_by" data-admin-user-filter-select class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="created_at" @selected(($filters['sort_by'] ?? 'created_at') === 'created_at')>Created date</option>
                            <option value="name" @selected(($filters['sort_by'] ?? '') === 'name')>Name</option>
                            <option value="email" @selected(($filters['sort_by'] ?? '') === 'email')>Email</option>
                            <option value="role" @selected(($filters['sort_by'] ?? '') === 'role')>Role</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Order</label>
                        <select id="sort_order" name="sort_order" data-admin-user-filter-select class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="desc" @selected(($filters['sort_order'] ?? 'desc') === 'desc')>Newest</option>
                            <option value="asc" @selected(($filters['sort_order'] ?? '') === 'asc')>Oldest</option>
                        </select>
                    </div>

                    <div class="flex">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 lg:flex-none">
                            Reset
                        </a>
                    </div>
                </form>

                <p class="mt-3 text-xs text-gray-500">Use profile settings to change your own password.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Accounts</h2>
                        <p class="text-sm text-gray-500">{{ number_format($users->total()) }} {{ \Illuminate\Support\Str::plural('record', $users->total()) }} found</p>
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
                            @forelse($users as $user)
                                @php
                                    $isActive = (bool) ($user->is_active ?? true);
                                    $displayRole = $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role));
                                    $displayRoleClass = $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-700';
                                    $municipality = $user->lgu_municipality ? ucwords(strtolower($user->lgu_municipality)) : null;
                                    $barangay = $user->lgu_barangay ? ucwords(strtolower($user->lgu_barangay)) : null;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-sm font-bold text-white">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                                    @if($user->id === auth()->id())
                                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">You</span>
                                                    @endif
                                                </div>
                                                <p class="truncate text-sm text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $displayRoleClass }}">
                                            {{ $displayRole }}
                                        </span>
                                        @if($user->role === 'lgu_validator')
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $municipality ?? 'No area assigned' }}@if($barangay) / {{ $barangay }}@endif
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ optional($user->created_at)->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                data-open-edit-user
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->email }}"
                                                data-user-role="{{ $user->role }}"
                                                data-user-lgu-municipality="{{ $user->lgu_municipality }}"
                                                data-user-lgu-barangay="{{ $user->lgu_barangay }}"
                                                data-user-is-active="{{ $isActive ? '1' : '0' }}"
                                                class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                Edit
                                            </button>
                                            @if($user->id !== auth()->id())
                                                <button
                                                    type="button"
                                                    data-open-reset-password
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-email="{{ $user->email }}"
                                                    class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                                    Reset
                                                </button>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-700">No users found</p>
                                        <p class="mt-1 text-sm text-gray-500">Try changing the search or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100 md:hidden">
                    @forelse($users as $user)
                        @php
                            $isActive = (bool) ($user->is_active ?? true);
                            $displayRole = $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role));
                            $displayRoleClass = $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-700';
                            $municipality = $user->lgu_municipality ? ucwords(strtolower($user->lgu_municipality)) : null;
                            $barangay = $user->lgu_barangay ? ucwords(strtolower($user->lgu_barangay)) : null;
                        @endphp
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-sm font-bold text-white">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="break-words text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                        @if($user->id === auth()->id())
                                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">You</span>
                                        @endif
                                    </div>
                                    <p class="break-all text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $displayRoleClass }}">{{ $displayRole }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                    {{ optional($user->created_at)->format('M d, Y') }}
                                </span>
                            </div>

                            @if($user->role === 'lgu_validator')
                                <p class="mt-2 text-xs text-gray-500">
                                    {{ $municipality ?? 'No area assigned' }}@if($barangay) / {{ $barangay }}@endif
                                </p>
                            @endif

                            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                <button
                                    type="button"
                                    data-open-edit-user
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-user-email="{{ $user->email }}"
                                    data-user-role="{{ $user->role }}"
                                    data-user-lgu-municipality="{{ $user->lgu_municipality }}"
                                    data-user-lgu-barangay="{{ $user->lgu_barangay }}"
                                    data-user-is-active="{{ $isActive ? '1' : '0' }}"
                                    class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                    Edit
                                </button>
                                @if($user->id !== auth()->id())
                                    <button
                                        type="button"
                                        data-open-reset-password
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                        Reset
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <p class="text-sm font-semibold text-gray-700">No users found</p>
                            <p class="mt-1 text-sm text-gray-500">Try changing the search or filters.</p>
                        </div>
                    @endforelse
                </div>

                @if($users->hasPages())
                    <div class="border-t border-gray-100 px-4 py-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 px-4">
        <div class="relative top-10 lg:top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add New User</h3>
                <button type="button" data-close-modal="addUserModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" name="name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="farmer">Farmer</option>
                            <option value="admin">Administrator</option>
                            <option value="lgu_validator">LGU Validator</option>
                        </select>
                    </div>

                    <div class="rounded-lg border border-teal-100 bg-teal-50 px-3 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">LGU assignment</p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Municipality</label>
                                <select name="lgu_municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    <option value="">Only required for LGU Validator</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}">{{ ucwords(strtolower($municipality)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Barangay</label>
                                <input type="text" name="lgu_barangay" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Optional">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                Active validator account
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary-700 text-white py-2 rounded-lg font-medium transition">
                        Create User
                    </button>
                    <button type="button" data-close-modal="addUserModal"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 px-4">
        <div class="relative top-10 lg:top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit User</h3>
                <button type="button" data-close-modal="editUserModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="hidden" name="edit_user_id" id="edit_user_id" value="{{ old('edit_user_id') }}">
                        <input type="hidden" name="edit_user_name" id="edit_user_name" value="{{ old('edit_user_name') }}">
                        <input type="hidden" name="edit_user_email" id="edit_user_email" value="{{ old('edit_user_email') }}">
                        <input type="hidden" name="edit_user_role" id="edit_user_role" value="{{ old('edit_user_role') }}">
                        <input type="text" name="name" id="edit_name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @if($errors->has('name'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" id="edit_email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @if($errors->has('email'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select name="role" id="edit_role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="farmer">Farmer</option>
                            <option value="admin">Administrator</option>
                            <option value="lgu_validator">LGU Validator</option>
                        </select>
                        @if($errors->has('role'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->first('role') }}</p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-teal-100 bg-teal-50 px-3 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">LGU assignment</p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Municipality</label>
                                <select name="lgu_municipality" id="edit_lgu_municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    <option value="">Only required for LGU Validator</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}">{{ ucwords(strtolower($municipality)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Barangay</label>
                                <input type="text" name="lgu_barangay" id="edit_lgu_barangay" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Optional">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                Active validator account
                            </label>
                        </div>
                    </div>

                    <div>
                        <p class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-600">
                            Password changes now use the dedicated <span class="font-semibold">Reset Password</span> action so administrators can track and enforce required password updates clearly.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition">
                        Update User
                    </button>
                    <button type="button" data-close-modal="editUserModal"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 px-4">
        <div class="relative top-10 lg:top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
                    <p class="text-sm text-gray-500 mt-1">Set a temporary password and require the user to change it after login.</p>
                </div>
                <button type="button" data-close-modal="resetPasswordModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <p class="font-semibold">Share this password manually.</p>
                <p class="mt-1">The user will be signed out of existing sessions and forced to choose a new password after logging in.</p>
            </div>

            <div class="mb-4 rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-gray-500">Selected User</p>
                <p id="resetUserNameDisplay" class="text-sm font-semibold text-gray-900 mt-1">User</p>
                <p id="resetUserEmailDisplay" class="text-sm text-gray-600"></p>
            </div>

            <form id="resetPasswordForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="reset_user_id" id="reset_user_id" value="{{ old('reset_user_id') }}">
                <input type="hidden" name="reset_user_name" id="reset_user_name" value="{{ old('reset_user_name') }}">
                <input type="hidden" name="reset_user_email" id="reset_user_email" value="{{ old('reset_user_email') }}">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                        <input type="password" name="new_password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <p class="text-xs text-gray-500 mt-1">Use a temporary password the user can change after signing in.</p>
                        @if($errors->resetPassword->has('new_password'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->resetPassword->first('new_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <input type="password" name="new_password_confirmation" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @if($errors->resetPassword->has('new_password_confirmation'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->resetPassword->first('new_password_confirmation') }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-lg font-medium transition">
                        Reset Password
                    </button>
                    <button type="button" data-close-modal="resetPasswordModal"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editUserRouteTemplate = @js(route('admin.users.update', ['user' => '__USER__']));
            const resetPasswordRouteTemplate = @js(route('admin.users.password.reset', ['user' => '__USER__']));

            const addUserModal = document.getElementById('addUserModal');
            const editUserModal = document.getElementById('editUserModal');
            const resetPasswordModal = document.getElementById('resetPasswordModal');
            const editUserForm = document.getElementById('editUserForm');
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const filterForm = document.querySelector('[data-admin-user-filter-form]');
            const filterSearchInput = filterForm?.querySelector('[data-admin-user-filter-search]');
            const filterSelects = filterForm?.querySelectorAll('[data-admin-user-filter-select]');

            const showModal = (modal) => modal?.classList.remove('hidden');
            const hideModal = (modal) => modal?.classList.add('hidden');

            const buildRoute = (template, id) => template.replace('__USER__', id);

            if (filterForm && filterForm.dataset.bound !== 'true') {
                filterForm.dataset.bound = 'true';
                let filterSubmitTimer;

                const submitFilters = (delay = 0) => {
                    window.clearTimeout(filterSubmitTimer);

                    filterSubmitTimer = window.setTimeout(() => {
                        filterForm.requestSubmit ? filterForm.requestSubmit() : filterForm.submit();
                    }, delay);
                };

                filterSearchInput?.addEventListener('input', () => {
                    submitFilters(450);
                });

                filterSearchInput?.addEventListener('search', () => {
                    submitFilters();
                });

                filterSelects?.forEach((select) => {
                    select.addEventListener('change', () => {
                        submitFilters();
                    });
                });
            }

            const openEditModal = (id, name, email, role, lguMunicipality = '', lguBarangay = '', isActive = '1') => {
                document.getElementById('edit_user_id').value = id;
                document.getElementById('edit_user_name').value = name;
                document.getElementById('edit_user_email').value = email;
                document.getElementById('edit_user_role').value = role;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_lgu_municipality').value = lguMunicipality || '';
                document.getElementById('edit_lgu_barangay').value = lguBarangay || '';
                document.getElementById('edit_is_active').checked = isActive !== '0';
                editUserForm.action = buildRoute(editUserRouteTemplate, id);
                showModal(editUserModal);
            };

            const openResetModal = (id, name, email) => {
                document.getElementById('reset_user_id').value = id;
                document.getElementById('reset_user_name').value = name;
                document.getElementById('reset_user_email').value = email;
                document.getElementById('resetUserNameDisplay').textContent = name;
                document.getElementById('resetUserEmailDisplay').textContent = email;
                resetPasswordForm.action = buildRoute(resetPasswordRouteTemplate, id);
                showModal(resetPasswordModal);
            };

            document.querySelectorAll('[data-open-edit-user]').forEach((button) => {
                button.addEventListener('click', () => {
                    openEditModal(
                        button.dataset.userId,
                        button.dataset.userName,
                        button.dataset.userEmail,
                        button.dataset.userRole,
                        button.dataset.userLguMunicipality,
                        button.dataset.userLguBarangay,
                        button.dataset.userIsActive
                    );
                });
            });

            document.querySelectorAll('[data-open-reset-password]').forEach((button) => {
                button.addEventListener('click', () => {
                    openResetModal(
                        button.dataset.userId,
                        button.dataset.userName,
                        button.dataset.userEmail
                    );
                });
            });

            document.querySelectorAll('[data-open-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    showModal(document.getElementById(button.dataset.openModal));
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    hideModal(document.getElementById(button.dataset.closeModal));
                });
            });

            document.querySelectorAll('#addUserModal, #editUserModal, #resetPasswordModal').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        hideModal(modal);
                    }
                });
            });

            @if($errors->any())
                @if(old('name') && !request()->has('search') && !old('edit_user_id'))
                    showModal(addUserModal);
                @elseif(old('edit_user_id'))
                    openEditModal(
                        {{ old('edit_user_id') }},
                        @js(old('edit_user_name', 'User')),
                        @js(old('edit_user_email', '')),
                        @js(old('edit_user_role', 'farmer'))
                    );
                @endif
            @endif

            @if($errors->resetPassword->any() && old('reset_user_id'))
                openResetModal(
                    {{ old('reset_user_id') }},
                    @js(old('reset_user_name', 'User')),
                    @js(old('reset_user_email', ''))
                );
            @endif
        });
    </script>
</x-admin-layout>
