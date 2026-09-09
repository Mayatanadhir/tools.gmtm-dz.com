# Changelog: tools.gmtm-dz.com

All notable changes, features, refactorings, and fixes will be documented in this file.

The format is based on Keep a Changelog.

## [2026-09-09] — Comprehensive Anti-Self-Action Protection Suite (Self-Delete, Self-Suspend, Self-Role Locks)
### Security & UI Protection
- **Anti-Self-Deletion Guard (`SystemTableController::destroyUser`)**: Rejects any DELETE request where the authenticated administrator targets their own account (`$user->id === auth()->id()`). Redirects with localized session error `"You cannot delete your own account."`.
- **Anti-Self-Suspension Guard (`SystemTableController::toggleUserStatus` & `updateUser`)**: Strictly forbids administrators from toggling or updating their own account status to `suspended`.
- **UI Row Protection (`resources/views/system/users.blade.php`)**:
  - Delete row action (`<x-table.action-delete>`) is conditionally hidden for the authenticated user's own row (`@if($user->id !== auth()->id())`).
  - Quick-toggle account status button is conditionally hidden for the authenticated user's own row (`@if($user->id !== auth()->id())`).
- **Edit User Modal Protection (`resources/views/system/users.blade.php`)**:
  - `role` select dropdown is replaced with a read-only badge display and amber cautionary message (`"You cannot change your own role."`) when editing the authenticated user's own account (`editUserIsSelf`).
  - `status` select dropdown is replaced with a read-only status badge display and cautionary note when editing the authenticated user's own account.
- **Trilingual Dictionary Parity (`lang/ar.json`, `lang/en.json`, `lang/fr.json`)**: Added translations for `"You cannot delete your own account."`, `"You cannot suspend your own account."`, and `"You cannot change your own role."` (446 keys each, 100% key parity).
- **Test Suite Expansion (`tests/Feature/SystemTableTest.php`)**: Added tests for self-deletion rejection, another-user deletion success, toggle self-suspension rejection, and update self-suspension rejection. All 49 tests passing.

---

## [2026-09-09] — Rank-Lock Guard: Admin Self-Role Modification Prevention
### Security
- **Rank-Lock Guard** `app/Http/Controllers/SystemTableController.php` — Added security guard in `updateUser()` that rejects any PUT request where the authenticated admin targets their own account with a `role` field change. Returns a localized session error `You cannot change your own role.` instead of applying the change. Guard placed alongside the existing anti-lockout suspend guard.
- **Trilingual Translation** `lang/ar.json`, `lang/en.json`, `lang/fr.json` — Added `"You cannot change your own role."` with accurate Arabic (`لا يمكنك تغيير رتبتك الخاصة.`) and French (`Vous ne pouvez pas modifier votre propre rôle.`) translations.
- **Test Coverage** `tests/Feature/SystemTableTest.php` — Added `test_admin_cannot_change_their_own_role_via_system_panel` verifying the guard fires, session contains the `role` error, and the admin's original role is preserved.

---

## [2026-09-09] — Default Baseline Role Protection & Auto-Assignment Suite
### Added
- **Default Baseline Role Auto-Assignment** `app/Observers/UserObserver.php` — Automatically assigns the default `'User'` role to every newly registered user in the observer `created()` lifecycle event, ensuring all new accounts start at the baseline lowest rank without manual intervention.
- **Default Role Discovery & Architecture** `app/Services/PermissionDiscoveryService.php` — Added `getDefaultRole(): string` and `isDefaultRole(string $roleName): bool` methods.
- **Default Role Immutability & Anti-Deletion Security Guard** `app/Http/Controllers/SystemTableController.php`:
  - `destroyRole()`: Strictly blocks any deletion of the default role (`User`) with a localized error message (*"The default role cannot be deleted."*).
  - `updateRole()`: Strictly prohibits renaming the default role (*"The default role cannot be renamed."*).
- **Default Role Badge & UI Protection** `resources/views/system/roles.blade.php`:
  - Displays a distinctive semantic `<x-badge variant="info">` (`Default Role`) for the default role on the RBAC role cards.
  - Automatically omits and suppresses the delete action button (`<x-table.action-delete>`) for the default role.
  - Locks the role name field as read-only (`x-bind:readonly`) in the Edit Role modal with an explanatory cautionary warning note.
- **Navigation Switchers Redesign**:
  - `theme-switcher.blade.php`: Redesigned as a compact, sleek circular ghost button (`rounded-full w-9 h-9`) matching the navigation bar aesthetic, using project orange accent tokens for active state instead of indigo.
  - `language-switcher.blade.php`: Redesigned as a compact pill button displaying country flag + locale code + mini chevron, with orange theme highlights.
- **Automated Feature & Unit Testing**:
  - `RegistrationTest.php`: Verified that newly registered users are automatically assigned the default `User` role upon signup.
  - `SystemTableTest.php`: Verified that the default role cannot be deleted or renamed, and that the `Default Role` badge renders in the roles explorer.
  - `PermissionDiscoveryServiceTest.php`: Added unit assertions for `getDefaultRole` and `isDefaultRole`.
- **User Role Non-Null Enforcement (`UpdateSystemUserRequest` & `CreateSystemUserRequest`)**:
  - Strictly enforced that the user's role assignment cannot be empty or null (`'role' => ['sometimes', 'required', 'string', 'exists:roles,name']`).
  - `SystemTableController::updateUser`: Guaranteed that user role synchronization never leaves a user roleless by validating non-empty input and falling back safely to the default baseline role (`User`).
  - `resources/views/system/users.blade.php`: Removed `-- Select Role (Optional) --` from both Create User and Edit User modals, added `required` constraint with disabled placeholder `-- Select Role --`, and ensured `openEditModal` pre-selects the active role with a fallback to `User`.
  - Added attribute translations for `'role'` across `lang/ar/validation.php` (`الدور`), `lang/fr/validation.php` (`rôle`), and `lang/en/validation.php` (`role`).
  - Synchronized `"Select Role"` across all three language files (`lang/ar.json`, `lang/en.json`, `lang/fr.json` — 441 keys each, 100% parity).
  - Added feature test `test_updating_user_role_rejects_empty_or_null_role` in `SystemTableTest.php`.
- **Trilingual Dictionary Parity (Rule 17)** — Synchronized 5 new keys across `lang/ar.json`, `lang/en.json`, `lang/fr.json` (441 keys each, 100% key parity).

## [2026-09-09] — Profile Suite Modernization
### Added
- **Hero Identity Card** `profile/edit.blade.php` — Gradient banner + avatar (photo or initial monogram), name, email, role/verification/status badges, "Member since" chip.
- **2-Column Responsive Grid** — `lg:grid-cols-2`: left = Profile Info & Photo, right = Password + Danger Zone stacked.
- **Anti-Lockout UI Guard** `delete-user-form.blade.php` — Super-Admin sees amber warning card with shield SVG instead of delete button.
- **Photo Upload Suite** `update-profile-information-form.blade.php` — Alpine.js live preview, file name display, remove toggle. Backend: `ProfileController` stores to `profile_photos/`, `ProfileUpdateRequest` validates `photo` (image, max 2048) and `remove_photo`.
- **Success Alerts** on both profile-update and password-update forms.
- **3 New Tests** in `ProfileTest.php` — photo upload, photo removal, Super-Admin deletion guard. (9/9 ✅)
- **Language Parity** — 4 keys added across `ar.json`, `en.json`, `fr.json`. Total: 436 keys each, 0 diffs.

## [2026-09-09]
### Added — Standardized Semantic Color Palette & Unified UI Token Architecture
- **NEW Component** `resources/views/components/badge.blade.php` — Polymorphic status badge component (`<x-badge>`) supporting 6 semantic color variants (`primary`, `success`, `danger`, `warning`, `info`, `neutral`), dual sizing (`sm`, `md`), and optional status dot (`:dot="true"`) with animated ping pulse (`:dot-ping="true"`).
- **NEW Component** `resources/views/components/alert.blade.php` — Universal alert & flash message banner (`<x-alert>`) supporting all 6 semantic color variants, integrated variant-specific SVGs, and dismissible Alpine.js transition wrapper.
- **ENHANCED** `resources/views/components/auth-session-status.blade.php` — Refactored to leverage `<x-alert variant="success">`.
- **REFACTORED** System & Database Explorer domain views:
  - `users.blade.php`: Replaced raw blue session badge and hardcoded status indicators with `<x-badge variant="info">`, `<x-badge variant="success" :dot="true">`, and `<x-badge variant="danger" :dot="true">`. Upgraded flash message to `<x-alert variant="success">`.
  - `roles.blade.php`: Standardized header counters to `<x-badge variant="info">` (Roles) and `<x-badge variant="success">` (Permissions), permissions catalog badge to `<x-badge variant="success">`, and protected role indicator to `<x-badge variant="warning">`.
  - `queues.blade.php`: Replaced disparate queue count styling with standardized `<x-badge>` components and unified queue name badges to `info`.
  - `pruning.blade.php`: Upgraded pruning status indicators with pulsing ping dot `<x-badge variant="success" :dot="true" :dot-ping="true">` and `<x-badge variant="danger" :dot="true">`, and standardized error/status banners with `<x-alert>`.
  - `cache.blade.php`: Unified cached items and active locks badges with `<x-badge>`, and standardized expiration status cells to `<x-badge variant="danger" :dot="true">` and `<x-badge variant="success" :dot="true">`.
  - `backups.blade.php`: Replaced raw snapshot role tags with `<x-badge variant="warning">` (Oldest) and `<x-badge variant="success">` (Latest), and unified flash alerts with `<x-alert>`.
  - `notifications.blade.php`: Standardized read/unread status tags to `<x-badge variant="neutral">` and `<x-badge variant="success" :dot="true">`.
  - `activity-log.blade.php`: Dynamically mapped audit events (`created` -> `success`, `updated` -> `warning`, `deleted` -> `danger`, default -> `info`) to `<x-badge>`.
  - `index.blade.php`: Harmonized metric card icons to unified Indigo token, unified live system tables indicator to `<x-badge variant="primary" :dot="true" :dot-ping="true">`, and standardized recent activities event badges.
- **STANDARDIZED** Dashboard & Profile cards (`dashboard.blade.php`, `profile/edit.blade.php`) to design token standard `rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-sm`.
- **ELIMINATED** Ad-hoc color drift (random `blue-*` classes replaced with consistent `indigo-*` forensic tokens).
- **ENHANCED** `resources/views/components/badge.blade.php` — Removed redundant inner wrapper span around `$slot` to allow child SVG icons and text labels to directly inherit parent flex layout, ensuring precise `gap-1.5` spacing and vertical centering.
- **ENHANCED** User Profile Popovers (`navigation-ltr.blade.php`, `navigation-rtl.blade.php`) — Refactored role badge display to loop over all assigned roles (`@foreach`), localized `{{ __('Super-Admin') }}`, and synchronized across language dictionaries.
- **Trilingual Dictionary Parity (Rule 17):** Maintained 100% key parity across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (432 keys each, 0 diffs).
- **REMOVED** Redundant middle quick links section (`Dashboard` and `System Tables`) from the user dropdown popover in both `resources/views/layouts/navigation-ltr.blade.php` and `resources/views/layouts/navigation-rtl.blade.php`.
- **ENHANCED** Popover card flow: now transitions directly and cleanly from the signature oval pill button (`Manage your Account`) to the bottom footer card (`Log Out`), removing visual clutter and duplicate navigation options that are already permanently available on the main top navigation bar.

### Added — User Account Status Toggle (Active/Suspended) & Profile Photo Suite
- **NEW Migration** `database/migrations/2026_09_09_083203_add_status_and_photo_fields_to_users_table.php` — Added `status` (`varchar(32)`, indexed, default `'active'`), `profile_photo_path` (`varchar(2048)`, nullable), and `photo_hash` (`varchar(64)`, nullable) to `users` table.
- **NEW Enum** `app/Enums/AccountStatus.php` — Strongly typed Enum (`Active = 'active'`, `Suspended = 'suspended'`) featuring helper methods `label()`, `color()`, `badgeClass()`, and localized names.
- **ENHANCED** `app/Models/User.php`:
  - Added `status`, `profile_photo_path`, `photo_hash` to `$fillable`.
  - Added `status => AccountStatus::class` to `$casts`.
  - Added `status` to `$filterable` for dynamic query filtering via `FilterableTrait`.
  - Added helper methods `isActive(): bool`, `isSuspended(): bool`, and `getProfilePhotoUrlAttribute(): ?string`.
- **ENHANCED** `app/Http/Requests/Auth/LoginRequest.php` — Added authentication security interceptor: prevents suspended users from logging in, immediately clears rate limiter, and throws localized `ValidationException` (*"Your account is suspended. Please contact the administrator."*).
- **ENHANCED** `app/Http/Requests/CreateSystemUserRequest.php` & `UpdateSystemUserRequest.php` — Added validation rules for `status` (`Rule::enum(AccountStatus::class)`), `profile_photo_path` (`nullable|string|max:2048`), and `photo_hash` (`nullable|string|max:64`). Added self-suspension guard in `UpdateSystemUserRequest` to prevent the authenticated user from suspending their own active account.
- **NEW Route & Controller Method** `POST /system-tables/users/{user}/toggle-status` (`system-tables.users.toggle-status`) -> `SystemTableController::toggleUserStatus()` — Toggles user status between active and suspended with self-suspension guard, Spatie activity logging, and flash notifications.
- **ENHANCED** `SystemTableService::getUsers()` — Added `$status` parameter allowing direct filtering by account status (`active`, `suspended`).
- **ENHANCED** `resources/views/components/crud-modal/form.blade.php` — Added `enctype` prop support (passing `enctype="multipart/form-data"` to inner form elements) allowing file uploads in modal dialogs.
- **ENHANCED** `app/Http/Requests/CreateSystemUserRequest.php` & `UpdateSystemUserRequest.php` — Added validation rules for uploaded photo files (`photo => nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120`) and photo removal (`remove_photo => nullable|boolean`).
- **ENHANCED** `SystemTableController` — Updated `storeUser()` and `updateUser()` to automatically store uploaded photos to `storage/app/public/photos`, delete obsolete files upon replacement or removal (`remove_photo`), and update `profile_photo_path`.
- **ENHANCED** `resources/views/system/users.blade.php`:
  - **Interactive Photo Picker Button & Preview:** Added standardized `<x-secondary-button>` ("Choose Photo" / "اختيار صورة") triggering native file dialog with instant round avatar preview, selected file name display, and remove/clear button in both Create User and Edit User modals.
  - **Dual Mode Support:** Users can either click the button to select an image from their device or enter a custom path/URL directly.
  - **Avatar Rendering:** Displays user profile photo when `profile_photo_path` is present, with subtle shadow and fallback to standardized letter avatars.
  - **Account Status Badge:** Distinct semantic badges (Emerald for Active, Rose for Suspended).
  - **Quick Status Toggle Action Button:** Interactive toggle button in table row actions with safety confirmation modal and self-suspension prevention.
  - **Account Status Filter:** Added status filter dropdown (`All`, `Active`, `Suspended`) to the table toolbar.
- **Trilingual Localization (Rule 17):** Synchronized 7 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (`Choose Photo`, `Remove Photo`, `Profile Photo (Optional)`, `PNG, JPG, WEBP up to 5MB`, etc. — 428 keys each, 100% parity, 0 missing).
- **Automated Tests:** Added `test_authenticated_admin_can_upload_photo_file_for_user` and `test_authenticated_admin_can_remove_photo_via_edit` to `SystemTableTest.php` (All 206 tests passing, 842 assertions).

### Added — Unified CRUD Suite Architecture & Translation Parity Audit
- **NEW** `resources/views/components/crud-modal/delete.blade.php` — Unified delete confirmation modal. Accepts Alpine variable names for `show`, `action-url`, `item-name`. Replaces 4+ hand-coded delete modals across the system explorer.
- **NEW** `resources/views/components/crud-modal/form.blade.php` — Unified create/edit form modal. Supports `POST`/`PUT` methods, dynamic Alpine action URL via `alpine-action` prop, named `$hidden` slot, configurable icon colors (orange/amber/indigo/emerald/rose). Replaces 5+ hand-coded form modals.
- **NEW** `resources/views/components/crud-modal.blade.php` — Blade anonymous component group root wrapper.
- **NEW** `DELETE /system-tables/users/{user}` route (`system-tables.users.destroy`) with corresponding `SystemTableController::destroyUser()` method. Includes activity logging and flash confirmation.
- **ENHANCED** `resources/views/components/table/action-delete.blade.php` — Added `action-url`, `confirm-message`, and `method` props enabling self-contained, inline delete form submission without manual form wrapping.
- **NEW** `SystemTableService::translateActivityDescription()` — Robust pattern-matching localization engine translating dynamic and parameterized forensic audit descriptions into Arabic, English, and French.
- Injected `translated_description` via `through()` in `getActivityLogs()` and `map()` in `getRecentActivities()`.

- **Backup Storage Test Isolation:** Resolved root cause of backup files disappearing without user action. Previously, `tests/Feature/DatabaseBackupTest.php` ran `File::cleanDirectory()` against the shared application backup directory (`storage/app/private/Laravel`). Isolated the test suite to use a dedicated `TestingBackup` namespace (`config(['backup.backup.name' => 'TestingBackup'])`), safeguarding real backups permanently.
- **Localization Audit (Rule 17):** Conducted deep project-wide translation audit across `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
  - Removed 12 duplicate keys from each language file.
  - Added missing keys (`Form`, `Create`, `Create New User`).
  - Added 21 new activity log description translation keys with full placeholder support.
  - Alphabetically sorted and normalized all 3 dictionaries.
  - Verified 100% key parity (387 keys in each file, 0 missing, 0 duplicate keys).

### Added — Automated Table Discovery & Dynamic Permission Matrix Architecture
- **NEW** `app/Services/PermissionDiscoveryService.php` — Pure Live Schema Introspection Engine. Automatically inspects the active database schema without any hardcoded entities, applies strict system blacklists (excluding migrations, queue, cache, forensic, and Spatie tables), discovers physical business tables (currently `users`), prunes obsolete permissions for non-existent entities (such as phantom `instruments`), generates 4 standard CRUD permissions per discovered entity (`view`, `create`, `edit`, `delete`), and groups database permissions into a structured matrix.
- **NEW** `app/Console/Commands/SyncTablePermissionsCommand.php` — Artisan command (`php artisan permissions:sync-tables [--dry-run]`) to introspect database schema, generate standard permissions, prune obsolete permissions for dropped/non-existent tables, and automatically synchronize all active permissions to the `Super-Admin` role.
- **NEW** `tests/Unit/PermissionDiscoveryServiceTest.php` — Unit test suite covering database introspection, blacklist filtering, CRUD generation, Super-Admin syncing, stale permission pruning, real-time auto-sync, and matrix grouping (7 tests, 50 assertions).
- **NEW** `tests/Feature/SyncTablePermissionsCommandTest.php` — Feature test suite verifying CLI dry-run mode, table generation, obsolete permission pruning, and Super-Admin synchronization (3 tests, 13 assertions).
- **ENHANCED** `app/Http/Controllers/SystemTableController.php` — Injected `PermissionDiscoveryService`, passed dynamic `$permissionMatrix` to `roles()` view, and implemented Anti-Lockout guards in `updateRole` and `destroyRole` preventing modification or deletion of the `Super-Admin` role.
- **ENHANCED** `resources/views/system/roles.blade.php` — Replaced flat permission lists in Create Role and Edit Role modals with an interactive, responsive Dynamic Permission Matrix table. Converted the raw Permissions Catalog table into a collapsible, on-demand card hidden by default (`showPermissionsCatalog`), positioned above `Configured Roles`. Fixed icon spacing in the Permissions Catalog and Configured Roles headers with explicit `me-3.5` margin and compiled CSS utilities to ensure optimal visual separation in both RTL and LTR viewports.
- **ENHANCED** `resources/views/components/crud-modal/form.blade.php` — Added `3xl` (`sm:max-w-3xl`) support to `maxWidth` prop for comfortable matrix rendering.
- **Trilingual Localization (Rule 17):** Added 17 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (408 keys per dictionary, 100% parity, 0 missing).

### Added — Database Backups Action Buttons Verification & Table Component Suite Expansion
- **NEW** `resources/views/components/table/action-download.blade.php` — Standardized download action component adhering to Rule 15.
- **NEW** `resources/views/components/table/action-restore.blade.php` — Standardized database snapshot restore action component adhering to Rule 15.
- **ENHANCED** `resources/views/components/table/action.blade.php` — Added `buttonType` prop support (`buttonType="button"`, passes through to `<button>`), and integrated native support for `type="download"` (Indigo Info theme + download SVG) and `type="restore"` (Amber Warning theme + rotate restore SVG).
- **FIXED** `resources/views/components/table/action-delete.blade.php` — Resolved critical bug where duplicate `type="delete" type="submit"` caused `$type` to become `'submit'`, resulting in default indigo styling, eye icon, and `<button type="button">` which prevented form submission. Now explicitly passes `button-type="submit"` allowing valid destructive form dispatching.
- **ENHANCED** `resources/views/system/backups.blade.php` — Replaced ad-hoc raw `<a>` and `<button>` tags with standardized `<x-table.action-download>` and `<x-table.action-restore>`. Added Alpine.js double-click protection (`isCreating`, `isRestoring`) with animated loading spinners to prevent concurrent backup creations or multiple point-in-time state overwrites.
- **Trilingual Localization (Rule 17):** Added `Download`, `Restore`, `Creating Backup...`, and `Restoring Database...` across `lang/ar.json`, `lang/en.json`, and `lang/fr.json`. Re-verified 100% key parity (391 keys per dictionary, 0 missing).

### Refactored
- `resources/views/system/users.blade.php` — replaced 2 hand-coded inline modals (Create User, Edit User) + added unified delete modal. Reduced from 428 to ~240 lines.
- `resources/views/system/roles.blade.php` — replaced 6 hand-coded inline modals (Create Role, Edit Role, Delete Role, Create Permission, Edit Permission, Delete Permission) with `<x-crud-modal.form>` and `<x-crud-modal.delete>`. Reduced from 689 to ~250 lines.
- `resources/views/system/backups.blade.php` — refactored all action buttons to comply with Rule 14 & Rule 15.
- `resources/views/system/activity-log.blade.php` & `resources/views/system/index.blade.php` — updated to render `$activity->translated_description`.

### Tests
- All 197 tests pass with 795 assertions (100% passing).
- Laravel Pint formatting verified clean.

---

## [2026-09-06]

### Added
- Established project-wide documentation enforcement rules in [.antigravityrules](file:///d:/HARD%20Project/tools.gmtm-dz.com/.antigravityrules), [AGENTS.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/AGENTS.md), and [CLAUDE.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/CLAUDE.md).
- Initialized [docs/project_state.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/docs/project_state.md) with baseline schema, routes, models, and dependencies snapshot.
- Initialized [docs/ARCHITECTURE_LOG.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/docs/ARCHITECTURE_LOG.md) documenting ADR-001 (Clean Architecture/Thin Controllers) and ADR-002 (Enforced AI Documentation Protocol).
- Configured Antigravity MCP integration for Laravel Boost globally in `~/.gemini/config/mcp_config.json` and as a workspace plugin in `.agents/plugins/laravel-boost/`.
- Implemented Core Foundation Layer: [ApiResponseTrait](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Traits/ApiResponseTrait.php), [BaseRepositoryInterface](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Interfaces/BaseRepositoryInterface.php), [BaseRepository](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Repositories/BaseRepository.php), and [BaseService](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/BaseService.php).
- Implemented User Module with Clean Architecture: [UserRepositoryInterface](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Interfaces/UserRepositoryInterface.php), [UserRepository](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Repositories/UserRepository.php), and [UserService](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/UserService.php).
- Created and registered [RepositoryServiceProvider](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Providers/RepositoryServiceProvider.php) in [bootstrap/providers.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/bootstrap/providers.php).
- Added comprehensive unit and feature tests covering `ApiResponseTrait`, `UserRepository`, and `UserService`.
- Installed [intervention/image](file:///d:/HARD%20Project/tools.gmtm-dz.com/composer.json) (v4/v3 API) and removed unreachable `packages.filamentphp.com` repository entry from `composer.json`.
- Implemented [ImageOptimizationService](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/ImageOptimizationService.php) with aspect ratio scaling, alpha preservation, zero-division protection, and safe backup support.
- Created Artisan command [OptimizeImagesCommand](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Console/Commands/OptimizeImagesCommand.php) (`php artisan images:optimize`) with tabular CLI metrics.
- Added comprehensive unit and feature tests: [ImageOptimizationServiceTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Unit/ImageOptimizationServiceTest.php) and [OptimizeImagesCommandTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/OptimizeImagesCommandTest.php).

## [2026-09-07]
### Fixed
- Resolved "No MCP servers installed" issue in Antigravity IDE for Laravel Boost MCP:
  - Added Herd binary path (`C:\Users\HP\.config\herd\bin`) to Windows User `PATH` environment variable.
  - Configured global Antigravity MCP definition in `~/.gemini/config/mcp_config.json` targeting PHP 8.4 binary (`C:\Users\HP\.config\herd\bin\php84\php.exe`) and project artisan path.
  - Updated workspace MCP configurations in [.agents/mcp_config.json](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/mcp_config.json) and [.agents/plugins/laravel-boost/mcp_config.json](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/plugins/laravel-boost/mcp_config.json) with absolute paths to ensure deterministic execution.

### Added
- Integrated `spatie/laravel-permission` (v8.3) for industrial-standard Role-Based Access Control (RBAC):
  - Published and ran RBAC migrations creating 5 tables: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, and `role_has_permissions`.
  - Published [config/permission.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/config/permission.php) for cache configuration and table mappings.
  - Added `HasRoles` trait to [User](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Models/User.php) model.
  - Registered middleware aliases (`role`, `permission`, `role_or_permission`) in [bootstrap/app.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/bootstrap/app.php).
  - Created [RolesAndPermissionsSeeder](file:///c:/Project%20HARD/tools.gmtm-dz.com/database/seeders/RolesAndPermissionsSeeder.php) seeding initial roles (`Super-Admin`, `Admin`, `User`) and initial permissions (`view instruments`, `create instruments`, `edit instruments`, `delete instruments`, `manage users`).
  - Added [RoleAndPermissionTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/RoleAndPermissionTest.php) verifying role assignments, permission inheritance, super-admin rights, and middleware route security.
- Integrated `spatie/laravel-activitylog` (v5.1) for automated audit trail and model event tracking:
  - Published and ran migration creating the `activity_log` table with indexes on subjects, causers, and log names.
  - Published [config/activitylog.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/config/activitylog.php).
  - Integrated `HasActivity` concern into [User](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Models/User.php) model with secure `getActivitylogOptions()` (tracking `name` and `email`, enforcing `logOnlyDirty()`, enabling `dontLogEmptyChanges()`, and strictly omitting credentials).
  - Added [ActivityLogTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/ActivityLogTest.php) validating automatic logging, dirty attribute tracking, password credential omission, and causer resolution.
- Implemented [FileUploadService](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Services/FileUploadService.php) for standardized file storage abstraction:
  - Methods: `uploadFile` (unique UUID naming, extension whitelisting, dangerous executable rejection), `deleteFile`, `replaceFile`, `fileExists`, `getUrl`, and `getSize`.
  - Created public storage symbolic link via `php artisan storage:link`.
  - Added unit test suite in [FileUploadServiceTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Unit/FileUploadServiceTest.php) covering upload, delete, replace, dangerous extension blocking, and metadata inspection.
- Implemented declarative dynamic query filtering architecture via [FilterableTrait](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Traits/FilterableTrait.php):
  - Added `scopeFilter()` supporting whitelisted attribute matching, array matching (`whereIn`), range filters (`from`/`to`, `min`/`max`), dynamic sorting (`sort_by`, `sort_direction`), multi-column keyword search (`search`/`q`), and custom model hook methods (`filter{Field}`).
  - Integrated `FilterableTrait` into [User](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Models/User.php) model declaring `$filterable`, `$searchable`, and custom `filterRole` hook.
  - Extended [BaseRepositoryInterface](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Interfaces/BaseRepositoryInterface.php) and [BaseRepository](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Repositories/BaseRepository.php) with `filter()` and `paginateWithFilter()`.
  - Added comprehensive test suite in [FilterableTraitTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/FilterableTraitTest.php).
- Integrated `spatie/laravel-backup` (v10.3) for automated disaster recovery and database protection:
  - Published [config/backup.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/config/backup.php) configured for MySQL and local disk destination.
  - Configured MySQL connection in [config/database.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/config/database.php) with `dump` options (`dump_binary_path` pointing to MySQL 8.0 binary directory, `use_single_transaction`, and timeout protection).
  - Defined automated scheduled backup routines in [routes/console.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/routes/console.php) (`backup:clean` at 01:00 daily, `backup:run` at 01:30 daily).
  - Added test suite in [BackupConfigurationTest](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/BackupConfigurationTest.php) verifying configuration integrity, Artisan command availability, and cron schedule definitions.
- Implemented Database Notifications System (ADR-012):
  - Generated and ran standard Laravel notifications migration creating the `notifications` table with UUID primary key, polymorphic `notifiable` relation, JSON `data` column, and `read_at` timestamp.
  - Created [SystemActivityAlert](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Notifications/SystemActivityAlert.php): centralized notification class dispatching exclusively over the `database` channel with a unified structured payload (`title`, `message`, `type`, `causer`, `extra`).
  - Created [UserObserver](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Observers/UserObserver.php): captures `created` and `deleted` events on `User`; auto-fetches all `Super-Admin`/`Admin` users via Spatie Permission and dispatches `SystemActivityAlert` to each; gracefully handles environments without seeded roles via `RoleDoesNotExist` catch.
  - Bound `UserObserver` to `User` model using the modern `#[ObservedBy([UserObserver::class])]` PHP attribute in [User.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Models/User.php).
  - Created [NotificationController](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/Api/NotificationController.php) with 5 actions (`index`, `unread`, `markAsRead`, `markAllAsRead`, `destroy`), enforcing per-user notification isolation (404 on cross-user access) and using `ApiResponseTrait` for unified JSON responses.
  - Created [routes/api.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/api.php) with 5 auth-protected API routes registered under `/api/notifications`.
  - Registered `api: __DIR__.'/../routes/api.php'` in [bootstrap/app.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/bootstrap/app.php).
  - Added comprehensive [DatabaseNotificationTest](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/DatabaseNotificationTest.php) (14 test cases, 48 assertions) covering: channel verification, payload structure, observer dispatching on create/delete, admin-only targeting, all 5 API endpoints, and security isolation.
- Implemented URL Localization & Strict Architectural View Isolation (ADR-013):
  - Installed `mcamara/laravel-localization` (v2.4.2) and configured [config/laravellocalization.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/config/laravellocalization.php) supporting `ar` (default, hidden in URL), `en`, and `fr`.
  - Implemented environment-aware browser language detection (`LARAVELLOCALIZATION_USE_ACCEPT_LANGUAGE_HEADER`).
  - Separated layout files into isolated RTL/LTR views: [layouts/app-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/app-rtl.blade.php), [layouts/app-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/app-ltr.blade.php), [layouts/guest-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/guest-rtl.blade.php), and [layouts/guest-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/guest-ltr.blade.php).
  - Separated navigation bars into [layouts/navigation-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php) and [layouts/navigation-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php).
  - Created isolated Tailwind CSS and Vite entrypoints: `resources/css/app-rtl.css`, `resources/css/app-ltr.css`, `resources/js/app-rtl.js`, and `resources/js/app-ltr.js`, bundled in [vite.config.js](file:///d:/HARD%20Project/tools.gmtm-dz.com/vite.config.js).
  - Implemented dynamic component routing in [AppLayout.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/View/Components/AppLayout.php) and [GuestLayout.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/View/Components/GuestLayout.php) enabling automatic RTL/LTR resolution without view refactoring.
  - Created reusable [LanguageSwitcher](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/language-switcher.blade.php) Alpine.js dropdown component with localized alternate URLs and flags.
  - Registered Mcamara middleware aliases and added [SetLocale](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Middleware/SetLocale.php) to the `web` pipeline in [bootstrap/app.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/bootstrap/app.php).
  - Grouped web routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php) under localization group with route name collision prevention (`'as' => "{$locale}."`).
  - Added comprehensive translation dictionaries: [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json), and PHP files for `ar` and `fr` (`auth.php`, `pagination.php`, `passwords.php`, `validation.php`).
  - Added test suite in [LocalizationTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/LocalizationTest.php) (16 tests, 48 assertions).
- Implemented Theme Management Architecture with Zero-FOUC & Alpine.js Tri-State (ADR-014):
  - Configured class-based dark mode (`darkMode: 'class'`) in [tailwind.config.js](file:///d:/HARD%20Project/tools.gmtm-dz.com/tailwind.config.js).
  - Injected synchronous inline zero-FOUC prevention scripts into `<head>` across all layouts: [app-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/app-rtl.blade.php), [app-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/app-ltr.blade.php), [guest-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/guest-rtl.blade.php), [guest-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/guest-ltr.blade.php), and [welcome.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/welcome.blade.php).
  - Created reusable [ThemeSwitcher](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/theme-switcher.blade.php) Alpine.js Blade component managing 3 states: `light`, `dark`, and `system`.
  - Implemented cross-instance reactive synchronization via custom `theme-changed` window event and OS dark mode watcher (`matchMedia('(prefers-color-scheme: dark)')`).
  - Embedded `<x-theme-switcher />` into [navigation-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php) and [navigation-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) (desktop and mobile), guest layouts, and welcome page.
  - Added comprehensive dark mode utility classes across layouts, navigation bars, and [dashboard.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/dashboard.blade.php).
  - Added translation keys (`Theme`, `Light`, `Dark`, `System`) to `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
  - Added [ThemeManagementTest](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/ThemeManagementTest.php) (9 feature tests, 54 assertions).
  - Full test suite: **117/117 tests passing, 374 assertions**.
- Enforced Rule 12: Strict Separation of Concerns (Backend, Frontend & CSS) (ADR-015):
  - Updated project-wide directives in [.antigravityrules](file:///d:/HARD%20Project/tools.gmtm-dz.com/.antigravityrules), [AGENTS.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/AGENTS.md), and [CLAUDE.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/CLAUDE.md).
  - Prohibited inline `style="..."` attributes across all Blade/HTML markup; refactored dropdown menus to utilize pure CSS `[x-cloak]` and Tailwind utility classes.
  - Enforced zero code mixing across Backend (`app/`, `routes/`, `database/`), Frontend (`resources/views/`, `resources/js/`), and CSS layers (`resources/css/`, Tailwind).

## [2026-09-08]
### Refactored
- Aligned Profile Module Architecture & Visual Theming with Project Standards (ADR-016):
  - Refactored [ProfileController](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Http/Controllers/ProfileController.php) to adhere strictly to Clean Architecture (ADR-001, ADR-005): added `declare(strict_types=1);`, injected `UserService` via constructor property promotion, and delegated profile updates and account deletions directly to `UserService` inside safe database transactions.
  - Upgraded [profile/edit.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/edit.blade.php) and sub-views ([update-profile-information-form.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/partials/update-profile-information-form.blade.php), [update-password-form.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/partials/update-password-form.blade.php), [delete-user-form.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/partials/delete-user-form.blade.php)) with comprehensive Tailwind Dark Mode tokens (`dark:bg-gray-800`, `dark:text-gray-100`, `dark:text-gray-200`, `dark:text-gray-400`, `dark:border-gray-700/60`, `dark:shadow-gray-950/50`).
  - Added full Dark Mode support across shared form and layout components: [input-label.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/input-label.blade.php), [text-input.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/text-input.blade.php), [input-error.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/input-error.blade.php), [primary-button.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/primary-button.blade.php), [secondary-button.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/secondary-button.blade.php), [danger-button.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/danger-button.blade.php), [modal.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/modal.blade.php), [dropdown.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown.blade.php), [dropdown-link.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown-link.blade.php), and [responsive-nav-link.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/responsive-nav-link.blade.php).
  - Maintained absolute adherence to Rule 12 (Zero inline styles, zero CSS/JS/PHP mixing).
  - Added dark mode verification feature test in [ProfileTest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/ProfileTest.php).
  - Full test suite: **118/118 tests passing, 378 assertions**.

### Added
- Initialized dedicated image asset directory structure:
  - Created `public/images/` for static branding assets (logos, icons, favicons, graphics) with `.gitkeep`.
  - Created `storage/app/public/instruments/` for user-uploaded instrument and equipment photos with `.gitkeep`.
  - Re-linked and verified Windows junction for `public/storage` pointing to `storage/app/public`, ensuring immediate web browser accessibility to uploaded instruments at `/storage/instruments/...`.
  - Updated `storage/app/public/.gitignore` to track the `instruments/` directory structure across repository checkouts.
- Integrated **ENGI-MATE** Visual Identity & Logo across the application:
  - Updated [application-logo.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/application-logo.blade.php) to render the new `public/images/logo.png`.
  - Integrated branded logo and title into navigation bars ([navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php), [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php)).
  - Upgraded authentication guest layouts ([guest-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/guest-rtl.blade.php), [guest-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/guest-ltr.blade.php)) with high-resolution logo card, brand title, and localized slogan ("Your Engineering Work Assistant").
  - Redesigned [dashboard.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/dashboard.blade.php) welcome card showcasing the new brand avatar, name, and tagline. Optimized logo dimension to a compact, balanced scale (`w-12 h-12` / 48px) with harmonious typography and spacing.
  - Generated and integrated `public/images/logo-dark.png`: seamless transparent Dark Mode variant with luminous white lettering and helmet contrast, preserving the dynamic safety-orange orbit.
  - Upgraded [application-logo.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/application-logo.blade.php) to use HTML5 `<picture>` switching automatically between `logo.png` (Light) and `logo-dark.png` (Dark) using Tailwind's `dark:` classes.
  - Eliminated artificial white background wrappers, shadows, and borders from headers, guest cards, and dashboard, allowing the transparent logo to blend seamlessly directly into both light and dark themes.
- Optimized Navigation Bar Logo Display & Proportions:
  - Trimmed empty transparent bounding margins from `public/images/logo.png` and `public/images/logo-dark.png` (426x426 tight canvas) to maximize optical clarity.
  - Eliminated duplicate adjacent text `<span ...>ENGI-MATE</span>` in [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) and [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php).
  - Scaled the standalone brand logo to a generous, balanced size (`h-12 w-auto sm:h-14`) with smooth micro-hover scale feedback.
  - Rebuilt production frontend assets via `npm run build` to compile responsive Tailwind dimension tokens.

### Removed
- Purged legacy, non-compliant Breeze layout files to maintain clean code and architecture:
  - Deleted `resources/views/layouts/navigation.blade.php` (lacked dark mode, language switcher, theme switcher, and modern logo specifications).
  - Deleted `resources/views/layouts/app.blade.php` and `resources/views/layouts/guest.blade.php` (orphaned single-template layouts completely superseded by ADR-013 isolated RTL/LTR layouts).

### Fixed
- Resolved black text contrast issue on navigation links in Dark Mode:
  - Added comprehensive Tailwind Dark Mode tokens to [nav-link.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/nav-link.blade.php) (`dark:text-gray-100`, `dark:border-indigo-500`, `dark:focus:border-indigo-400` for active states; `dark:text-gray-400`, `dark:hover:text-gray-300`, `dark:hover:border-gray-600` for inactive states).
  - Added `dark:text-green-400` to [auth-session-status.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/auth-session-status.blade.php).
  - Recompiled production assets via `npm run build`.


## [2026-09-08]
### Added
- Implemented **System & Database Tables Explorer** Hub for comprehensive data observability across all 13 application tables:
  - Created backend service [SystemTableService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/SystemTableService.php) encapsulating safe counts, pagination, search, and relations across `users`, `sessions`, `roles`, `permissions`, `activity_log`, `notifications`, `jobs`, `failed_jobs`, `job_batches`, `cache`, `cache_locks`, and `password_reset_tokens`.
  - Created controller [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) providing clean, isolated handlers for overview, users, roles, activity log, notifications, queues, and cache.
  - Registered localized, auth-protected routes under `/system-tables` with `auth` and `verified` middlewares in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php).
  - Built modern, responsive Blade view suite in `resources/views/system/`:
    - [index.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/index.blade.php): Overview dashboard with cards for every table category, total record counts, recent activity feed, and quick links.
    - [users.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/users.blade.php): Users list with search, role badges, verification status, and active sessions tracker.
    - [roles.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/roles.blade.php): Visual matrix of RBAC roles with attached permissions pills and users count.
    - [activity-log.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/activity-log.blade.php): Audit trail table with event badges and an Alpine.js modal inspecting structured attribute delta changes (`old` vs `new`).
    - [notifications.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/notifications.blade.php): Internal database notifications monitor with status filter and JSON payload viewer modal.
    - [queues.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/queues.blade.php): Triple-tab queue dashboard covering pending jobs (`jobs`), failed jobs (`failed_jobs` with full exception stack trace modal), and job batches (`job_batches`).
    - [cache.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/cache.blade.php): Real-time monitor for cached keys, TTL expiration status, and atomic locks (`cache_locks`).
    - [system-tabs.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/system-tabs.blade.php): Modern vertical sidebar navigation component with icons, active state highlights, and status indicator widget embedded in a two-column responsive layout.
  - Integrated "System Tables" navigation link across desktop and mobile menus in [navigation-rtl.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php) and [navigation-ltr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php).
  - Added complete trilingual localization strings in [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json).
  - Added comprehensive automated test suite [SystemTableTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) with 8 tests and 41 assertions (100% passing).
  - Established **Rule 13 (Strict Security Quarantine & Mandatory Developer Verification)** in [AGENTS.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/AGENTS.md), `.ai/rules/services.md`, and [docs/project_state.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/docs/project_state.md): Strictly isolates the System Tables & Security Explorer module from regular user pages, and mandates that any additions or changes must be vetted by asking the developer beforehand.
  - Implemented Standardized Semantic Button & Color Token System (ADR-019, Rule 14):
    - Re-styled and branded [primary-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/primary-button.blade.php) with ENGI-MATE Safety Orange (`bg-orange-500`, `dark:bg-orange-600`).
    - Standardized [secondary-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/secondary-button.blade.php) with neutral bordered styling for cancel/dismiss/close actions.
    - Standardized [danger-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/danger-button.blade.php) with Rose/Red styling for destructive operations.
    - Created [success-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/success-button.blade.php) (Emerald) for approvals and positive completions.
    - Created [warning-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/warning-button.blade.php) (Amber) for cautionary actions and retries.
    - Created [info-button.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/info-button.blade.php) (Indigo) for data inspection, payload viewing, and mutation diffs.
    - Enforced **Rule 14 (Mandatory Unified Button & Semantic Color System)** in [AGENTS.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/AGENTS.md) and `.ai/rules/views.md` strictly prohibiting ad-hoc raw buttons.
  - Created and Standardized the Unified Table Component Suite (ADR-020):
    - Implemented [table.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table.blade.php) (`<x-table>`) as a self-contained card container with optional toolbar, sticky/scrollable horizontal wrapper, header slot, row body slot, and pagination footer.
    - Implemented [table/th.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/th.blade.php) (`<x-table.th>`) with uppercase styling, text alignment, and responsive padding.
    - Implemented [table/tr.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/tr.blade.php) (`<x-table.tr>`) with subtle hover micro-interactions in Light and Dark modes.
    - Implemented [table/td.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/td.blade.php) (`<x-table.td>`) with aligned typography and vertical cell centering.
    - Implemented [table/empty.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/empty.blade.php) (`<x-table.empty>`) with empty state icon, customizable colspan, and translated fallback message.
  - Refactored all System & Database Tables Explorer views to exclusively utilize the `<x-table>` component suite:
    - [users.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/users.blade.php): Converted Users and Active Sessions tables.
    - [roles.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/roles.blade.php): Converted Permissions Catalog table.
    - [activity-log.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/activity-log.blade.php): Converted Audit Trail table and filters toolbar.
    - [notifications.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/notifications.blade.php): Converted Notifications table and status selector.
    - [queues.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/queues.blade.php): Converted Pending Jobs, Failed Jobs, and Job Batches subtab tables.
    - [cache.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/cache.blade.php): Converted Cache Entries and Cache Locks tables.
  - Created and Standardized Unified Table Action Buttons Suite (ADR-021):
    - Implemented [actions.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/actions.blade.php) (`<x-table.actions>`) as a standardized flex wrapper for row actions.
    - Implemented [action.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/action.blade.php) (`<x-table.action>`) as a polymorphic component (renders `<a>` if `href` is present, `<button>` otherwise) with dynamic semantic theme classes, embedded SVGs, responsive text/icon-only sizing, and tooltips.
    - Implemented semantic action button shortcuts:
      - [action-view.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/action-view.blade.php) (`<x-table.action-view>`): Indigo/Info theme with eye icon for viewing details/payloads/mutations.
      - [action-edit.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/action-edit.blade.php) (`<x-table.action-edit>`): Amber/Warning theme with pencil icon for edits.
      - [action-delete.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/table/action-delete.blade.php) (`<x-table.action-delete>`): Rose/Danger theme with trash icon for deletions/exceptions.
    - Refactored all action buttons in `activity-log.blade.php`, `notifications.blade.php`, and `queues.blade.php` to use the unified table action components.
  - Integrated Context-Aware Unified Actions across the System & Database Tables Explorer:
    - Added "New User" CTA (`<x-primary-button>`) in the Users table toolbar, and unified Row Actions (`<x-table.action-edit>` & `<x-table.action-delete>`) in the Users table.
    - Added "Terminate Session" action (`<x-table.action-delete :title="__('Terminate Session')">`) in the Active Sessions table.
    - Added "New Role" CTA in the Roles section header, and Edit/Delete actions (`<x-table.action-edit>` & `<x-table.action-delete>`) on each Role card.
    - Added "New Permission" CTA in the Permissions catalog toolbar, and Edit/Delete actions (`<x-table.action-edit>` & `<x-table.action-delete>`) in the Permissions table.
    - Added "Cancel Job" action (`<x-table.action-delete :title="__('Cancel Job')">`) in the Pending Jobs table.
    - Enhanced Failed Jobs diagnostics: converted trace inspection to `<x-table.action-view>`, and added "Retry Job" (`<x-table.action type="primary" :title="__('Retry Job')">`) and "Delete Record" (`<x-table.action-delete :title="__('Delete Record')">`).
    - Added "Delete Batch" action (`<x-table.action-delete :title="__('Delete Batch')">`) in the Job Batches table.
    - Added "Forget Key" action (`<x-table.action-delete :title="__('Forget Key')">`) in the Cache Entries table.
    - Added "Release Lock" action (`<x-table.action-delete :title="__('Release Lock')">`) in the Cache Locks table.
    - Preserved tamper-proof forensic immutability on `activity_log` by strictly restricting actions to inspection (`<x-table.action-view>`).
    - Added complete trilingual localization strings for all new action labels and tooltips in [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json).
  - Implemented **Stateful Unified Global Filter Architecture (`<x-global-filter />`)** (ADR-023):
    - Created reusable root component [global-filter.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/global-filter.blade.php) (`<x-global-filter>`) with Alpine.js reactive state, built-in search, instant clear button, query parameter retention, submit button, and automatic reset button shown only when active filters exist.
    - Created sub-components in `resources/views/components/global-filter/`:
      - [search.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/global-filter/search.blade.php) (`<x-global-filter.search>`) for composable search inputs.
      - [select.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/global-filter/select.blade.php) (`<x-global-filter.select>`) for standardized dropdown filters with optional auto-submit.
      - [sort.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/global-filter/sort.blade.php) (`<x-global-filter.sort>`) for sorting controls with ascending/descending toggles.
    - Refactored existing table views to exclusively use the `<x-global-filter>` suite:
      - [users.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/users.blade.php): Integrated global filter with keyword search and reset.
      - [activity-log.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/activity-log.blade.php): Integrated global filter with event dropdown and keyword search.
      - [notifications.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/notifications.blade.php): Integrated global filter with status dropdown and auto-submit.
    - Added automated test suite [GlobalFilterTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/GlobalFilterTest.php) with 7 tests and 27 assertions (100% passing).
    - Expanded trilingual localization with filter terms in [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json).
  - Executed Comprehensive Trilingual Translation Audit & 100% Synchronization (Arabic, English, French):
    - Audited all Blade templates across `resources/views/` and extracted all `__('...')` translation invocations.
    - Verified that zero hardcoded Arabic or raw untranslated strings exist outside translation wrappers.
    - Identified and wrapped remaining inline strings in [cache.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/cache.blade.php) (`Expired`, `Active`, `keys`, `locks`).
    - Identified 62 system & table translation keys that were previously missing from [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json) and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json), as well as 16 keys missing from [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json).
    - Added high-quality, professional trilingual translations for all 64 system terms (including technical terms: TTL, Atomic Locks, UUID, User Agent, Stack Trace, etc.).
    - Synchronized all 3 dictionary files ([lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json)) to an identical count of 186 keys each.
    - Re-ran verification audit confirming: Missing in AR: 0, Missing in FR: 0, Missing in EN: 0 across all views in the application.
  - Established Mandatory Trilingual Localization Across All Views & Features (Strict AI Agent Rule):
    - Enforced rule in [AGENTS.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/AGENTS.md), [.antigravityrules](file:///d:/HARD%20Project/tools.gmtm-dz.com/.antigravityrules), and [.ai/rules/views.md](file:///d:/HARD%20Project/tools.gmtm-dz.com/.ai/rules/views.md).
    - Mandated zero hardcoded strings across all templates, simultaneous registration of all translation keys in `ar.json`, `en.json`, and `fr.json`, zero tolerance for English fallbacks in Arabic/French views, and pre-completion audit verification before any task is marked done.
  - Implemented Unified Automated Data Pruning & Lifecycle Management System (ADR-025):
    - Created configuration file [config/pruning.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/config/pruning.php) with master toggle, chunk size, sovereign protected tables blacklist, and per-table lifecycle definitions for `activity_log`, `notifications`, and `failed_jobs`.
    - Created central service [DataPruningService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/DataPruningService.php) with dual pruning strategies (date-based retention and count-based maximum capacity), memory-safe chunked batch deletion, hardcoded immutable sovereign table blacklist, dry-run mode, and Spatie activity log integration.
    - Created Artisan command [DataPruneCommand.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Console/Commands/DataPruneCommand.php) (`php artisan data:prune`) with `--table`, `--dry-run`, and `--chunk` options and structured ASCII table reporting.
    - Scheduled automated execution in [routes/console.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/console.php) daily at 02:00 midnight without overlapping.
    - Added comprehensive Feature test suite [DataPruningTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/DataPruningTest.php) with 9 tests and 38 assertions (100% passing; project total: 142/142 tests passing).
    - Synchronized trilingual translations in [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (193 keys each, 0 missing).
  - Implemented Manual Settings & Control Dashboard for Automated Data Pruning (ADR-026):
    - Created database migration `2026_09_08_220000_create_system_settings_table.php` and Eloquent model [SystemSetting.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Models/SystemSetting.php) with JSON value casting and Cache integration.
    - Enhanced [DataPruningService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/DataPruningService.php) with dynamic settings resolution (`getEffectiveConfig()`), custom settings persistence (`saveCustomSettings()`), default reset (`resetCustomSettings()`), and audit history retrieval (`getPruningHistory()`).
    - Created Form Request [UpdatePruningSettingsRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/UpdatePruningSettingsRequest.php) enforcing validation bounds on chunk sizes, retention days, and max capacity limits.
    - Updated [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) with 5 action endpoints: `pruningSettings`, `updatePruningSettings`, `dryRunPruning`, `executePruning`, and `resetPruningSettings`.
    - Registered 5 secure routes under `/system-tables/pruning` protected by `auth` and `verified` in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php).
    - Added "Data Pruning" tab to the System Tables navigation sidebar in [system-tabs.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/system-tabs.blade.php).
    - Built comprehensive dashboard view [pruning.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/pruning.blade.php) featuring master engine switch card, per-table policy cards (`activity_log`, `notifications`, `failed_jobs`), standardized action buttons suite (`<x-primary-button>`, `<x-info-button>`, `<x-danger-button>`, `<x-secondary-button>`), Alpine.js modals for live dry-run simulation and execution/reset confirmations, and audit trail table (`<x-table>`).
    - Synchronized all new UI strings across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (256 keys each, 0 missing).
  - Implemented Dynamic Database Table Onboarding for Data Pruning & Lifecycle Management (ADR-027):
    - Upgraded [DataPruningService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/DataPruningService.php) with multi-driver schema table discovery (`getAvailableDatabaseTables`), eligible candidate filtering (`getEligibleTablesForPruning`), custom table registration (`addCustomTable`), and custom table detachment (`removeCustomTable`).
    - Enforced hardcoded sovereign table blacklist protection (`users`, `roles`, `permissions`, `sessions`, `jobs`, `cache`, `migrations`) preventing critical tables from ever being onboarded.
    - Normalized database table names across SQLite/MySQL drivers by stripping schema/database prefixes (`main.*`, `gmtmdz_tools.*`).
    - Created Form Request [AddPruningTableRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/AddPruningTableRequest.php) validating table existence, column specifications, retention limits, and sovereign table rejections.
    - Enhanced [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) with `addPruningTable` and `removePruningTable` actions, dynamic eligible tables discovery, and per-table live record counts.
    - Registered secure management routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php): `POST /system-tables/pruning/tables` and `DELETE /system-tables/pruning/tables/{table}`.
    - Updated dashboard view [pruning.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/pruning.blade.php) with "Add Table to Pruning" button, dynamic custom table cards with delete triggers, and an Alpine.js modal with dynamic table selection, automatic column suggestions, retention bounds, and sovereign warning notice.
    - Synchronized all 17 new UI and response strings in [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (273 keys each, exact 1-to-1 parity, 0 missing).
    - Created Feature test suite [DynamicTablePruningTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/DynamicTablePruningTest.php) with 6 tests and 44 assertions (100% passing; project total: 154/154 tests passing).
  - Implemented **Database Backup & Point-in-Time Disaster Recovery Management System (ADR-028)**:
    - Created central service [DatabaseBackupService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/DatabaseBackupService.php) wrapping `Spatie\Backup\BackupDestination` for local storage backup listing, sorting, size formatting, and metadata analysis.
    - Implemented automatic classification of oldest (`is_oldest = true`) and latest (`is_newest = true`) database snapshots.
    - Added on-demand database backup creation (`createBackup`) invoking `backup:run --only-db` and logging to `spatie/laravel-activitylog`.
    - Implemented secure download and file deletion with strict path traversal prevention (`sanitizeFileName`).
    - Implemented point-in-time database restoration (`restoreBackup` and `restoreOldestBackup`) utilizing `ZipArchive`, temporary directory extraction, database driver detection (disabling foreign keys on MySQL), and executing database dumps via `DB::unprepared`.
    - Updated [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) with 6 actions: `backups`, `createBackup`, `downloadBackup`, `deleteBackup`, `restoreBackup`, and `restoreOldestBackup`.
    - Added 6 quarantine routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php) under `/system-tables/backups` protected by `auth` and `verified` middleware.
    - Added "Database Backups" navigation tab in [system-tabs.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/components/system-tabs.blade.php).
    - Built comprehensive administrative dashboard [backups.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/backups.blade.php) featuring storage metric cards, primary action button (`<x-primary-button>`) for instant backup generation, warning button (`<x-warning-button>`) for instant restoration of oldest snapshot, standardized table suite (`<x-table>`) with download, restore, and delete action triggers, visual badges for oldest/latest snapshots, and high-visibility Alpine.js red confirmation modal with database overwrite warnings.
    - Synchronized all 41 new translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (314 keys each, exact 1-to-1 key parity, 0 missing).
    - Compiled production frontend assets via `npm run build`.
    - Created Feature test suite [DatabaseBackupTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/DatabaseBackupTest.php) with 11 tests and 57 assertions (100% passing; project total: 165/165 tests passing).
  - Implemented **Administrative User Creation System in System Tables Explorer (ADR-029)**:
    - Created Form Request [CreateSystemUserRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/CreateSystemUserRequest.php) with rigorous validation for name, unique email, confirmed password rules, and valid role exists checks.
    - Injected [UserService.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Services/UserService.php) into [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) and implemented `storeUser` action with transaction registration, Spatie role assignment, and audit logging to `spatie/laravel-activitylog` (`system_users`).
    - Added secure route `POST /system-tables/users` (`system-tables.users.store`) in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php).
    - Enhanced [users.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/users.blade.php) with an interactive Alpine.js modal (`showCreateModal`), form validation retention, role selection dropdown from seeded roles, and flash status messages.
    - Synchronized all 10 new translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (324 keys each, exact 1-to-1 key parity, 0 missing).
    - Added feature tests in [SystemTableTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) verifying guest access rejection, admin user creation with roles and audit logging, and input validation bounds (project total: 168/168 tests passing, 643 assertions).
  - Implemented **Administrative User Editing System in System Tables Explorer (ADR-030)**:
    - Created Form Request [UpdateSystemUserRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/UpdateSystemUserRequest.php) with unique email validation ignoring current user ID (`Rule::unique('users')->ignore($userId)`), optional confirmed password bounds, and role exists checks.
    - Added `updateUser` action in [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) supporting profile updates, optional password hashing, Spatie role syncing, and audit trail logging in `spatie/laravel-activitylog` (`system_users`).
    - Added secure route `PUT /system-tables/users/{user}` (`system-tables.users.update`) in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php).
    - Wired row action button `<x-table.action-edit>` to open an interactive Alpine.js modal (`showEditModal`) pre-populated with user data, dynamic role selection, and optional password update.
    - Synchronized all 9 new translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (333 keys each, exact 1-to-1 key parity, 0 missing).
  - Implemented **Administrative Role and Permission Creation System in System Tables Explorer (ADR-031)**:
    - Created Form Request [CreateRoleRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/CreateRoleRequest.php) validating unique role names and optional permissions array.
    - Created Form Request [CreatePermissionRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/CreatePermissionRequest.php) validating unique permission names.
    - Implemented `storeRole` and `storePermission` action endpoints in [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) with Spatie RBAC integration, web guard enforcement, and structured activity logging (`roles_permissions`).
    - Added secure quarantine routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php): `POST /system-tables/roles` (`system-tables.roles.store`) and `POST /system-tables/permissions` (`system-tables.permissions.store`).
    - Enhanced [roles.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/roles.blade.php) with interactive Alpine.js modals (`showRoleModal` and `showPermissionModal`), sticky validation error retention (`form_type`), scrollable checkbox matrix of existing permissions, and flash status message alert.
    - Synchronized all 15 new translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (348 keys each, exact 1-to-1 key parity, 0 missing).
    - Compiled production assets via `npm run build`.
    - Added 4 feature tests in [SystemTableTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) verifying guest rejection, admin creation of roles with permissions, creation of permissions, and unique name validation (project total: 175/175 tests passing, 674 assertions).
  - Implemented **Administrative Role and Permission Editing System in System Tables Explorer (ADR-032)**:
    - Created Form Request [UpdateRoleRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/UpdateRoleRequest.php) validating unique role names (ignoring current role ID) and optional permissions array.
    - Created Form Request [UpdatePermissionRequest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Requests/UpdatePermissionRequest.php) validating unique permission names (ignoring current permission ID).
    - Implemented `updateRole` and `updatePermission` action endpoints in [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) with Spatie RBAC permission synchronization and activity auditing in `spatie/laravel-activitylog` (`roles_permissions`).
    - Added secure quarantine routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php): `PUT /system-tables/roles/{role}` (`system-tables.roles.update`) and `PUT /system-tables/permissions/{permission}` (`system-tables.permissions.update`).
    - Wired row action button `<x-table.action-edit>` on each role card and permission table row in [roles.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/roles.blade.php).
    - Built two dedicated interactive Alpine.js modals (`showEditRoleModal` and `showEditPermissionModal`) with reactive permission checkbox toggling (`isPermissionSelected`, `togglePermission`), pre-populated input bindings, sticky validation error retention, and standardized buttons (`<x-primary-button>` and `<x-secondary-button>`).
    - Synchronized all 6 new translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (354 keys each, exact 1-to-1 key parity, 0 missing).
    - Compiled production assets via `npm run build`.
    - Added 4 feature tests in [SystemTableTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) verifying guest rejection, admin update of roles with permissions, update of permissions, and unique name validation rules ignoring self (project total: 179/179 tests passing, 694 assertions).
  - Implemented **Administrative Role and Permission Deletion System in System Tables Explorer (ADR-033)**:
    - Added `destroyRole` and `destroyPermission` action endpoints in [SystemTableController.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php); both capture the entity name before hard-deletion and write a structured audit entry to `spatie/laravel-activitylog` (`roles_permissions`).
    - Added secure quarantine routes in [routes/web.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/web.php): `DELETE /system-tables/roles/{role}` (`system-tables.roles.destroy`) and `DELETE /system-tables/permissions/{permission}` (`system-tables.permissions.destroy`).
    - Wired role card and permission row delete buttons (`<x-table.action-delete>`) in [roles.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/system/roles.blade.php) to new Alpine.js `openDeleteRoleModal` and `openDeletePermissionModal` helpers.
    - Added two Alpine.js confirmation modals (`showDeleteRoleModal`, `showDeletePermissionModal`) displaying the entity name via `x-text` binding, using `<x-danger-button>` for confirm and `<x-secondary-button>` for cancel.
    - Synchronized all 6 new delete modal/flash translation keys across [lang/ar.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/ar.json), [lang/en.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/en.json), and [lang/fr.json](file:///d:/HARD%20Project/tools.gmtm-dz.com/lang/fr.json) (360 keys each, exact 1-to-1 key parity, 0 missing).
    - Added 4 feature tests in [SystemTableTest.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) verifying authenticated user can delete a role, authenticated user can delete a permission, guest cannot delete a role (record preserved), and guest cannot delete a permission (record preserved). All 26/26 tests pass (118 assertions).

## [2026-09-09]
### Added
- Implemented **Autonomous Dynamic RBAC Discovery & Maintenance Engine (ADR-037)**:
  - Created [PermissionDiscoveryService.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Services/PermissionDiscoveryService.php) introspecting live database schema tables via `DB::connection()->getSchemaBuilder()->getTableListing()`, filtering internal/system blacklisted tables (`migrations`, `sessions`, `cache`, `jobs`, `activity_log`, Spatie RBAC tables), generating standard CRUD permissions (`view`, `create`, `edit`, `delete`), and auto-syncing to `Super-Admin`.
  - Added stale permission pruning to purge phantom permissions for tables that no longer exist in the schema.
  - Created Artisan command `permissions:sync-tables` ([SyncTablePermissionsCommand.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Console/Commands/SyncTablePermissionsCommand.php)) for manual or CLI execution.
  - Real-time automatic discovery sync invoked seamlessly upon entering `/system-tables/roles`.
  - Transformed Role permissions management in [roles.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/roles.blade.php) into an interactive CRUD matrix grid with entity row toggles and master select-all/deselect-all controls.
  - Collapsed the raw Permissions Catalog table behind an automated status banner, positioned prominently above Configured Roles, expandable on demand (`showPermissionsCatalog`).
  - Implemented anti-lockout defense protecting the `Super-Admin` role at controller and UI levels with a locked system role badge.
- Implemented **User Account Status Lifecycle & Profile Photo Architecture (ADR-038)**:
  - Added `status`, `profile_photo_path`, and `photo_hash` columns to `users` table via migration [2026_09_09_083203_add_status_and_photo_fields_to_users_table.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/database/migrations/2026_09_09_083203_add_status_and_photo_fields_to_users_table.php).
  - Created type-safe string enum [AccountStatus.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Enums/AccountStatus.php) (`active`, `suspended`) with localized labels, semantic colors, and badge classes.
  - Enforced authentication boundary in [LoginRequest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Http/Requests/Auth/LoginRequest.php) blocking suspended users from logging in.
  - Implemented automatic background SHA-256 generation in [UserObserver.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Observers/UserObserver.php) for `photo_hash` whenever `profile_photo_path` changes.
  - Enhanced [users.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/users.blade.php) with an interactive photo picker button (`<x-secondary-button>`), round avatar preview, file name display, quick remove action, status filtering, and row-level account status toggle.
  - Added multipart form handling to `<x-crud-modal.form>` via `enctype="multipart/form-data"` and automatic photo disk management in [SystemTableController.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php).
- Implemented **Fluid Full-Width Container System for High-Density Tabular Data (ADR-039)**:
  - Transitioned the entire application layout architecture from restricted fixed-width containers (`max-w-7xl` / `1280px`) to dynamic 100% full-width containers (`w-full px-4 sm:px-6 lg:px-8`).
  - Liberated ~640px of extra horizontal space on standard 1080p desktop viewports for dense data tables, matrix grids, and side-by-side split layouts.
  - Updated all navigation bars, base layouts, and application views:
    - [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) and [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php)
    - [app-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/app-ltr.blade.php) and [app-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/app-rtl.blade.php)
    - [users.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/users.blade.php)
    - [roles.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/roles.blade.php)
    - [queues.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/queues.blade.php)
    - [pruning.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/pruning.blade.php)
    - [notifications.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/notifications.blade.php)
    - [index.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/index.blade.php)
    - [cache.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/cache.blade.php)
    - [backups.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/backups.blade.php)
    - [activity-log.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/activity-log.blade.php)
    - [dashboard.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/dashboard.blade.php)
    - [edit.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/edit.blade.php)
  - Compiled production assets via `npm run build` and verified full test suite (206/206 tests passing, 842 assertions).
- Implemented **Super Roles Authorization Bypass & Anti-Lockout Governance Architecture (ADR-040)**:
  - Added configurable `$superRoles = ['Super-Admin']` property in [AppServiceProvider.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Providers/AppServiceProvider.php) registering `Gate::before()` callback that automatically grants all authorization abilities to any user possessing a super role, while falling through to standard checks for other roles.
  - Mirrored `$superRoles = ['Super-Admin']` in [PermissionDiscoveryService.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Services/PermissionDiscoveryService.php) with helper methods `getSuperRoles(): array` and `isSuperRole(string $roleName): bool`.
  - Updated automatic CRUD discovery sync in `PermissionDiscoveryService::syncSuperAdminPermissions()` to ensure every configured super role automatically receives all system permissions and is created if missing.
  - Protected all configured super roles against modification and deletion in [SystemTableController.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) (`updateRole` and `destroyRole`) via `isSuperRole()`, replacing hardcoded string checks.
  - Dynamically passed `$superRoles` to [roles.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/roles.blade.php), automatically rendering the locked system role badge (`Protected System Role`) for all super roles and suppressing edit/delete actions.
  - Added `isSuperAdmin(): bool` convenience method to [User.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Models/User.php).
  - Updated CLI sync command [SyncTablePermissionsCommand.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Console/Commands/SyncTablePermissionsCommand.php) to display all synchronized super roles.
  - Added automated feature tests in [RoleAndPermissionTest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/RoleAndPermissionTest.php) and unit tests in [PermissionDiscoveryServiceTest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Unit/PermissionDiscoveryServiceTest.php) (All 210 tests passing, 851 assertions).
- Implemented **System Tables High-Security Quarantine & Super-Admin Exclusivity Lock (ADR-041)**:
  - Restricted the entire `/system-tables` route group exclusively to `Super-Admin` by attaching `role:Super-Admin` middleware in [routes/web.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/routes/web.php). Unauthenticated guests are redirected to login, while authenticated non-super users are denied with HTTP `403 Forbidden`.
  - Completely cloaked and hidden the "System Tables" navigation link from the desktop and mobile menus across both [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) and [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php) via `@if(Auth::user()?->isSuperAdmin() || Auth::user()?->hasRole('Super-Admin'))`.
  - Added comprehensive automated security boundary tests in [SystemTableTest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/SystemTableTest.php) verifying that regular users without `Super-Admin` are forbidden from accessing all system routes and cannot see the navigation links in the dashboard.
  - Full application test suite passing: 212/212 tests passing, 864 assertions.
- Implemented **Pruning Audit Trail Dynamic Operation Localization (ADR-042)**:
  - In [SystemTableController.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/app/Http/Controllers/SystemTableController.php) (`pruningSettings()`), mapped `$history` collection to dynamically translate the `description` string via `SystemTableService::translateActivityDescription($item->description)`.
  - In [pruning.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/system/pruning.blade.php), updated the Operation column cell to render `{{ $item->translated_description ?? $item->description }}`.
  - Ensured all pruning activity logs (`Updated automated data pruning settings`, `Reset automated data pruning settings to defaults`, custom table additions/removals, and auto-pruning executions) resolve to natural localized text in Arabic and French across both LTR and RTL layouts while preserving strict separation of concerns.
  - Added automated feature test in [PruningSettingsTest.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/tests/Feature/PruningSettingsTest.php) (`test_pruning_audit_trail_operation_column_displays_translated_descriptions`) verifying Arabic and French rendering.
  - Full application test suite passing: 213/213 tests passing, 868 assertions.
- Implemented **User Dropdown Navigation Border Radius & Polished Margins Architecture (ADR-043)**:
  - Redesigned the User Settings trigger button in both [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) and [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php), upgrading from a flat unbordered button to an elegant container with `border border-gray-300 dark:border-gray-600 shadow-sm rounded-lg px-3 py-2 gap-2 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500`, harmonizing its design with the theme and language switchers.
  - Added user avatar integration: dynamically displays round user photo (`h-5 w-5 rounded-full object-cover ring-1 ring-gray-300 dark:ring-gray-600`) when `profile_photo_path` is present, or a clean initial avatar badge (`h-5 w-5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 text-xs font-semibold`).
  - Enhanced the dropdown container in [dropdown.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown.blade.php) with `rounded-lg shadow-lg p-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10`.
  - Refined dropdown link items in [dropdown-link.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown-link.blade.php) with `rounded-md px-3 py-2 font-medium hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400`, ensuring hover highlights have clean rounded corners and breathing room from menu edges.
  - Recompiled production frontend assets via `npm run build` and verified full test suite (213/213 tests passing, 868 assertions).
- Implemented **Google-Style Account Popover Card Architecture (ADR-044)**:
  - Transformed the User dropdown in both [navigation-ltr.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-ltr.blade.php) and [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php) into an authentic Google Account popover card (`width="80"` / 320px).
  - Designed circular avatar trigger with modern hover ring (`ring-2 ring-gray-200 dark:ring-gray-700 hover:ring-orange-500 dark:hover:ring-orange-500 p-0.5 rounded-full`) rendering live user photo or initial badge.
  - Implemented Google Hero Card section: Centered user email, large avatar (`w-20 h-20 rounded-full shadow-md ring-4 ring-orange-100 dark:ring-orange-950/60`) with interactive camera overlay button linking to [profile.edit](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/profile/edit.blade.php), personalized greeting (`Hi, :name!`), role badge (`Super-Admin` shield badge or assigned role), and active account status indicator.
  - Added iconic Google pill action button: `"Manage your Account"` (`rounded-full px-5 py-2 border shadow-sm`).
  - Added quick links menu with Dashboard and System Tables shortcuts, plus a dedicated Google-style `"Log Out"` button in the bottom footer.
  - Updated [dropdown.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown.blade.php) to support `w-80` width mapping and `rounded-2xl shadow-xl`.
  - Added user avatar to mobile responsive navigation drawer headers across LTR and RTL layouts.
  - Synchronized 3 new translation keys (`Manage your Account`, `Change Photo`, `Hi, :name!`) across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (431 keys each, 100% parity, 0 diffs).
  - Recompiled production frontend assets via `npm run build` and verified full test suite (213/213 tests passing, 868 assertions).
- Implemented **Dropdown Viewport Bounds & Logical RTL Positioning Architecture (ADR-045)**:
  - Resolved dropdown frame overflow bug where the Google popover card in RTL layouts projected outside the left edge of the viewport.
  - In [dropdown.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/components/dropdown.blade.php), corrected the `$alignmentClasses` mapping so both `'right'` and `'end'` cleanly resolve to `end-0` (`ltr:origin-top-right rtl:origin-top-left end-0`). Under Tailwind's logical properties, `end-0` compiles to `right: 0` in LTR (expanding leftward towards page center) and `left: 0` in RTL (expanding rightward towards page center), ensuring dropdowns situated on the navigation toolbar always project inward into the page frame.
  - In [navigation-rtl.blade.php](file:///c:/Project%20HARD/tools.gmtm-dz.com/resources/views/layouts/navigation-rtl.blade.php), changed `<x-dropdown align="left">` to `<x-dropdown align="right">`, removing the inverted `start-0` anchoring.
  - Added safety viewport constraint `max-w-[calc(100vw-2rem)]` on `<x-dropdown>` container preventing horizontal clipping or layout breaks on constrained viewports.
  - Recompiled production frontend assets via `npm run build` and verified full test suite (213/213 tests passing, 868 assertions).
