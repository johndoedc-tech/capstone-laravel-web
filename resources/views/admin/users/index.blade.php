<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
        <p class="text-xs lg:text-sm text-gray-600 mt-1">Manage system users and their roles</p>
    </x-slot>

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-6">
                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Total Users</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $totalUsers }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Admins -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Administrators</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $adminCount }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Farmers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Farmers</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $farmerCount }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Recent Users (Last 30 Days) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">New (30 days)</p>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $recentUsers }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-4 lg:mb-6 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-primary-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-primary-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 lg:mb-6 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Filters and Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6">
                <div class="p-4 lg:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Search and Filters -->
                        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                    placeholder="Search by name or email..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="w-full sm:w-48">
                                <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Roles</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="farmer" {{ request('role') == 'farmer' ? 'selected' : '' }}>Farmer</option>
                                </select>
                            </div>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Filter
                            </button>
                            @if(request('search') || request('role'))
                                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center">
                                    Clear
                                </a>
                            @endif
                        </form>

                        <!-- Add User Button -->
                        <button
                            type="button"
                            data-open-modal="addUserModal"
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-700 transition flex items-center gap-2 justify-center whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add User
                        </button>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Need to change your own password? Use the password section on your profile page instead of user management.
                    </p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                @if($user->id === auth()->id())
                                                    <div class="text-xs text-blue-600">(You)</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->role === 'admin')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Admin
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Farmer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button
                                            type="button"
                                            data-open-edit-user
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-role="{{ $user->role }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            Edit
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button
                                                type="button"
                                                data-open-reset-password
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->email }}"
                                                class="text-amber-600 hover:text-amber-800 mr-3"
                                            >
                                                Reset Password
                                            </button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" 
                                                onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No users found</p>
                                        <p class="text-sm mt-1">Try adjusting your search or filters</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
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
                        </select>
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
                        </select>
                        @if($errors->has('role'))
                            <p class="text-xs text-red-600 mt-1">{{ $errors->first('role') }}</p>
                        @endif
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

            const showModal = (modal) => modal?.classList.remove('hidden');
            const hideModal = (modal) => modal?.classList.add('hidden');

            const buildRoute = (template, id) => template.replace('__USER__', id);

            const openEditModal = (id, name, email, role) => {
                document.getElementById('edit_user_id').value = id;
                document.getElementById('edit_user_name').value = name;
                document.getElementById('edit_user_email').value = email;
                document.getElementById('edit_user_role').value = role;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_role').value = role;
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
                        button.dataset.userRole
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
