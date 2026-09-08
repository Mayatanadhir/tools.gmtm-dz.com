<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                    {{ __('Users & Sessions') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ __('Inspect registered accounts, role assignments, and active user sessions') }}
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-xs font-semibold">
                {{ $users->total() }} {{ __('Total Users') }}
            </span>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showCreateModal: {{ ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('role')) && !old('_method') ? 'true' : 'false' }},
        showEditModal: {{ ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('role')) && old('_method') === 'PUT' ? 'true' : 'false' }},
        editUserId: {{ old('_method') === 'PUT' ? (int) old('edit_user_id', 0) : 'null' }},
        editUserName: '{{ old('_method') === 'PUT' ? addslashes((string) old('name', '')) : '' }}',
        editUserEmail: '{{ old('_method') === 'PUT' ? addslashes((string) old('email', '')) : '' }}',
        editUserRole: '{{ old('_method') === 'PUT' ? addslashes((string) old('role', '')) : '' }}',
        editUserActionUrl: '{{ old('_method') === 'PUT' && old('edit_user_id') ? route('system-tables.users.update', (int) old('edit_user_id')) : '' }}',
        openEditModal(id, name, email, role, actionUrl) {
            this.editUserId = id;
            this.editUserName = name;
            this.editUserEmail = email;
            this.editUserRole = role;
            this.editUserActionUrl = actionUrl;
            this.showEditModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-system-tabs active="users" />
                </aside>

                <!-- Main Content -->
                <main class="flex-1 w-full min-w-0 space-y-8">

                    @if (session('status'))
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Users Section -->
                    <x-table>
                        <x-slot:toolbar>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>{{ __('Users Table') }} (<code>users</code>)</span>
                            </h3>

                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Global Filter Form -->
                                <x-global-filter
                                    :action="route('system-tables.users')"
                                    :search="true"
                                    :search-placeholder="__('Search by name or email...')"
                                    :search-value="$search"
                                    :submit-text="__('Search')"
                                />

                                <!-- Create User Button -->
                                <x-primary-button type="button" @click="showCreateModal = true" class="py-1.5 px-3 text-xs rounded-lg flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>{{ __('New User') }}</span>
                                </x-primary-button>
                            </div>
                        </x-slot:toolbar>

                <x-slot:header>
                    <x-table.th>ID</x-table.th>
                    <x-table.th>{{ __('Name') }}</x-table.th>
                    <x-table.th>{{ __('Email') }}</x-table.th>
                    <x-table.th>{{ __('Roles') }}</x-table.th>
                    <x-table.th>{{ __('Status') }}</x-table.th>
                    <x-table.th>{{ __('Date') }}</x-table.th>
                    <x-table.th class="text-end">{{ __('Actions') }}</x-table.th>
                </x-slot:header>

                @forelse($users as $user)
                    <x-table.tr>
                        <x-table.td class="font-mono font-bold text-gray-900 dark:text-white">#{{ $user->id }}</x-table.td>
                        <x-table.td class="font-semibold text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span>{{ $user->name }}</span>
                            </div>
                        </x-table.td>
                        <x-table.td class="font-mono">{{ $user->email }}</x-table.td>
                        <x-table.td>
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 italic">{{ __('None') }}</span>
                                @endforelse
                            </div>
                        </x-table.td>
                        <x-table.td>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __('Verified') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ __('Unverified') }}
                                </span>
                            @endif
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap">
                            {{ $user->created_at?->format('Y-m-d H:i') }}
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap text-end">
                            <x-table.actions class="justify-end">
                                <x-table.action-edit
                                    type="button"
                                    :title="__('Edit User')"
                                    @click="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->roles->first()?->name ?? '' }}', '{{ route('system-tables.users.update', $user) }}')"
                                />
                                <x-table.action-delete :title="__('Delete User')" />
                            </x-table.actions>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <x-table.empty colspan="7" />
                @endforelse

                @if($users->hasPages())
                    <x-slot:pagination>
                        {{ $users->links() }}
                    </x-slot:pagination>
                @endif
            </x-table>

            <!-- Active Sessions Section -->
            <x-table>
                <x-slot:toolbar>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>{{ __('Active Sessions') }} (<code>sessions</code>)</span>
                    </h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        {{ $sessions->total() }} {{ __('Sessions tracked') }}
                    </span>
                </x-slot:toolbar>

                <x-slot:header>
                    <x-table.th>{{ __('Session ID') }}</x-table.th>
                    <x-table.th>{{ __('User') }}</x-table.th>
                    <x-table.th>{{ __('IP Address') }}</x-table.th>
                    <x-table.th>{{ __('User Agent') }}</x-table.th>
                    <x-table.th>{{ __('Last Activity') }}</x-table.th>
                    <x-table.th class="text-end">{{ __('Actions') }}</x-table.th>
                </x-slot:header>

                @forelse($sessions as $session)
                    <x-table.tr>
                        <x-table.td class="font-mono text-gray-400 dark:text-gray-500">
                            {{ substr($session->id, 0, 16) }}...
                        </x-table.td>
                        <x-table.td class="font-semibold text-gray-900 dark:text-white">
                            @if($session->user_name)
                                <span>{{ $session->user_name }}</span>
                                <span class="block text-xs font-mono font-normal text-gray-400">#{{ $session->user_id }}</span>
                            @else
                                <span class="text-gray-400 italic">{{ __('Guest') }}</span>
                            @endif
                        </x-table.td>
                        <x-table.td class="font-mono text-gray-800 dark:text-gray-200">
                            {{ $session->ip_address ?? '127.0.0.1' }}
                        </x-table.td>
                        <x-table.td class="max-w-xs truncate text-gray-500 dark:text-gray-400" title="{{ $session->user_agent }}">
                            {{ $session->user_agent ?? 'N/A' }}
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap">
                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap text-end">
                            <x-table.actions class="justify-end">
                                <x-table.action-delete :title="__('Terminate Session')" />
                            </x-table.actions>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <x-table.empty colspan="6" />
                @endforelse

                @if($sessions->hasPages())
                    <x-slot:pagination>
                        {{ $sessions->links() }}
                    </x-slot:pagination>
                @endif
            </x-table>

            <!-- Create User Modal (Alpine.js) -->
            <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showCreateModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showCreateModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                        <form method="POST" action="{{ route('system-tables.users.store') }}">
                            @csrf

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">
                                                {{ __('Create New System User') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Add a new user account with credentials and role assignment.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <x-input-label for="new_user_name" :value="__('Full Name')" />
                                        <x-text-input id="new_user_name" name="name" type="text" class="mt-1 block w-full text-sm" :value="old('name')" required autofocus placeholder="{{ __('e.g. John Doe') }}" />
                                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <x-input-label for="new_user_email" :value="__('Email Address')" />
                                        <x-text-input id="new_user_email" name="email" type="email" class="mt-1 block w-full text-sm" :value="old('email')" required placeholder="{{ __('e.g. user@example.com') }}" />
                                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Password -->
                                    <div>
                                        <x-input-label for="new_user_password" :value="__('Password')" />
                                        <x-text-input id="new_user_password" name="password" type="password" class="mt-1 block w-full text-sm" required placeholder="••••••••" />
                                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div>
                                        <x-input-label for="new_user_password_confirmation" :value="__('Confirm Password')" />
                                        <x-text-input id="new_user_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full text-sm" required placeholder="••••••••" />
                                    </div>

                                    <!-- Role Selection -->
                                    <div>
                                        <x-input-label for="new_user_role" :value="__('Role Assignment')" />
                                        <select id="new_user_role" name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                            <option value="">-- {{ __('Select Role (Optional)') }} --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('role')" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showCreateModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Create User') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal (Alpine.js) -->
            <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showEditModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showEditModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                        <form method="POST" :action="editUserActionUrl">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="edit_user_id" :value="editUserId">

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="edit-modal-title">
                                                {{ __('Edit System User') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Update user credentials, profile information, or assigned roles.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <x-input-label for="edit_user_name_input" :value="__('Full Name')" />
                                        <x-text-input id="edit_user_name_input" name="name" type="text" class="mt-1 block w-full text-sm" x-model="editUserName" required autofocus />
                                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <x-input-label for="edit_user_email_input" :value="__('Email Address')" />
                                        <x-text-input id="edit_user_email_input" name="email" type="email" class="mt-1 block w-full text-sm" x-model="editUserEmail" required />
                                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Password (Optional) -->
                                    <div>
                                        <x-input-label for="edit_user_password_input" :value="__('New Password (Optional)')" />
                                        <x-text-input id="edit_user_password_input" name="password" type="password" class="mt-1 block w-full text-sm" placeholder="{{ __('Leave blank to keep current password') }}" />
                                        <p class="mt-1 text-xs text-gray-400">{{ __('Leave empty if you do not wish to change the password.') }}</p>
                                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div>
                                        <x-input-label for="edit_user_password_confirmation_input" :value="__('Confirm New Password')" />
                                        <x-text-input id="edit_user_password_confirmation_input" name="password_confirmation" type="password" class="mt-1 block w-full text-sm" placeholder="{{ __('Confirm new password') }}" />
                                    </div>

                                    <!-- Role Selection -->
                                    <div>
                                        <x-input-label for="edit_user_role_select" :value="__('Role Assignment')" />
                                        <select id="edit_user_role_select" name="role" x-model="editUserRole" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                            <option value="">-- {{ __('Select Role (Optional)') }} --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('role')" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showEditModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ __('Save Changes') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
            </div>
        </div>
    </div>
</x-app-layout>
