# Project State: tools.gmtm-dz.com

**Last Updated:** 2026-09-10
**Status:** Automatic Migration Engine & Zero-State Super Admin Gate Active

---

## 1. Application Overview
- **Framework:** Laravel 13.x (PHP 8.4+)
- **Application Brand:** ENGI-MATE ("Your Engineering Work Assistant")
- **Frontend Stack:** Blade + Tailwind CSS (Class-based Dark Mode) + Alpine.js + Vite (Isolated RTL/LTR bundles)
- **Locales Supported:** `ar` (Arabic, default, hidden prefix), `en` (English, `/en/`), `fr` (French, `/fr/`)
- **Themes Supported:** `light`, `dark`, `system` (Zero-FOUC prevention script, Alpine.js reactive store, cross-instance sync)
- **Database Engine:** MySQL (`gmtmdz_tools2` active, supports zero-touch auto-migration from fresh state)
- **Authentication:** Laravel Breeze (Session/Blade based with active `MustVerifyEmail` contract; registration immediately dispatches verification emails via `${APP_NAME} <${MAIL_FROM_ADDRESS}>`; registration routes guarded by dynamic cache-backed `EnsureRegistrationIsOpen` middleware)
- **Automatic Migration & Auto-Creation Engine:** `EnsureDatabaseIsMigrated` middleware inspects pending schema and database existence. Automatically provisions missing MySQL/SQLite/PostgreSQL databases via raw PDO, runs `migrate --force`, `db:seed`, and permission discovery without user prompt.
- **Database Error Fallback View:** `resources/views/errors/database.blade.php` rendered with HTTP 503 if database connection or auto-creation fails, preventing 500 crashes and offering connection diagnostics and troubleshooting steps.
- **Zero-State Super Admin Setup Gate:** `EnsureSuperAdminExists` middleware intercepts all web traffic on zero-user databases, redirecting to `/system-tables/setup` to provision Super Admin, then permanently locks the route (404/403).
- **Test Suite:** 261 tests, 1109 assertions (100% passing)
- **Localization Parity:** Trilingual dictionary parity across Arabic, English, and French (559 keys each, 0 missing)
- **Roles & Permissions Architecture:** Configured Roles section modernized to Unified Table Component (`<x-table>`) with localized functional titles, scopes, and privilege previews.
- **Notifications Architecture:** Database notifications explorer updated with localized alert types, semantic action badges, payload title translation (`{{ __($notificationTitle) }}`), and preview modals with 100% key parity across AR, EN, and FR.
- **System Settings Engine:** Enterprise key-value runtime configuration engine with zero-latency persistent caching (`86400` TTL), atomic cache updates, and Super-Admin interactive dashboard.
- **Anti-Self-Action Security Policy:** Administrators are strictly prohibited from self-demotion/role changing, self-suspension, self-locking, or self-deletion via dual-layer protection (controller guards and UI action masking).

---

## 2. Database Schema Snapshot

### Tables:
- `users`: Standard authentication table (`id`, `name`, `email`, `status` (active/suspended, indexed), `email_verified_at`, `password`, `remember_token`, `profile_photo_path`, `photo_hash`, `timestamps`).
- `password_reset_tokens`: Password reset handling (`email`, `token`, `created_at`).
- `sessions`: Database session driver table.
- `cache` & `cache_locks`: Database cache storage.
- `jobs`, `job_batches`, `failed_jobs`: Database queue driver tables.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: Spatie RBAC tables. Configured roles: `Super-Admin` (protected super role), `User` (protected baseline default role, auto-assigned to new registrants), `Admin`, `manager`.
- `activity_log`: Spatie audit trail and event logging table.
- `notifications`: Laravel database notifications table (`id (uuid)`, `type`, `notifiable_type`, `notifiable_id`, `data (json)`, `read_at`, `timestamps`).
- `system_settings`: Key-value configuration table with JSON casting and caching (`id`, `key`, `value`, `group`, `description`, `timestamps`).

---

## 3. Registered Models & Enums
- `App\Enums\AccountStatus`: Backed string enum (`Active = 'active'`, `Suspended = 'suspended'`) with UI labels, badge color tokens, and translation helpers.
- `App\Models\User`: Authenticatable user model (implements `MustVerifyEmail`; includes `FilterableTrait`, `HasActivity`, `HasFactory`, `HasRoles`, `Notifiable`; casts `'status' => AccountStatus::class`; attributes `profile_photo_path`, `photo_hash`, `profile_photo_url`). Automatically assigned default role `'User'` on creation via `UserObserver::created()`. Immediate verification email dispatched on registration.
- `App\Models\SystemSetting`: System configuration overrides model with cached access and JSON value casting.
- `App\Services\PermissionDiscoveryService`: Introspection and RBAC service defining super roles (`Super-Admin`) and default baseline role (`User`), with immutability guarantees.

---

## 4. Routes & Endpoints
- **Localized Web Routes** (`routes/web.php` wrapped in `LaravelLocalization::groupRoutes`):
  - Arabic (default, no prefix):
    - `GET /` -> Public welcome page (`welcome.blade.php`; automatically intercepts & redirects to `/system-tables/setup` when `User::count() === 0`).
    - `GET /system-tables/setup` -> First-Run Super Admin Setup Wizard (`SystemTableController@setup`; zero-user gate). **[NEW 2026-09-09]**
    - `POST /system-tables/setup` -> Provision First Super Admin Account (`SystemTableController@storeSetup`). **[NEW 2026-09-09]**
    - `GET /dashboard` -> Authenticated user dashboard (`dashboard.blade.php`).
    - `GET /profile` -> Edit user profile (`ProfileController@edit`).
    - `PATCH /profile` -> Update profile details (`ProfileController@update`).
    - `DELETE /profile` -> Delete account (`ProfileController@destroy`).
    - `GET /system-tables` -> System Tables Overview Dashboard (`SystemTableController@index`).
    - `GET /system-tables/users` -> Users & Active Sessions Explorer (`SystemTableController@users`).
    - `POST /system-tables/users` -> Create New User (`SystemTableController@storeUser`).
    - `PUT /system-tables/users/{user}` -> Update User Details & Role (`SystemTableController@updateUser`).
    - `POST /system-tables/users/{user}/toggle-status` -> Quick Account Status Toggle (`SystemTableController@toggleUserStatus`). **[NEW 2026-09-09]**
    - `DELETE /system-tables/users/{user}` -> Delete User Account (`SystemTableController@destroyUser`). **[NEW 2026-09-09]**
    - `GET /system-tables/roles` -> Roles & Permissions Matrix (`SystemTableController@roles`).
    - `GET /system-tables/activity-log` -> Audit Trail Explorer (`SystemTableController@activityLog`).
    - `GET /system-tables/notifications` -> Notifications Explorer (`SystemTableController@notifications`).
    - `GET /system-tables/queues` -> Queue & Background Jobs Monitor (`SystemTableController@queues`).
    - `GET /system-tables/cache` -> Cache & Atomic Locks Inspector (`SystemTableController@cache`).
    - `GET /system-tables/pruning` -> Data Pruning Settings & Dashboard (`SystemTableController@pruningSettings`).
    - `POST /system-tables/pruning/settings` -> Update Pruning Parameters (`SystemTableController@updatePruningSettings`).
    - `POST /system-tables/pruning/dry-run` -> Pruning Simulation (`SystemTableController@dryRunPruning`).
    - `POST /system-tables/pruning/execute` -> Immediate Pruning Execution (`SystemTableController@executePruning`).
    - `POST /system-tables/pruning/reset` -> Reset Pruning Settings to Defaults (`SystemTableController@resetPruningSettings`).
    - `POST /system-tables/pruning/tables` -> Onboard Database Table to Pruning (`SystemTableController@addPruningTable`).
    - `DELETE /system-tables/pruning/tables/{table}` -> Remove Custom Table from Pruning (`SystemTableController@removePruningTable`).
    - `GET /system-tables/backups` -> Database Backups & Disaster Recovery Dashboard (`SystemTableController@backups`).
    - `POST /system-tables/backups/create` -> Create On-demand Database Backup (`SystemTableController@createBackup`).
    - `GET /system-tables/backups/download/{file}` -> Download Backup Zip Archive (`SystemTableController@downloadBackup`).
    - `DELETE /system-tables/backups/{file}` -> Delete Backup Snapshot (`SystemTableController@deleteBackup`).
    - `POST /system-tables/backups/restore` -> Restore Point-in-time Snapshot (`SystemTableController@restoreBackup`).
    - `POST /system-tables/backups/restore-oldest` -> Restore Oldest Available Snapshot (`SystemTableController@restoreOldestBackup`).
    - `GET /system-tables/settings` -> Dynamic System Settings Dashboard (`SystemTableController@settings`). **[NEW 2026-09-10]**
    - `POST /system-tables/settings/toggle-registration` -> Real-time Registration Switch Toggle (`SystemTableController@toggleRegistration`). **[NEW 2026-09-10]**
    - `POST /system-tables/settings/update` -> Dynamic Setting Parameter Update (`SystemTableController@updateSetting`). **[NEW 2026-09-10]**
    - Auth routes (`login`, `register` [shielded by `EnsureRegistrationIsOpen`], `forgot-password`, `reset-password`, etc.).
  - English (`/en/...`) and French (`/fr/...`):
    - Prefixed mirror routes (`en.dashboard`, `fr.dashboard`, `en.system-tables.index`, `fr.system-tables.index`, etc.).
- **API Notification Routes** (`routes/api.php`) — Auth-protected:
  - `GET /api/notifications` → `NotificationController@index` (paginated all notifications)
  - `GET /api/notifications/unread` → `NotificationController@unread` (unread count + list)
  - `PATCH /api/notifications/read-all` → `NotificationController@markAllAsRead`
  - `PATCH /api/notifications/{id}/read` → `NotificationController@markAsRead`
  - `DELETE /api/notifications/{id}` → `NotificationController@destroy`

---

## 5. Architectural Components
- **Helpers (`app/Helpers`):** `helpers.php` (`system_setting($key, $default)` for cached configuration retrieval, `is_registration_open()` for instant boolean gate inspection).
- **Traits (`app/Traits`):** `ApiResponseTrait` (unified API responses with success/error envelopes), `FilterableTrait` (declarative dynamic request query filtering and search scope).
- **Interfaces (`app/Interfaces`):** `BaseRepositoryInterface` (includes `filter` & `paginateWithFilter`), `UserRepositoryInterface`.
- **Repositories (`app/Repositories`):** `BaseRepository` (abstract base with dynamic filtering), `UserRepository`.
- **Services (`app/Services`):** `BaseService` (transaction manager & exception handling), `UserService` (domain logic), `ImageOptimizationService` (image compression & scaling), `FileUploadService` (standardized secure file uploads & storage management), `SystemTableService` (centralized queries, metrics aggregation, pagination for all 13 database tables), `DataPruningService` (central automated data pruning engine, lifecycle management, dynamic table discovery, eligible candidate filtering), `DatabaseBackupService` (wraps `Spatie\Backup\BackupDestination`, backup inspection, oldest/latest snapshot tagging, on-demand creation, secure download/delete with path traversal sanitization, point-in-time SQL extraction and database state restoration), `PermissionDiscoveryService` (core schema introspection engine, database table discovery, dynamic blacklist filtering, automatic standard CRUD permission generator, and dynamic matrix aggregation).
- **Notifications (`app/Notifications`):** `SystemActivityAlert` (database-channel-only notification with unified payload: `title`, `message`, `type`, `causer`, `extra`).
- **Observers (`app/Observers`):** `UserObserver` (captures `created` & `deleted` events on User model; auto-dispatches `SystemActivityAlert` to all `Super-Admin` and `Admin` role users; gracefully handles missing roles).
- **Controllers:**
  - `App\Http\Controllers\ProfileController`: Refactored with `declare(strict_types=1);`, delegates `update` and `destroy` to `UserService` (transaction-wrapped).
  - `App\Http\Controllers\SystemTableController`: Thin controller orchestrating requests for the System Tables Explorer suite (includes `settings`, `toggleRegistration`, `updateSetting`).
  - `App\Http\Controllers\Api\NotificationController`: API (`index`, `unread`, `markAsRead`, `markAllAsRead`, `destroy`; uses `ApiResponseTrait`; enforces per-user notification isolation).
- **Console Commands (`app/Console/Commands`):** `SetupProjectCommand` (`php artisan project:setup [--fresh] [--force]`), `OptimizeImagesCommand` (`php artisan images:optimize`), `SyncTablePermissionsCommand` (`php artisan permissions:sync-tables [--dry-run]`), `DataPruneCommand` (`php artisan data:prune`).
- **Providers (`app/Providers`):** `RepositoryServiceProvider` (maps repository interfaces to implementations), `AppServiceProvider` (registers Blade `@registrationOpen` directive, super role bypass, and helper autoloading).
- **Middleware (`app/Http/Middleware`):** `SetLocale` (guarantees runtime locale synchronization), `EnsureRegistrationIsOpen` (guards `/register` routes).
- **Middleware Aliases (`bootstrap/app.php`):** `role` (RoleMiddleware), `permission` (PermissionMiddleware), `role_or_permission` (RoleOrPermissionMiddleware), `localize` (LaravelLocalizationRoutes), `localizationRedirect` (LaravelLocalizationRedirectFilter), `localeSessionRedirect` (LocaleSessionRedirect), `localeCookieRedirect` (LocaleCookieRedirect), `localeViewPath` (LaravelLocalizationViewPath), `setLocale` (SetLocale), `registration.open` (EnsureRegistrationIsOpen).
- **Views & Layout Isolation (`resources/views`):**
  - Layouts: `layouts/app-rtl.blade.php`, `layouts/app-ltr.blade.php`, `layouts/guest-rtl.blade.php`, `layouts/guest-ltr.blade.php` (all embedded with Zero-FOUC prevention scripts; legacy unisolated Breeze files `app`, `guest`, `navigation` purged).
  - Navigation: `layouts/navigation-rtl.blade.php`, `layouts/navigation-ltr.blade.php` (with responsive desktop/mobile theme & language switchers, and standalone enlarged brand logo lockup `h-12 w-auto sm:h-14` without redundant text labels).
  - Profile: `profile/edit.blade.php`, `profile/partials/update-profile-information-form.blade.php`, `profile/partials/update-password-form.blade.php`, `profile/partials/delete-user-form.blade.php` (all with complete Dark Mode styling).
  - System Explorer: `system/settings.blade.php` (Dynamic System Settings dashboard with Alpine.js real-time toggles), `system/users.blade.php` (Users & Sessions with registration status indicator and manual account locking).
  - Components: `AppLayout` (dynamic RTL/LTR resolution), `GuestLayout` (dynamic RTL/LTR resolution), `x-system-tabs` (vertical sidebar with System Settings item), `x-language-switcher`, `x-theme-switcher`, and Dark-Mode enabled standardized button & form components (`x-primary-button`, `x-secondary-button`, `x-danger-button`, `x-success-button`, `x-warning-button`, `x-info-button`, `x-nav-link`, `x-input-label`, `x-text-input`, `x-input-error`, `x-modal`, `x-dropdown`, `x-dropdown-link`, `x-responsive-nav-link`, `x-auth-session-status`).
  - **Unified CRUD Modal Components:** `x-crud-modal.delete`, `x-crud-modal.form`.
- **Vite Bundles (`resources/css`, `resources/js`):**
  - RTL: `app-rtl.css`, `app-rtl.js`
  - LTR: `app-ltr.css`, `app-ltr.js`
- **Tailwind Configuration:** `darkMode: 'class'` in `tailwind.config.js`.
- **Assets & Storage Structure:**
  - Static Web Assets: `public/images/` (dual-theme transparent logos `logo.png` [Light] and `logo-dark.png` [Dark], icons, graphics; accessed via `asset('images/...')`).
  - Dynamic Uploads: `storage/app/public/instruments/` (linked to `public/storage/instruments/` via active junction; accessed via `asset('storage/instruments/...')`).
- **Scheduled Tasks (`routes/console.php`):** `backup:clean` (01:00 daily), `backup:run` (01:30 daily).
- **Actions (`app/Actions`):** None yet.
- **Form Requests (`app/Http/Requests`):** `InitialSystemSetupRequest`, `ProfileUpdateRequest`, `Auth\LoginRequest`, `UpdatePruningSettingsRequest`, `AddPruningTableRequest`, `CreateSystemUserRequest`, `UpdateSystemUserRequest`, `CreateRoleRequest`, `CreatePermissionRequest`, `UpdateRoleRequest`, `UpdatePermissionRequest`.
- **API Resources:** None yet.

---

## 6. Installed Key Dependencies
- `laravel/framework` (13.x)
- `laravel/breeze` (2.4+)
- `laravel/boost` (2.7)
- `laravel/pint` (1.27)
- `mcamara/laravel-localization` (2.4+)
- `spatie/laravel-permission` (8.3)
- `spatie/laravel-activitylog` (5.1)
- `spatie/laravel-backup` (10.3+)
- `intervention/image` (4.3+)
- `phpunit/phpunit` (12.5+)

---

## 7. AI Agent Integrations & MCP
- **Laravel Boost MCP Server:**
  - Globally configured: `~/.gemini/config/mcp_config.json` (pointing to `C:\Users\HP\.config\herd\bin\php84\php.exe` and `c:\Project HARD\tools.gmtm-dz.com\artisan boost:mcp`).
  - Workspace plugin: [.agents/plugins/laravel-boost/](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/plugins/laravel-boost/) (`plugin.json`, `mcp_config.json`).
  - Workspace root config: [.agents/mcp_config.json](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/mcp_config.json).
  - Environment: `C:\Users\HP\.config\herd\bin` added to Windows User `PATH`.
  - Exposes tools: `database-query`, `database-schema`, `get-absolute-url`, `browser-logs`, `search-docs`.

---

## 8. Standing Architectural Directives
- **Rule 12 (Strict Separation of Concerns):**
  - Backend isolation: Pure PHP logic in `app/`, `routes/`, `database/`; no HTML, inline styles, or frontend scripts in PHP controllers, actions, services, or models.
  - Frontend isolation: UI markup in `resources/views/` and `resources/js/`; no database queries or Eloquent business logic in views.
  - CSS & Styling isolation: Absolute prohibition of inline `style="..."` attributes in HTML/Blade markup. Utility-first Tailwind classes in templates, or dedicated CSS files in `resources/css/`. Zero raw `<style>` tag injections in PHP or JS components.
- **Rule 13 (Strict Security Quarantine & Mandatory Developer Verification):**
  - High-Security Quarantine (`resources/views/system/**`, `app/Http/Controllers/SystemTableController.php`, `app/Services/SystemTableService.php`, `system-tables.*` routes): Confidential, internal system inspection module. Strictly isolated from standard public or user application flows.
  - Mandatory Developer Pre-Approval: The AI assistant is strictly bound to request developer confirmation before adding or modifying any feature relating to this module, explicitly asking: *"هل هذه الإضافة تنتمي إلى هذا الملف/القسم الأمني السري أم لا؟"*.
- **Rule 14 (Mandatory Unified Button & Semantic Color System):**
  - Strict UI Component Reuse: Never use ad-hoc raw `<button>` elements with arbitrary styles.
  - Standardized Semantic Roles: `<x-primary-button>` (Brand Orange for main actions), `<x-secondary-button>` (Bordered Gray for dismiss/cancel), `<x-danger-button>` (Rose Red for destructive operations), `<x-success-button>` (Emerald Green for approval/resolving), `<x-warning-button>` (Amber Yellow for retries/caution), `<x-info-button>` (Indigo Blue for inspection/diffs/payloads). Dual Light/Dark mode parity required across all button components.
- **Rule 15 (Mandatory Unified Table Component Architecture):**
  - Strict Table Component Reuse: Prohibit writing raw, unstructured HTML `<table>` elements with redundant utility classes.
  - Component Hierarchy (`resources/views/components/table/` and `table.blade.php`):
    - `<x-table>`: Primary table container offering card styling, optional `toolbar` slot, horizontal overflow scroll wrapper, `header` slot, default slot for rows, and `pagination` footer slot.
    - `<x-table.th>`: Standardized uppercase table header cell with typography and responsive padding.
    - `<x-table.tr>`: Standardized table body row with subtle hover state transitions in light and dark modes.
    - `<x-table.td>`: Standardized table body cell with responsive alignment and text tokens.
    - `<x-table.empty>`: Standardized empty state row with SVG icon, customizable `colspan`, and translated message.
    - `<x-table.actions>`: Flex wrapper (`inline-flex items-center gap-1.5 whitespace-nowrap`) for row action buttons.
    - `<x-table.action>`: Universal action component supporting `type` (`view`, `edit`, `delete`, `download`, `restore`, `primary`, `success`), `href` (polymorphic `<a>` or `<button>`), `buttonType` (`button`, `submit`), icons, and tooltips.
    - `<x-table.action-view>`: Semantic Indigo action button with eye icon for viewing/inspecting.
    - `<x-table.action-edit>`: Semantic Amber action button with pencil icon for edits.
    - `<x-table.action-delete>`: Semantic Rose action button with trash icon for deletes/destructive operations. Supports `action-url` and `confirm-message` with `button-type="submit"`.
    - `<x-table.action-download>`: Semantic Indigo action button with download archive icon for file exports/snapshots.
    - `<x-table.action-restore>`: Semantic Amber action button with rotate/restore icon for point-in-time state recovery.
  - Context-Aware Action Placement Matrix:
    - `backups`: Create Backup (`<x-primary-button>`) in overview card; Restore Oldest Snapshot (`<x-warning-button>`); Download Archive (`<x-table.action-download>`), Restore Snapshot (`<x-table.action-restore>`), and Delete Snapshot (`<x-table.action-delete>`) in rows.
    - `users`: Create User (`<x-primary-button>`) in toolbar; Edit & Delete (`<x-table.action-edit>`, `<x-table.action-delete>`) in rows.
    - `sessions`: Terminate Session (`<x-table.action-delete :title="__('Terminate Session')">`) in rows.
    - `roles`: Create Role (`<x-primary-button>`) in header; Edit & Delete (`<x-table.action-edit>`, `<x-table.action-delete>`) on role cards.
    - `permissions`: Create Permission (`<x-primary-button>`) in toolbar; Edit & Delete (`<x-table.action-edit>`, `<x-table.action-delete>`) in rows.
    - `jobs`: Cancel Job (`<x-table.action-delete :title="__('Cancel Job')">`) in rows.
    - `failed_jobs`: View Trace (`<x-table.action-view>`), Retry Job (`<x-table.action type="primary">`), Delete Record (`<x-table.action-delete>`).
    - `job_batches`: Delete Batch (`<x-table.action-delete :title="__('Delete Batch')">`) in rows.
    - `cache`: Forget Key (`<x-table.action-delete :title="__('Forget Key')">`) in rows.
    - `cache_locks`: Release Lock (`<x-table.action-delete :title="__('Release Lock')">`) in rows.
    - `notifications`: View Payload (`<x-table.action-view>`), Delete Notification (`<x-table.action-delete :title="__('Delete Notification')">`).
    - `activity_log`: Strictly read-only (`<x-table.action-view>`) to preserve forensic audit immutability.
- **Rule 16 (Stateful Unified Global Filter Architecture):**
  - Strict UI Reuse: Prohibit writing ad-hoc `<form>` and `<input>` blocks for table filtering and search. Always utilize `<x-global-filter>` and its sub-components (`<x-global-filter.search>`, `<x-global-filter.select>`, `<x-global-filter.sort>`).
  - Zero Data Leakage: `<x-global-filter>` is strictly a presentation component submitting GET parameters to caller endpoints; backend endpoints enforce authorization and `FilterableTrait` column whitelisting.
  - State Continuity: Query parameters remain the single source of truth across pagination (`withQueryString()`), language switches (`getLocalizedURL()`), and form operations.
- **Rule 17 (Mandatory Trilingual Localization Across All Views & Features):**
  - Zero Hardcoded Text: Strict prohibition against raw text, placeholders, or labels in `resources/views/**` and `resources/js/**`. All UI copy must be wrapped in `__('...')` or `@lang('...')`.
  - Simultaneous 3-Language Sync: Any new key must immediately be translated and committed to `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
  - Zero Fallback Policy: All 3 dictionaries maintain exact 1-to-1 key parity (408 keys each, 0 duplicates, 0 missing). Missing keys in any language constitute a failed task.
  - Verification Mandate: Audit scripts must confirm 0 missing keys before closing UI work.
- **Rule 18 (Unified Automated Data Pruning & Lifecycle Management):**
  - Architecture: Centralized in `App\Services\DataPruningService` and `config/pruning.php`.
  - Storage & Overrides: Custom dashboard configurations persist in `system_settings` table via `App\Models\SystemSetting` with Cache caching and fallback to `config/pruning.php`.
  - Dynamic Table Onboarding: Administrators can onboard any eligible database table dynamically via `addCustomTable()` and Alpine.js modal with auto-suggested primary key and timestamp columns. Custom tables can be detached via `removeCustomTable()`.
  - Sovereign Blacklist: Hardcoded immutable safeguard prohibiting pruning of `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `migrations`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, and `jobs`.
  - Chunked Processing: All deletions execute in batches (default 1,000) to protect database memory and avoid prolonged table locking.
  - Dual Strategy: Combines date-based retention (`retention_days`) and maximum capacity count pruning (`max_records`).
  - Audit Trail: Pruning executions, settings updates, and custom table onboarding/removal automatically log to Spatie activitylog (`log_name = 'data_pruning'`).
  - Web Management Dashboard: Dedicated administrative view at `system-tables.pruning` (`resources/views/system/pruning.blade.php`) with global engine toggle, per-table policy cards (built-in and custom), live dry-run simulation modal, manual execution with safety confirmation, defaults reset, and "Add Table to Pruning" modal.
  - CLI & Automation: Triggerable via `php artisan data:prune` (`--dry-run`, `--table`, `--chunk`) and scheduled daily at 02:00 in `routes/console.php`.
- **Rule 19 (Unified Database Backups & Point-in-Time Disaster Recovery):**
  - Architecture: Centralized in `App\Services\DatabaseBackupService` wrapping `Spatie\Backup\BackupDestination`.
  - Storage & Discovery: Discovers backups from the `local` disk (default `storage/app/private/Laravel`), sorting chronologically descending, tagging `is_oldest` and `is_newest` snapshots.
  - On-Demand Creation: Administrators can generate instant database dumps (`backup:run --only-db`) with activity audit tracking.
  - Security & Path Traversal: Prohibits directory traversal in backup file operations via strict sanitization (`/`, `\`, `..` rejection) and `.zip` extension enforcement.
  - Point-in-Time Restoration: Restores database state directly from compressed archives using `ZipArchive`, temporary directory extraction, database driver isolation (disabling foreign keys on MySQL), and executing SQL dumps via `DB::unprepared`.
  - Oldest Snapshot Recovery: Fast CTA and dedicated action to roll back to the earliest recorded snapshot point.
  - UI Safeguards: High-visibility Alpine.js red confirmation modal warning of irreversible database overwrites.
  - Full Test Coverage: Complete feature test suite covering authentication, directory traversal defense, download, deletion, restoration, and edge cases with dedicated test namespace isolation (`TestingBackup`) safeguarding real backup files. (Overall test suite: 197/197 tests passing, 795 assertions).
- **Rule 20 (Mandatory Unified CRUD Suite Architecture):**
  - Component Standard (`resources/views/components/crud-modal/`):
    - `<x-crud-modal.delete>`: Unified confirmation modal taking Alpine variable names as standard props (`show`, `action-url`, `item-name`). Eliminates hand-coded backdrop, layout, and modal boilerplate.
    - `<x-crud-modal.form>`: Unified create/edit form modal with dynamic method (`POST`/`PUT`), dynamic action resolution (`alpine-action` or `action-url`), named `$hidden` slot, and configurable semantic icon color themes.
  - Enhanced Inline Table Deletions (`<x-table.action-delete>`):
    - Accepts optional `action-url`, `confirm-message`, and `method` props.
    - When `action-url` is specified, automatically encapsulates a self-contained POST form with `@method('DELETE')`, `@csrf`, and confirmation prompt.
    - When `action-url` is omitted, continues functioning as an Alpine modal trigger button.
- **Rule 21 (Automated Table Discovery & Dynamic Permission Matrix Architecture):**
  - Architecture: Centralized in `App\Services\PermissionDiscoveryService` and CLI command `App\Console\Commands\SyncTablePermissionsCommand`.
  - Pure Live Schema Introspection: Exclusively queries the live database schema at runtime with zero hardcoded business entities; dynamically scopes to the active database connection and strictly excludes internal/system blacklisted tables (`migrations`, `sessions`, `cache`, `jobs`, `activity_log`, Spatie RBAC tables).
  - Stale Permissions Pruning: Automatically detects and purges obsolete CRUD permissions for entities/tables that no longer physically exist in the database schema, preventing phantom permission pollution.
  - Standard CRUD Generator & Real-Time Auto-Sync: Dynamically generates 4 standard permissions per discovered business table (`view [table]`, `create [table]`, `edit [table]`, `delete [table]`) and synchronizes newly generated permissions automatically to the `Super-Admin` role. Executes automatically in real-time when administrators access the Roles view (`/system-tables/roles`) or manually on-demand via `php artisan permissions:sync-tables`.
  - Interactive Matrix UI: Roles view (`resources/views/system/roles.blade.php`) presents permissions as a structured interactive grid (Rows = Entities, Columns = View/Create/Edit/Delete, 1-click Row Toggle, and Global Select/Deselect All) bound via Alpine.js.
  - Collapsible On-Demand Permissions Catalog: The legacy/raw permissions catalog table is collapsed and hidden by default behind an automated engine status banner; it expands smoothly on demand via an Alpine toggle (`showPermissionsCatalog`) for manual inspection, creation, or deletion.
  - Anti-Lockout Defense: Hardcoded protection in `SystemTableController` and UI suppressing deletion or modification of the `Super-Admin` role, rendering a locked system badge (`Protected System Role`) in place of action buttons.
- **Rule 22 (User Account Status Lifecycle & Profile Photo Architecture):**
  - Schema: `status` (`varchar(32)`, default `'active'`, indexed), `profile_photo_path` (`varchar(2048)`, nullable), and `photo_hash` (`varchar(64)`, nullable) in `users` table.
  - Enum: `App\Enums\AccountStatus` (`Active = 'active'`, `Suspended = 'suspended'`) providing localized labels, colors, and badge classes.
  - Security Boundary: Suspended accounts are rejected at login by `App\Http\Requests\Auth\LoginRequest`. Self-suspension is strictly prohibited in `SystemTableController`.
  - Background Integrity: `App\Observers\UserObserver` hooks `saving()` to automatically calculate SHA-256 `photo_hash` whenever `profile_photo_path` is present or modified, and resets it to `null` if cleared.
  - Interactive UI: Standardized `<x-secondary-button>` file picker in `resources/views/system/users.blade.php` with live avatar preview, file name display, quick remove button, status filter toolbar, and table row quick toggle.
  - Multi-part Modal: `resources/views/components/crud-modal/form.blade.php` supports `enctype="multipart/form-data"`. Photo files stored on `public` disk (`photos/`) and stale photos automatically purged.
- **Rule 23 (Fluid Full-Width Container Architecture for High-Density Views):**
  - Standard Container Token: All application pages, navigation bars, and views must use dynamic full width: `w-full px-4 sm:px-6 lg:px-8`.
  - Fixed-Width Prohibition: Fixed-width maximum boundaries (such as `max-w-7xl` / `1280px`) are strictly prohibited on layout wrappers and data tables. Modals remain reasonably constrained (`max-w-lg`, `max-w-2xl`, `max-w-3xl`) for focused readability.
  - Responsiveness: Preserves fluid adaptability across all viewports while granting data tables maximum horizontal breathing room (~1576px on 1080p desktop) to avoid column congestion.
- **Rule 24 (Super Roles Authorization Bypass & Anti-Lockout Governance Architecture):**
  - Configurable Property: `protected array $superRoles = ['Super-Admin'];` established in `App\Providers\AppServiceProvider` and `App\Services\PermissionDiscoveryService`.
  - Authorization Interception: `Gate::before` in `AppServiceProvider::boot()` automatically approves any authorization check (`$user->can()`, `@can`, `authorize()`) for any authenticated user possessing a role configured in `$superRoles`, while returning `null` to evaluate specific assigned permissions and policies for other users.
  - Automatic Discovery Synchronization: `PermissionDiscoveryService::syncSuperAdminPermissions()` ensures all roles listed in `$superRoles` exist in the database and inherit 100% of discovered active CRUD permissions upon schema discovery.
  - System Security Quarantine & Anti-Lockout: `SystemTableController` enforces `isSuperRole()` checks to block modification or deletion of any super role via HTTP redirects and localized error notifications.
  - Dynamic UI Protection: `resources/views/system/roles.blade.php` dynamically evaluates `$superRoles`, replacing edit and delete action buttons with the standardized locked system badge (`Protected System Role`).
  - Model Convenience: `App\Models\User` exposes `isSuperAdmin(): bool` convenience method.
- **Rule 25 (High-Security System Tables Quarantine & Super-Admin Exclusivity Lock):**
  - Route Group Middleware Enforcement: All routes under `/system-tables` are strictly quarantined behind `['auth', 'verified', 'role:Super-Admin']`.
  - Cloaked Navigation Links: In both `navigation-ltr.blade.php` and `navigation-rtl.blade.php`, the "System Tables" navigation link (desktop and mobile) is completely hidden from non-super admins via `@if(Auth::user()?->isSuperAdmin() || Auth::user()?->hasRole('Super-Admin'))`.
  - Security Boundary Behavior: Unauthenticated requests redirect to login; authenticated requests lacking the `Super-Admin` role immediately receive HTTP `403 Forbidden`.
  - Factory Convenience: `UserFactory` provides `superAdmin()` state automatically attaching the `Super-Admin` role.
- **Rule 26 (Pruning Audit Trail Dynamic Operation Localization):**
  - Separation of Concerns in Audit History: `SystemTableController::pruningSettings()` transforms the pruning history collection via `map()` to assign a localized `translated_description` attribute through `SystemTableService::translateActivityDescription()`, keeping Blade views free of service calls.
  - Localized View Binding: `resources/views/system/pruning.blade.php` renders `{{ $item->translated_description ?? $item->description }}` in the Operation column.
  - Regex & Interpolation Handling: Dynamic log strings (e.g. `Auto-pruned :total records from table ':table' (Date: :date, Capacity: :capacity)`, `Added custom table ':table' to automated data pruning`) and static messages (`Updated automated data pruning settings`) are seamlessly parsed and translated across all three languages (`ar`, `en`, `fr`).
- **Rule 27 (User Dropdown Navigation Border Radius & Harmonized Action Toolbar):**
  - Trigger Button Component Standard: The User Settings button in `navigation-ltr.blade.php` and `navigation-rtl.blade.php` must follow the uniform toolbar button token: `border border-gray-300 dark:border-gray-600 shadow-sm rounded-lg px-3 py-2 gap-2 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500`.
  - Dynamic Avatar Rendering: Includes user profile picture with fallback to initial badge (`h-5 w-5 rounded-full`) inside the trigger button.
  - Dropdown Menu Margins & Radius: `<x-dropdown>` provides `rounded-lg shadow-lg p-1` container, and `<x-dropdown-link>` applies `rounded-md px-3 py-2` ensuring hover highlights have clean rounded corners and breathing room from the menu boundaries.
- **Rule 28 (Google-Style Account Popover Card Architecture):**
  - Circular Avatar Trigger: Header trigger displays an interactive circular avatar (`p-0.5 rounded-full ring-2 hover:ring-orange-500`) with live user photo or initial letter.
  - Google Popover Structure: Rendered via `<x-dropdown width="80">` with centered email, large avatar (`w-20 h-20 ring-4 shadow-md`) with camera badge linking to profile photo edit, personalized greeting (`Hi, :name!`), role badge (`Super-Admin` shield or assigned role), active status badge, and the iconic rounded-full pill button (`Manage your Account`).
  - Streamlined Focus & Exit: Redundant navigation links (Dashboard and System Tables) are omitted from the popover to avoid duplication with the navbar, transitioning smoothly from the "Manage your Account" pill button directly to the Google-style "Log Out" bottom footer card.
  - Trilingual Dictionary Synchronization: All texts localized across `ar`, `en`, and `fr` dictionaries with 100% key parity.
- **Rule 29 (Dropdown Viewport Bounds & Logical RTL Positioning):**
  - Inward Projection Mandate: Dropdown menus situated on the end toolbar in navigation bars must always use logical `end-0` (`ltr:origin-top-right rtl:origin-top-left end-0`) so they project inward toward the center of the page, eliminating frame overflow in both LTR (expanding leftward) and RTL (expanding rightward).
  - Safety Viewport Constraint: Dropdown containers in `<x-dropdown>` must include `max-w-[calc(100vw-2rem)]` to prevent horizontal viewport clipping or overflow on narrow displays.
- **Rule 30 (Standardized Semantic Color Palette & Unified UI Token Architecture):**
  - Six Curated Semantic Color Categories:
    1. Primary / Brand: Safety Orange (`orange`) for primary calls to action, focus highlights, and active toggles.
    2. Success / Positive: Emerald Green (`emerald`) for active statuses, successful operations, and confirmations.
    3. Danger / Destructive: Rose Red (`rose`) for suspended accounts, failed jobs, deletions, and critical alerts.
    4. Warning / Caution: Amber Yellow (`amber`) for protected system roles, retries, and cautionary hold states.
    5. Info / Forensic: Indigo Blue (`indigo`) for technical counters, audit logs, caches, locks, payloads, and discovered permissions (eliminating random blue drift).
    6. Neutral / Structure: Cool Gray (`gray`) for cards, borders, secondary actions, and subtitles.
  - Standardized Components: `<x-badge>` (polymorphic badge with dot/ping options) and `<x-alert>` (dismissible flash message banner with semantic icons).
  - Standardized Border Radiuses: `rounded-xl` for cards and tables, `rounded-2xl` for modals and popovers, `rounded-lg` for interactive controls and inputs, `rounded-full` for badges and pills.
- **Rule 31 (Standardized Client Handoff & Project Setup Architecture):**
  - Command: `php artisan project:setup` (`app/Console/Commands/SetupProjectCommand.php`).
  - Automated Migration Pipeline: Runs `migrate` (or `migrate:fresh` when `--fresh` is specified) with production overrides (`--force`).
  - Automated Seeding & RBAC Discovery: Runs `db:seed` to trigger `DatabaseSeeder`, `RolesAndPermissionsSeeder`, and `PermissionDiscoveryService` to introspect database schema, generate CRUD permissions, and define baseline roles (`Super-Admin`, `Admin`, `User`).
  - Interactive Secure Super-Admin Creation: Masked terminal prompts (`secret()`) for password with length validation (>= 8 chars), email format validation, and confirmation match.
  - Domain Integration: Explicitly assigns `'Super-Admin'` Spatie RBAC role, sets `AccountStatus::Active`, marks email as verified (`email_verified_at = now()`), and syncs all permissions.
  - Zero Hardcoding Guarantee: Eliminates hardcoded administrative credentials in code or `.env` files during deployment and client handoffs.
- **Rule 32 (Zero-State First-Run Super Admin Web Setup Architecture):**
  - High-Security Quarantine Integration: Placed under the System Security Module per explicit developer pre-approval (`SystemTableController@setup`, `SystemTableController@storeSetup`, `SystemTableService::createInitialSuperAdmin`, `resources/views/system/setup.blade.php`).
  - Automatic Zero-State Gate: Automatically intercepts root `/` traffic when the database contains 0 users or is unmigrated (`!Schema::hasTable('users') || User::count() === 0`) and safely redirects to `/system-tables/setup`.
  - Zero-CLI Self-Installation Pipeline: If the database is completely empty/unmigrated, `SystemTableService::createInitialSuperAdmin` automatically executes `migrate` and `db:seed` before provisioning the first Super Admin account.
  - Strict Anti-Hijacking Lockout: If any user exists in the database, `GET /system-tables/setup` immediately aborts with `404 Not Found` and `POST /system-tables/setup` is rejected via `InitialSystemSetupRequest` (`403 Forbidden`).
  - Tabular Visual Layout: Renders a compact visual specifications table detailing assigned role (`Super-Admin`), initial status (`Active`), permission scope (`Full System Authority`), and database schema state (`Auto-Migration Required` or `Schema Ready`).
  - Atomic Initialization Pipeline: Creates the user with `AccountStatus::Active`, sets `email_verified_at = now()`, synchronizes the protected `'Super-Admin'` role and CRUD permissions, logs an audit trail event, and immediately authenticates the session (`Auth::login($user)`).
  - Trilingual Dictionary Parity: All 17 UI keys synchronized with 100% parity across `ar`, `en`, and `fr` (463 keys each).









