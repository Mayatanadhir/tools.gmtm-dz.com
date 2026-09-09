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
                <x-badge variant="info" size="md">
                    {{ $roles->count() }} {{ __('Roles') }}
                </x-badge>
                <x-badge variant="success" size="md">
                    {{ $permissions->total() }} {{ __('Permissions') }}
                </x-badge>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showRoleModal:            {{ ($errors->any() && old('form_type') === 'role') ? 'true' : 'false' }},
        showPermissionModal:      {{ ($errors->any() && old('form_type') === 'permission') ? 'true' : 'false' }},
        showEditRoleModal:        {{ ($errors->any() && old('form_type') === 'edit_role') ? 'true' : 'false' }},
        showEditPermissionModal:  {{ ($errors->any() && old('form_type') === 'edit_permission') ? 'true' : 'false' }},
        showDeleteRoleModal:       false,
        showDeletePermissionModal: false,
        showPermissionsCatalog:   {{ ($errors->any() && in_array(old('form_type'), ['permission', 'edit_permission'])) ? 'true' : 'false' }},

        createRolePermissions:    {{ (old('form_type') === 'role' && is_array(old('permissions'))) ? json_encode(old('permissions')) : '[]' }},

        editRoleId:               {{ old('form_type') === 'edit_role' ? (int) old('edit_role_id', 0) : 'null' }},
        editRoleName:             '{{ old('form_type') === 'edit_role' ? addslashes((string) old('name', '')) : '' }}',
        editRolePermissions:      {{ old('form_type') === 'edit_role' && is_array(old('permissions')) ? json_encode(old('permissions')) : '[]' }},
        editRoleActionUrl:        '{{ old('form_type') === 'edit_role' && old('edit_role_id') ? route('system-tables.roles.update', (int) old('edit_role_id')) : '' }}',

        editPermissionId:         {{ old('form_type') === 'edit_permission' ? (int) old('edit_permission_id', 0) : 'null' }},
        editPermissionName:       '{{ old('form_type') === 'edit_permission' ? addslashes((string) old('name', '')) : '' }}',
        editPermissionActionUrl:  '{{ old('form_type') === 'edit_permission' && old('edit_permission_id') ? route('system-tables.permissions.update', (int) old('edit_permission_id')) : '' }}',

        deleteRoleName:             '',
        deleteRoleActionUrl:        '',
        deletePermissionName:       '',
        deletePermissionActionUrl:  '',

        allPermissionNames:       {{ json_encode($permissionMatrix['all_names']) }},

        openEditRoleModal(id, name, permissions, actionUrl) {
            this.editRoleId = id;
            this.editRoleName = name;
            this.editRolePermissions = Array.isArray(permissions) ? [...permissions] : [];
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

        toggleCreateEntityAll(actions) {
            const allSelected = actions.every(p => this.createRolePermissions.includes(p));
            if (allSelected) {
                this.createRolePermissions = this.createRolePermissions.filter(p => !actions.includes(p));
            } else {
                actions.forEach(p => {
                    if (!this.createRolePermissions.includes(p)) {
                        this.createRolePermissions.push(p);
                    }
                });
            }
        },

        toggleEditEntityAll(actions) {
            const allSelected = actions.every(p => this.editRolePermissions.includes(p));
            if (allSelected) {
                this.editRolePermissions = this.editRolePermissions.filter(p => !actions.includes(p));
            } else {
                actions.forEach(p => {
                    if (!this.editRolePermissions.includes(p)) {
                        this.editRolePermissions.push(p);
                    }
                });
            }
        },

        selectAllCreate() {
            this.createRolePermissions = [...this.allPermissionNames];
        },

        deselectAllCreate() {
            this.createRolePermissions = [];
        },

        selectAllEdit() {
            this.editRolePermissions = [...this.allPermissionNames];
        },

        deselectAllEdit() {
            this.editRolePermissions = [];
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-system-tabs active="roles" />
                </aside>

                <!-- Main Content -->
                <main class="flex-1 w-full min-w-0 space-y-8">

                    @if (session('status'))
                        <x-alert variant="success">
                            {{ session('status') }}
                        </x-alert>
                    @endif

                    <!-- ============================================================ -->
                    <!-- Permissions Catalog Section (Collapsible & On-Demand)        -->
                    <!-- ============================================================ -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 p-5 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center shrink-0 me-3.5">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                             {{ __('Permissions Catalog') }}
                                        </h3>
                                        <x-badge variant="success" class="ms-1.5">
                                            {{ __('Automated Engine') }}
                                        </x-badge>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ __('Managed automatically by the Schema Introspection Engine. Hidden by default for clean administration.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                    {{ $permissions->total() }} {{ __('Total permissions') }}
                                </span>
                                <x-secondary-button
                                    type="button"
                                    @click="showPermissionsCatalog = !showPermissionsCatalog"
                                    class="py-1.5 px-3 text-xs rounded-lg flex items-center gap-1.5"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-200 me-1" :class="{ 'rotate-180': showPermissionsCatalog }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span x-text="showPermissionsCatalog ? '{{ __('Hide Permissions Catalog') }}' : '{{ __('Manage Permissions Catalog') }}'"></span>
                                </x-secondary-button>
                                <div x-show="showPermissionsCatalog" x-cloak>
                                    <x-primary-button type="button" @click="showPermissionModal = true" class="py-1.5 px-3 text-xs rounded-lg flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>{{ __('New Permission') }}</span>
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>

                        <!-- Collapsible Table Content -->
                        <div x-show="showPermissionsCatalog" x-cloak class="pt-3 border-t border-gray-100 dark:border-gray-700/60">
                            <x-table>
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
                                                <x-table.action-edit
                                                    :title="__('Edit Permission')"
                                                    @click="openEditPermissionModal({{ $permission->id }}, '{{ addslashes($permission->name) }}', '{{ route('system-tables.permissions.update', $permission) }}')"
                                                />
                                                <x-table.action-delete
                                                    :title="__('Delete Permission')"
                                                    @click="openDeletePermissionModal('{{ addslashes($permission->name) }}', '{{ route('system-tables.permissions.destroy', $permission) }}')"
                                                />
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
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- Roles Cards Grid                                             -->
                    <!-- ============================================================ -->
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500 shrink-0 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
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
                                            @if(in_array($role->name, $superRoles ?? ['Super-Admin'], true))
                                                <x-badge variant="warning">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    {{ __('Protected System Role') }}
                                                </x-badge>
                                            @elseif($role->name === ($defaultRole ?? 'User'))
                                                <x-badge variant="info">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                                    {{ __('Default Role') }}
                                                </x-badge>
                                                <x-table.action-edit
                                                    :title="__('Edit Role')"
                                                    @click="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', {{ json_encode($role->permissions->pluck('name')->values()->all()) }}, '{{ route('system-tables.roles.update', $role) }}')"
                                                />
                                            @else
                                                <x-table.action-edit
                                                    :title="__('Edit Role')"
                                                    @click="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', {{ json_encode($role->permissions->pluck('name')->values()->all()) }}, '{{ route('system-tables.roles.update', $role) }}')"
                                                />
                                                <x-table.action-delete
                                                    :title="__('Delete Role')"
                                                    @click="openDeleteRoleModal('{{ addslashes($role->name) }}', '{{ route('system-tables.roles.destroy', $role) }}')"
                                                />
                                            @endif
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

                    <!-- ============================================================ -->
                    <!-- Create Role Modal — x-crud-modal.form                        -->
                    <!-- ============================================================ -->
                    <x-crud-modal.form
                        show="showRoleModal"
                        :action-url="route('system-tables.roles.store')"
                        method="POST"
                        :title="__('Create New System Role')"
                        :description="__('Define a new access role and configure its granted permissions.')"
                        icon-color="indigo"
                        :submit-text="__('Create Role')"
                        max-width="3xl"
                    >
                        <x-slot:hidden>
                            <input type="hidden" name="form_type" value="role">
                        </x-slot:hidden>

                        <!-- Role Name -->
                        <div>
                            <x-input-label for="new_role_name" :value="__('Role Name')" />
                            <x-text-input
                                id="new_role_name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                :value="old('form_type') === 'role' ? old('name') : ''"
                                required
                                autofocus
                                placeholder="{{ __('e.g. Editor, Supervisor') }}"
                            />
                            <x-input-error :messages="old('form_type') === 'role' ? $errors->get('name') : []" class="mt-1 text-xs" />
                        </div>

                        <!-- Dynamic Permission Matrix by Entity -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <x-input-label :value="__('Permission Matrix by Entity')" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Assign fine-grained CRUD privileges grouped automatically by database table.') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <button type="button"
                                            @click="selectAllCreate()"
                                            class="font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400 hover:underline">
                                        {{ __('Select All') }}
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <button type="button"
                                            @click="deselectAllCreate()"
                                            class="font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline">
                                        {{ __('Deselect All') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Matrix Table Container -->
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm max-h-64 overflow-y-auto">
                                <table class="w-full text-xs text-start border-collapse">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 z-10">
                                        <tr>
                                            <th class="py-2.5 px-3 font-semibold text-gray-700 dark:text-gray-200 text-start">{{ __('Table / Entity') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-emerald-600 dark:text-emerald-400 w-16">{{ __('View') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-orange-600 dark:text-orange-400 w-16">{{ __('Create') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-amber-600 dark:text-amber-400 w-16">{{ __('Edit') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-rose-600 dark:text-rose-400 w-16">{{ __('Delete') }}</th>
                                            <th class="py-2.5 px-3 font-semibold text-end text-gray-500 w-28">{{ __('Toggle Row') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @forelse($permissionMatrix['entities'] as $entity => $actions)
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                                <td class="py-2.5 px-3 font-medium text-gray-900 dark:text-white">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                                                        <span class="font-mono capitalize font-bold text-xs">{{ $entity }}</span>
                                                    </div>
                                                </td>
                                                @foreach(['view', 'create', 'edit', 'delete'] as $act)
                                                    <td class="py-2.5 px-2 text-center align-middle">
                                                        @if(isset($actions[$act]))
                                                            <input type="checkbox"
                                                                   name="permissions[]"
                                                                   value="{{ $actions[$act] }}"
                                                                   x-model="createRolePermissions"
                                                                   class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4"
                                                                   title="{{ $actions[$act] }}">
                                                        @else
                                                            <span class="text-gray-300 dark:text-gray-600 text-xs">&mdash;</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="py-2.5 px-3 text-end align-middle">
                                                    <button type="button"
                                                            @click="toggleCreateEntityAll({{ json_encode(array_values($actions)) }})"
                                                            class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 px-2.5 py-1 rounded border border-indigo-200/60 dark:border-indigo-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors whitespace-nowrap">
                                                        {{ __('Toggle All') }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-4 text-center text-gray-400 italic">
                                                    {{ __('No standard entities found.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(!empty($permissionMatrix['custom']))
                                <!-- Additional System Permissions -->
                                <div class="pt-2">
                                    <x-input-label :value="__('Additional System Permissions')" />
                                    <div class="mt-1.5 flex flex-wrap gap-2">
                                        @foreach($permissionMatrix['custom'] as $customPerm)
                                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-xs cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $customPerm }}"
                                                       x-model="createRolePermissions"
                                                       class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500 w-4 h-4">
                                                <span class="font-mono text-gray-700 dark:text-gray-300">{{ $customPerm }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <x-input-error :messages="old('form_type') === 'role' ? $errors->get('permissions') : []" class="mt-1 text-xs" />
                        </div>
                    </x-crud-modal.form>

                    <!-- ============================================================ -->
                    <!-- Create Permission Modal — x-crud-modal.form                  -->
                    <!-- ============================================================ -->
                    <x-crud-modal.form
                        show="showPermissionModal"
                        :action-url="route('system-tables.permissions.store')"
                        method="POST"
                        :title="__('Create New Permission')"
                        :description="__('Register a new permission into the system access catalog.')"
                        icon-color="emerald"
                        :submit-text="__('Create Permission')"
                    >
                        <x-slot:hidden>
                            <input type="hidden" name="form_type" value="permission">
                        </x-slot:hidden>

                        <!-- Permission Name -->
                        <div>
                            <x-input-label for="new_permission_name" :value="__('Permission Name')" />
                            <x-text-input
                                id="new_permission_name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                :value="old('form_type') === 'permission' ? old('name') : ''"
                                required
                                autofocus
                                placeholder="{{ __('e.g. view-reports, manage-billing') }}"
                            />
                            <x-input-error :messages="old('form_type') === 'permission' ? $errors->get('name') : []" class="mt-1 text-xs" />
                        </div>
                    </x-crud-modal.form>

                    <!-- ============================================================ -->
                    <!-- Edit Role Modal — x-crud-modal.form                          -->
                    <!-- ============================================================ -->
                    <x-crud-modal.form
                        show="showEditRoleModal"
                        alpine-action="editRoleActionUrl"
                        method="PUT"
                        :title="__('Edit System Role')"
                        :description="__('Update role name and reconfigure its granted permissions.')"
                        icon-color="amber"
                        :submit-text="__('Save Changes')"
                        max-width="3xl"
                    >
                        <x-slot:hidden>
                            <input type="hidden" name="form_type" value="edit_role">
                            <input type="hidden" name="edit_role_id" :value="editRoleId">
                        </x-slot:hidden>

                        <!-- Role Name -->
                        <div>
                            <x-input-label for="edit_role_name" :value="__('Role Name')" />
                            <x-text-input
                                id="edit_role_name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                x-model="editRoleName"
                                x-bind:readonly="editRoleName === '{{ $defaultRole ?? 'User' }}'"
                                x-bind:class="editRoleName === '{{ $defaultRole ?? 'User' }}' ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed opacity-80' : ''"
                                required
                                autofocus
                            />
                            <p x-show="editRoleName === '{{ $defaultRole ?? 'User' }}'" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                {{ __('The default role name is protected and cannot be changed.') }}
                            </p>
                            <x-input-error :messages="old('form_type') === 'edit_role' ? $errors->get('name') : []" class="mt-1 text-xs" />
                        </div>

                        <!-- Dynamic Permission Matrix by Entity -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <x-input-label :value="__('Permission Matrix by Entity')" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Assign fine-grained CRUD privileges grouped automatically by database table.') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <button type="button"
                                            @click="selectAllEdit()"
                                            class="font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400 hover:underline">
                                        {{ __('Select All') }}
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <button type="button"
                                            @click="deselectAllEdit()"
                                            class="font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline">
                                        {{ __('Deselect All') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Matrix Table Container -->
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm max-h-64 overflow-y-auto">
                                <table class="w-full text-xs text-start border-collapse">
                                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 z-10">
                                        <tr>
                                            <th class="py-2.5 px-3 font-semibold text-gray-700 dark:text-gray-200 text-start">{{ __('Table / Entity') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-emerald-600 dark:text-emerald-400 w-16">{{ __('View') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-orange-600 dark:text-orange-400 w-16">{{ __('Create') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-amber-600 dark:text-amber-400 w-16">{{ __('Edit') }}</th>
                                            <th class="py-2.5 px-2 font-semibold text-center text-rose-600 dark:text-rose-400 w-16">{{ __('Delete') }}</th>
                                            <th class="py-2.5 px-3 font-semibold text-end text-gray-500 w-28">{{ __('Toggle Row') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @forelse($permissionMatrix['entities'] as $entity => $actions)
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                                <td class="py-2.5 px-3 font-medium text-gray-900 dark:text-white">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                        <span class="font-mono capitalize font-bold text-xs">{{ $entity }}</span>
                                                    </div>
                                                </td>
                                                @foreach(['view', 'create', 'edit', 'delete'] as $act)
                                                    <td class="py-2.5 px-2 text-center align-middle">
                                                        @if(isset($actions[$act]))
                                                            <input type="checkbox"
                                                                   name="permissions[]"
                                                                   value="{{ $actions[$act] }}"
                                                                   x-model="editRolePermissions"
                                                                   class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4"
                                                                   title="{{ $actions[$act] }}">
                                                        @else
                                                            <span class="text-gray-300 dark:text-gray-600 text-xs">&mdash;</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="py-2.5 px-3 text-end align-middle">
                                                    <button type="button"
                                                            @click="toggleEditEntityAll({{ json_encode(array_values($actions)) }})"
                                                            class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 px-2.5 py-1 rounded border border-indigo-200/60 dark:border-indigo-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors whitespace-nowrap">
                                                        {{ __('Toggle All') }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-4 text-center text-gray-400 italic">
                                                    {{ __('No standard entities found.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(!empty($permissionMatrix['custom']))
                                <!-- Additional System Permissions -->
                                <div class="pt-2">
                                    <x-input-label :value="__('Additional System Permissions')" />
                                    <div class="mt-1.5 flex flex-wrap gap-2">
                                        @foreach($permissionMatrix['custom'] as $customPerm)
                                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-xs cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $customPerm }}"
                                                       x-model="editRolePermissions"
                                                       class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500 w-4 h-4">
                                                <span class="font-mono text-gray-700 dark:text-gray-300">{{ $customPerm }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <x-input-error :messages="old('form_type') === 'edit_role' ? $errors->get('permissions') : []" class="mt-1 text-xs" />
                        </div>
                    </x-crud-modal.form>

                    <!-- ============================================================ -->
                    <!-- Edit Permission Modal — x-crud-modal.form                    -->
                    <!-- ============================================================ -->
                    <x-crud-modal.form
                        show="showEditPermissionModal"
                        alpine-action="editPermissionActionUrl"
                        method="PUT"
                        :title="__('Edit System Permission')"
                        :description="__('Update permission name in the system access catalog.')"
                        icon-color="amber"
                        :submit-text="__('Save Changes')"
                    >
                        <x-slot:hidden>
                            <input type="hidden" name="form_type" value="edit_permission">
                            <input type="hidden" name="edit_permission_id" :value="editPermissionId">
                        </x-slot:hidden>

                        <!-- Permission Name -->
                        <div>
                            <x-input-label for="edit_perm_name" :value="__('Permission Name')" />
                            <x-text-input id="edit_perm_name" name="name" type="text" class="mt-1 block w-full text-sm" x-model="editPermissionName" required autofocus />
                            <x-input-error :messages="old('form_type') === 'edit_permission' ? $errors->get('name') : []" class="mt-1 text-xs" />
                        </div>
                    </x-crud-modal.form>

                    <!-- ============================================================ -->
                    <!-- Delete Role Modal — x-crud-modal.delete                      -->
                    <!-- ============================================================ -->
                    <x-crud-modal.delete
                        show="showDeleteRoleModal"
                        action-url="deleteRoleActionUrl"
                        :title="__('Delete Role')"
                        :message="__('Are you sure you want to permanently delete the role')"
                        item-name="deleteRoleName"
                        :submit-text="__('Delete Role')"
                    />

                    <!-- ============================================================ -->
                    <!-- Delete Permission Modal — x-crud-modal.delete                -->
                    <!-- ============================================================ -->
                    <x-crud-modal.delete
                        show="showDeletePermissionModal"
                        action-url="deletePermissionActionUrl"
                        :title="__('Delete Permission')"
                        :message="__('Are you sure you want to permanently delete the permission')"
                        item-name="deletePermissionName"
                        :submit-text="__('Delete Permission')"
                    />

                </main>
            </div>
        </div>
    </div>
</x-app-layout>
