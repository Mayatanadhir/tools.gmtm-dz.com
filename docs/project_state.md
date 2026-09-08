# Project State: tools.gmtm-dz.com

**Last Updated:** 2026-09-08
**Status:** Localization, Theme Management & Profile Module Clean Architecture Active

---

## 1. Application Overview
- **Framework:** Laravel 13.x (PHP 8.4+)
- **Application Brand:** ENGI-MATE ("Your Engineering Work Assistant")
- **Frontend Stack:** Blade + Tailwind CSS (Class-based Dark Mode) + Alpine.js + Vite (Isolated RTL/LTR bundles)
- **Locales Supported:** `ar` (Arabic, default, hidden prefix), `en` (English, `/en/`), `fr` (French, `/fr/`)
- **Themes Supported:** `light`, `dark`, `system` (Zero-FOUC prevention script, Alpine.js reactive store, cross-instance sync)
- **Database Engine:** MySQL (`gmtmdz_tools`)
- **Authentication:** Laravel Breeze (Session/Blade based)
- **Test Suite:** 154 tests, 574 assertions (100% passing)

---

## 2. Database Schema Snapshot

### Tables:
- `users`: Standard authentication table (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `timestamps`).
- `password_reset_tokens`: Password reset handling (`email`, `token`, `created_at`).
- `sessions`: Database session driver table.
- `cache` & `cache_locks`: Database cache storage.
- `jobs`, `job_batches`, `failed_jobs`: Database queue driver tables.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: Spatie RBAC tables.
- `activity_log`: Spatie audit trail and event logging table.
- `notifications`: Laravel database notifications table (`id (uuid)`, `type`, `notifiable_type`, `notifiable_id`, `data (json)`, `read_at`, `timestamps`).
- `system_settings`: Key-value configuration table with JSON casting and caching (`id`, `key`, `value`, `group`, `description`, `timestamps`).

---

## 3. Registered Models
- `App\Models\User`: Authenticatable user model (includes `FilterableTrait`, `HasActivity`, `HasFactory`, `HasRoles`, `Notifiable`).
- `App\Models\SystemSetting`: System configuration overrides model with cached access and JSON value casting.

---

## 4. Routes & Endpoints
- **Localized Web Routes** (`routes/web.php` wrapped in `LaravelLocalization::groupRoutes`):
  - Arabic (default, no prefix):
    - `GET /` -> Public welcome page (`welcome.blade.php`).
    - `GET /dashboard` -> Authenticated user dashboard (`dashboard.blade.php`).
    - `GET /profile` -> Edit user profile (`ProfileController@edit`).
    - `PATCH /profile` -> Update profile details (`ProfileController@update`).
    - `DELETE /profile` -> Delete account (`ProfileController@destroy`).
    - `GET /system-tables` -> System Tables Overview Dashboard (`SystemTableController@index`).
    - `GET /system-tables/users` -> Users & Active Sessions Explorer (`SystemTableController@users`).
    - `POST /system-tables/users` -> Create New User (`SystemTableController@storeUser`).
    - `PUT /system-tables/users/{user}` -> Update User Details & Role (`SystemTableController@updateUser`).
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
    - Auth routes (`login`, `register`, `forgot-password`, `reset-password`, etc.).
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
- **Traits (`app/Traits`):** `ApiResponseTrait` (unified API responses with success/error envelopes), `FilterableTrait` (declarative dynamic request query filtering and search scope).
- **Interfaces (`app/Interfaces`):** `BaseRepositoryInterface` (includes `filter` & `paginateWithFilter`), `UserRepositoryInterface`.
- **Repositories (`app/Repositories`):** `BaseRepository` (abstract base with dynamic filtering), `UserRepository`.
- **Services (`app/Services`):** `BaseService` (transaction manager & exception handling), `UserService` (domain logic), `ImageOptimizationService` (image compression & scaling), `FileUploadService` (standardized secure file uploads & storage management), `SystemTableService` (centralized queries, metrics aggregation, pagination for all 13 database tables), `DataPruningService` (central automated data pruning engine, lifecycle management, dynamic table discovery, eligible candidate filtering), `DatabaseBackupService` (wraps `Spatie\Backup\BackupDestination`, backup inspection, oldest/latest snapshot tagging, on-demand creation, secure download/delete with path traversal sanitization, point-in-time SQL extraction and database state restoration).
- **Notifications (`app/Notifications`):** `SystemActivityAlert` (database-channel-only notification with unified payload: `title`, `message`, `type`, `causer`, `extra`).
- **Observers (`app/Observers`):** `UserObserver` (captures `created` & `deleted` events on User model; auto-dispatches `SystemActivityAlert` to all `Super-Admin` and `Admin` role users; gracefully handles missing roles).
- **Controllers:**
  - `App\Http\Controllers\ProfileController`: Refactored with `declare(strict_types=1);`, delegates `update` and `destroy` to `UserService` (transaction-wrapped).
  - `App\Http\Controllers\SystemTableController`: Thin controller orchestrating requests for the System Tables Explorer suite.
  - `App\Http\Controllers\Api\NotificationController`: API (`index`, `unread`, `markAsRead`, `markAllAsRead`, `destroy`; uses `ApiResponseTrait`; enforces per-user notification isolation).
- **Console Commands (`app/Console/Commands`):** `OptimizeImagesCommand` (`php artisan images:optimize`).
- **Providers (`app/Providers`):** `RepositoryServiceProvider` (maps repository interfaces to implementations).
- **Middleware (`app/Http/Middleware`):** `SetLocale` (guarantees runtime locale synchronization).
- **Middleware Aliases (`bootstrap/app.php`):** `role` (RoleMiddleware), `permission` (PermissionMiddleware), `role_or_permission` (RoleOrPermissionMiddleware), `localize` (LaravelLocalizationRoutes), `localizationRedirect` (LaravelLocalizationRedirectFilter), `localeSessionRedirect` (LocaleSessionRedirect), `localeCookieRedirect` (LocaleCookieRedirect), `localeViewPath` (LaravelLocalizationViewPath).
- **Views & Layout Isolation (`resources/views`):**
  - Layouts: `layouts/app-rtl.blade.php`, `layouts/app-ltr.blade.php`, `layouts/guest-rtl.blade.php`, `layouts/guest-ltr.blade.php` (all embedded with Zero-FOUC prevention scripts; legacy unisolated Breeze files `app`, `guest`, `navigation` purged).
  - Navigation: `layouts/navigation-rtl.blade.php`, `layouts/navigation-ltr.blade.php` (with responsive desktop/mobile theme & language switchers, and standalone enlarged brand logo lockup `h-12 w-auto sm:h-14` without redundant text labels).
  - Profile: `profile/edit.blade.php`, `profile/partials/update-profile-information-form.blade.php`, `profile/partials/update-password-form.blade.php`, `profile/partials/delete-user-form.blade.php` (all with complete Dark Mode styling).
  - Components: `AppLayout` (dynamic RTL/LTR resolution), `GuestLayout` (dynamic RTL/LTR resolution), `x-system-tabs` (vertical sidebar), `x-language-switcher`, `x-theme-switcher`, and Dark-Mode enabled standardized button & form components (`x-primary-button`, `x-secondary-button`, `x-danger-button`, `x-success-button`, `x-warning-button`, `x-info-button`, `x-nav-link`, `x-input-label`, `x-text-input`, `x-input-error`, `x-modal`, `x-dropdown`, `x-dropdown-link`, `x-responsive-nav-link`, `x-auth-session-status`).
- **Vite Bundles (`resources/css`, `resources/js`):**
  - RTL: `app-rtl.css`, `app-rtl.js`
  - LTR: `app-ltr.css`, `app-ltr.js`
- **Tailwind Configuration:** `darkMode: 'class'` in `tailwind.config.js`.
- **Assets & Storage Structure:**
  - Static Web Assets: `public/images/` (dual-theme transparent logos `logo.png` [Light] and `logo-dark.png` [Dark], icons, graphics; accessed via `asset('images/...')`).
  - Dynamic Uploads: `storage/app/public/instruments/` (linked to `public/storage/instruments/` via active junction; accessed via `asset('storage/instruments/...')`).
- **Scheduled Tasks (`routes/console.php`):** `backup:clean` (01:00 daily), `backup:run` (01:30 daily).
- **Actions (`app/Actions`):** None yet.
- **Form Requests (`app/Http/Requests`):** `ProfileUpdateRequest`, `Auth\LoginRequest`, `UpdatePruningSettingsRequest`, `AddPruningTableRequest`, `CreateSystemUserRequest`, `UpdateSystemUserRequest`, `CreateRoleRequest`, `CreatePermissionRequest`, `UpdateRoleRequest`, `UpdatePermissionRequest`.
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
    - `<x-table.action>`: Universal action component supporting `type` (`view`, `edit`, `delete`, `primary`, `success`), `href` (polymorphic `<a>` or `<button>`), icons, and tooltips.
    - `<x-table.action-view>`: Semantic Indigo action button with eye icon for viewing/inspecting.
    - `<x-table.action-edit>`: Semantic Amber action button with pencil icon for edits.
    - `<x-table.action-delete>`: Semantic Rose action button with trash icon for deletes/destructive operations.
  - Context-Aware Action Placement Matrix:
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
  - Zero Fallback Policy: All 3 dictionaries maintain exact 1-to-1 key parity (354 keys each). Missing keys in any language constitute a failed task.
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
  - Full Test Coverage: Complete feature test suite covering authentication, directory traversal defense, download, deletion, restoration, and edge cases. (Overall test suite: 179/179 tests passing, 694 assertions).

