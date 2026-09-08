<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                    {{ __('Roles & Permissions') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ __('Inspect access control roles, assigned permission sets, and system boundaries') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-xs font-semibold">
                    {{ $roles->count() }} {{ __('Roles') }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
                    {{ $permissions->total() }} {{ __('Permissions') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showRoleModal: {{ ($errors->any() && old('form_type') === 'role') ? 'true' : 'false' }},
        showPermissionModal: {{ ($errors->any() && old('form_type') === 'permission') ? 'true' : 'false' }},
        showEditRoleModal: {{ ($errors->any() && old('form_type') === 'edit_role') ? 'true' : 'false' }},
        showEditPermissionModal: {{ ($errors->any() && old('form_type') === 'edit_permission') ? 'true' : 'false' }},
        showDeleteRoleModal: false,
        showDeletePermissionModal: false,

        editRoleId: {{ old('form_type') === 'edit_role' ? (int) old('edit_role_id', 0) : 'null' }},
        editRoleName: '{{ old('form_type') === 'edit_role' ? addslashes((string) old('name', '')) : '' }}',
        editRolePermissions: {{ old('form_type') === 'edit_role' ? json_encode(old('permissions', [])) : '[]' }},
        editRoleActionUrl: '{{ old('form_type') === 'edit_role' && old('edit_role_id') ? route('system-tables.roles.update', (int) old('edit_role_id')) : '' }}',

        editPermissionId: {{ old('form_type') === 'edit_permission' ? (int) old('edit_permission_id', 0) : 'null' }},
        editPermissionName: '{{ old('form_type') === 'edit_permission' ? addslashes((string) old('name', '')) : '' }}',
        editPermissionActionUrl: '{{ old('form_type') === 'edit_permission' && old('edit_permission_id') ? route('system-tables.permissions.update', (int) old('edit_permission_id')) : '' }}',

        deleteRoleName: '',
        deleteRoleActionUrl: '',
        deletePermissionName: '',
        deletePermissionActionUrl: '',

        openEditRoleModal(id, name, permissions, actionUrl) {
            this.editRoleId = id;
            this.editRoleName = name;
            this.editRolePermissions = permissions;
            this.editRoleActionUrl = actionUrl;
            this.showEditRoleModal = true;
        },

        openEditPermissionModal(id, name, actionUrl) {
            this.editPermissionId = id;
            this.editPermissionName = name;
            this.editPermissionActionUrl = actionUrl;
            this.showEditPermissionModal = true;
        },

        openDeleteRoleModal(name, actionUrl) {
            this.deleteRoleName = name;
            this.deleteRoleActionUrl = actionUrl;
            this.showDeleteRoleModal = true;
        },

        openDeletePermissionModal(name, actionUrl) {
            this.deletePermissionName = name;
            this.deletePermissionActionUrl = actionUrl;
            this.showDeletePermissionModal = true;
        },

        isPermissionSelected(permName) {
            return Array.isArray(this.editRolePermissions) && this.editRolePermissions.includes(permName);
        },

        togglePermission(permName) {
            if (!Array.isArray(this.editRolePermissions)) {
                this.editRolePermissions = [];
            }
            const idx = this.editRolePermissions.indexOf(permName);
            if (idx > -1) {
                this.editRolePermissions.splice(idx, 1);
            } else {
                this.editRolePermissions.push(permName);
            }
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-system-tabs active="roles" />
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

            <!-- Roles Cards Grid -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>{{ __('Configured Roles') }} (<code>roles</code>, <code>role_has_permissions</code>, <code>model_has_roles</code>)</span>
                    </h3>
                    <x-primary-button type="button" @click="showRoleModal = true" class="py-1.5 px-3 text-xs rounded-lg flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('New Role') }}</span>
                    </x-primary-button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($roles as $role)
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $role->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $role->guard_name }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                    {{ $role->users_count }} {{ __('Users assigned') }}
                                </p>

                                <div class="space-y-1.5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                        {{ __('Granted Permissions') }} ({{ $role->permissions->count() }}):
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($role->permissions as $perm)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-700/50">
                                                {{ $perm->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">{{ __('No permissions directly attached.') }}</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                <span class="text-xs font-mono text-gray-400">ID: #{{ $role->id }} &bull; {{ $role->created_at?->format('Y-m-d') }}</span>
                                <x-table.actions>
                                    <x-table.action-edit :title="__('Edit Role')" @click="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', {{ json_encode($role->permissions->pluck('name')->values()->all()) }}, '{{ route('system-tables.roles.update', $role) }}')" />
                                    <x-table.action-delete :title="__('Delete Role')" @click="openDeleteRoleModal('{{ addslashes($role->name) }}', '{{ route('system-tables.roles.destroy', $role) }}')"/>
                                </x-table.actions>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/60 text-gray-500">
                            {{ __('No records found.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Permissions Catalog Section -->
            <x-table>
                <x-slot:toolbar>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        <span>{{ __('Permissions Catalog') }} (<code>permissions</code>, <code>model_has_permissions</code>)</span>
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $permissions->total() }} {{ __('Total permissions') }}
                        </span>
                        <x-primary-button type="button" @click="showPermissionModal = true" class="py-1.5 px-3 text-xs rounded-lg flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>{{ __('New Permission') }}</span>
                        </x-primary-button>
                    </div>
                </x-slot:toolbar>

                <x-slot:header>
                    <x-table.th>ID</x-table.th>
                    <x-table.th>{{ __('Name') }}</x-table.th>
                    <x-table.th>{{ __('Guard') }}</x-table.th>
                    <x-table.th>{{ __('Roles using it') }}</x-table.th>
                    <x-table.th>{{ __('Date') }}</x-table.th>
                    <x-table.th class="text-end">{{ __('Actions') }}</x-table.th>
                </x-slot:header>

                @forelse($permissions as $permission)
                    <x-table.tr>
                        <x-table.td class="font-mono font-bold text-gray-900 dark:text-white">#{{ $permission->id }}</x-table.td>
                        <x-table.td class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ $permission->name }}
                        </x-table.td>
                        <x-table.td>
                            <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $permission->guard_name }}
                            </span>
                        </x-table.td>
                        <x-table.td class="font-bold text-gray-800 dark:text-gray-200">
                            {{ $permission->roles_count }}
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap">
                            {{ $permission->created_at?->format('Y-m-d H:i') }}
                        </x-table.td>
                        <x-table.td class="whitespace-nowrap text-end">
                            <x-table.actions class="justify-end">
                                <x-table.action-edit :title="__('Edit Permission')" @click="openEditPermissionModal({{ $permission->id }}, '{{ addslashes($permission->name) }}', '{{ route('system-tables.permissions.update', $permission) }}')" />
                                <x-table.action-delete :title="__('Delete Permission')" @click="openDeletePermissionModal('{{ addslashes($permission->name) }}', '{{ route('system-tables.permissions.destroy', $permission) }}')"/>
                            </x-table.actions>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <x-table.empty colspan="6" />
                @endforelse

                @if($permissions->hasPages())
                    <x-slot:pagination>
                        {{ $permissions->links() }}
                    </x-slot:pagination>
                @endif
            </x-table>

            <!-- Create Role Modal (Alpine.js) -->
            <div x-cloak x-show="showRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="role-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showRoleModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showRoleModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showRoleModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700">

                        <form method="POST" action="{{ route('system-tables.roles.store') }}">
                            @csrf
                            <input type="hidden" name="form_type" value="role">

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="role-modal-title">
                                                {{ __('Create New System Role') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Define a new access role and configure its granted permissions.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showRoleModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Role Name -->
                                    <div>
                                        <x-input-label for="new_role_name" :value="__('Role Name')" />
                                        <x-text-input id="new_role_name" name="name" type="text" class="mt-1 block w-full text-sm" :value="old('form_type') === 'role' ? old('name') : ''" required autofocus placeholder="{{ __('e.g. Editor, Supervisor') }}" />
                                        <x-input-error :messages="old('form_type') === 'role' ? $errors->get('name') : []" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Permissions -->
                                    <div>
                                        <x-input-label :value="__('Assign Permissions (Optional)')" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            {{ __('Select the permission privileges to attach to this role.') }}
                                        </p>
                                        <div class="max-h-52 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 p-3 bg-gray-50/50 dark:bg-gray-900/30 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @forelse($allPermissions as $perm)
                                                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ (old('form_type') === 'role' && is_array(old('permissions')) && in_array($perm->name, old('permissions'))) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500">
                                                    <span class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 truncate">{{ $perm->name }}</span>
                                                </label>
                                            @empty
                                                <div class="col-span-2 text-xs text-gray-400 italic py-2 text-center">
                                                    {{ __('No permissions available in the system catalog.') }}
                                                </div>
                                            @endforelse
                                        </div>
                                        <x-input-error :messages="old('form_type') === 'role' ? $errors->get('permissions') : []" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showRoleModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Create Role') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Create Permission Modal (Alpine.js) -->
            <div x-cloak x-show="showPermissionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="perm-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showPermissionModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showPermissionModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showPermissionModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                        <form method="POST" action="{{ route('system-tables.permissions.store') }}">
                            @csrf
                            <input type="hidden" name="form_type" value="permission">

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="perm-modal-title">
                                                {{ __('Create New Permission') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Register a new permission into the system access catalog.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showPermissionModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Permission Name -->
                                    <div>
                                        <x-input-label for="new_permission_name" :value="__('Permission Name')" />
                                        <x-text-input id="new_permission_name" name="name" type="text" class="mt-1 block w-full text-sm" :value="old('form_type') === 'permission' ? old('name') : ''" required autofocus placeholder="{{ __('e.g. view-reports, manage-billing') }}" />
                                        <x-input-error :messages="old('form_type') === 'permission' ? $errors->get('name') : []" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showPermissionModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Create Permission') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Role Modal (Alpine.js) -->
            <div x-cloak x-show="showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="edit-role-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showEditRoleModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showEditRoleModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showEditRoleModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700">

                        <form method="POST" :action="editRoleActionUrl">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_type" value="edit_role">
                            <input type="hidden" name="edit_role_id" :value="editRoleId">

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="edit-role-modal-title">
                                                {{ __('Edit System Role') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Update role name and reconfigure its granted permissions.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showEditRoleModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Role Name -->
                                    <div>
                                        <x-input-label for="edit_role_name" :value="__('Role Name')" />
                                        <x-text-input id="edit_role_name" name="name" type="text" class="mt-1 block w-full text-sm" x-model="editRoleName" required autofocus />
                                        <x-input-error :messages="old('form_type') === 'edit_role' ? $errors->get('name') : []" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Permissions -->
                                    <div>
                                        <x-input-label :value="__('Assign Permissions (Optional)')" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            {{ __('Select the permission privileges to attach to this role.') }}
                                        </p>
                                        <div class="max-h-52 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 p-3 bg-gray-50/50 dark:bg-gray-900/30 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @forelse($allPermissions as $perm)
                                                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" :checked="isPermissionSelected('{{ $perm->name }}')" @change="togglePermission('{{ $perm->name }}')" class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500">
                                                    <span class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 truncate">{{ $perm->name }}</span>
                                                </label>
                                            @empty
                                                <div class="col-span-2 text-xs text-gray-400 italic py-2 text-center">
                                                    {{ __('No permissions available in the system catalog.') }}
                                                </div>
                                            @endforelse
                                        </div>
                                        <x-input-error :messages="old('form_type') === 'edit_role' ? $errors->get('permissions') : []" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showEditRoleModal = false">
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

            <!-- Edit Permission Modal (Alpine.js) -->
            <div x-cloak x-show="showEditPermissionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="edit-perm-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showEditPermissionModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showEditPermissionModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showEditPermissionModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">

                        <form method="POST" :action="editPermissionActionUrl">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_type" value="edit_permission">
                            <input type="hidden" name="edit_permission_id" :value="editPermissionId">

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="edit-perm-modal-title">
                                                {{ __('Edit System Permission') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Update permission name in the system access catalog.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showEditPermissionModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <span class="sr-only">{{ __('Close') }}</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <!-- Permission Name -->
                                    <div>
                                        <x-input-label for="edit_perm_name" :value="__('Permission Name')" />
                                        <x-text-input id="edit_perm_name" name="name" type="text" class="mt-1 block w-full text-sm" x-model="editPermissionName" required autofocus />
                                        <x-input-error :messages="old('form_type') === 'edit_permission' ? $errors->get('name') : []" class="mt-1 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showEditPermissionModal = false">
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
                <!-- Delete Role Confirmation Modal (Alpine.js) -->
            <div x-cloak x-show="showDeleteRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="delete-role-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showDeleteRoleModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showDeleteRoleModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showDeleteRoleModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700">

                        <form method="POST" :action="deleteRoleActionUrl">
                            @csrf
                            @method('DELETE')

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white" id="delete-role-modal-title">
                                            {{ __('Delete Role') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Are you sure you want to permanently delete the role') }}
                                            <strong class="text-gray-800 dark:text-gray-200" x-text="deleteRoleName"></strong>?
                                            {{ __('This action cannot be undone. All users assigned to this role will lose its permissions.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showDeleteRoleModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-danger-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    {{ __('Delete Role') }}
                                </x-danger-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Permission Confirmation Modal (Alpine.js) -->
            <div x-cloak x-show="showDeletePermissionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="delete-perm-modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="showDeletePermissionModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                     @click="showDeletePermissionModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showDeletePermissionModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700">

                        <form method="POST" :action="deletePermissionActionUrl">
                            @csrf
                            @method('DELETE')

                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white" id="delete-perm-modal-title">
                                            {{ __('Delete Permission') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Are you sure you want to permanently delete the permission') }}
                                            <strong class="font-mono text-gray-800 dark:text-gray-200" x-text="deletePermissionName"></strong>?
                                            {{ __('This action cannot be undone. All roles using this permission will lose it immediately.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                                <x-secondary-button type="button" @click="showDeletePermissionModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-danger-button type="submit">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    {{ __('Delete Permission') }}
                                </x-danger-button>
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
