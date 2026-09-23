# Project State: ENGI-GMTM Core Kernel (Point Zero)

**Last Updated:** 2026-09-23
**Status:** Point Zero Master Core Kernel Baseline Active (v1.0.51)

> [!NOTE]
> Historical state log prior to Point Zero Core Kernel extraction is preserved in `docs/archive/legacy_project_state.md`.

---

## 1. Application Overview
- **Framework:** Laravel 13.x (PHP 8.4+)
- **Application Brand:** ERP-GMTM (GMTM — "Générale Maintenance Et Travaux Montage")
- **Corporate Logo System:** Dual-theme official GMTM vector assets (`Logo-black.png` for light theme, `Logo-white.png` for dark theme) served via `<x-application-logo>` across the navigation bar, guest auth screens, and executive workspace banner with aspect-ratio conscious responsive sizing.
- **Frontend Stack:** Blade + Tailwind CSS (Class-based Dark Mode) + Alpine.js + Vite (Isolated RTL/LTR bundles)
- **Locales Supported:** `ar` (Arabic, default, hidden prefix), `en` (English, `/en/`), `fr` (French, `/fr/`)
- **Themes Supported:** `light`, `dark`, `system` (Zero-FOUC prevention script, Alpine.js reactive store, cross-instance sync)
- **Database Engine:** MySQL (`gmtmdz_erp` active, supports zero-touch auto-migration from fresh state)
- **Authentication:** Laravel Breeze (Session/Blade based with active `MustVerifyEmail` contract; registration immediately dispatches verification emails via `${APP_NAME} <${MAIL_FROM_ADDRESS}>`; registration routes guarded by dynamic cache-backed `EnsureRegistrationIsOpen` middleware)
- **Automatic Migration & Auto-Creation Engine:** `EnsureDatabaseIsMigrated` middleware inspects pending schema and database existence. Automatically provisions missing MySQL/SQLite/PostgreSQL databases via raw PDO, runs `migrate --force`, `db:seed`, and permission discovery without user prompt.
- **Database Error Fallback View:** `resources/views/errors/database.blade.php` rendered with HTTP 503 if database connection or auto-creation fails, preventing 500 crashes and offering connection diagnostics and troubleshooting steps.
- **Zero-State Super Admin Setup Gate:** `EnsureSuperAdminExists` middleware intercepts all web traffic on zero-user databases, redirecting to `/system-tables/setup` to provision Super Admin, then permanently locks the route (404/403).
- **Test Suite:** 306 tests, 1404 assertions (100% passing)
- **Legacy Code Modernization & Migration Protocol (`ADR-042`):** Standardized enterprise directive binding the AI assistant to act as a *Strict Enterprise Architect*. Forbids copying spaghetti architecture, enforces Laravel Eloquent naming standards (Models singular PascalCase, Tables plural snake_case, Pivot tables singular alphabetical snake_case, Foreign keys singular_id), mandates a pre-execution Naming Convention Fixes Table, enforces clean architecture (skinny controllers, domain services, form requests, enums), GMTM Blade components only, and sequential 9-step execution order.
- **Localization Parity & 100% English Master Keys Standard (`ADR-037`, `ADR-040`):** Trilingual dictionary parity across Arabic, English, and French (1,237 keys each, 0 missing, 0 non-English keys). Absolute rule enforced via automated test (`LocalizationTest::test_all_translation_keys_are_strictly_in_english`) and documented across all rule files.
- **Forensic Audit Trail Causer Identity & Search Integration (`ADR-039`, `ADR-040`):** Full actor identity display (user profile photo / initial avatar, name in bold, email underneath in mono styling) in `resources/views/system/activity-log.blade.php`; polymorphic search by causer name and email in `SystemTableService::getActivityLogs()` with full query string persistence; 100% trilingual event badge labels, table headers, and activity description translation regex engines.
- **Mandatory Comprehensive Ecosystem & Dependency Synchronization Protocol (`ADR-041`):** Enshrined cross-cutting rule obligating the AI assistant upon creating or modifying any new service or view to systematically verify and synchronize all connected ecosystem files (trilingual dictionaries, `<x-alert>` feedback, notifications, audit trail logs, `<x-global-filter>`, design system tokens, RBAC permissions, and tests).
- **Unified Alerts, Notifications & Functional Classification Engine (`ADR-038`):** Centralized `<x-alert>` as the single source of truth for in-page operational alerts with semantic variants (`success`, `danger`, `warning`, `info`, `primary`); standardized session flash keys (`with('success')`, `with('error')`, `with('warning')`, `with('info')`); structured `SystemActivityAlert` notifications scoped by functional tiers (`Management` for security/admin notices, `Engineering` for metrology/calibration alerts, `Technicians` for field operations); actor role chips mapped to `<x-badge>` functional variants with 100% trilingual dictionary parity.
- **Unified Search, Filter & State Persistence Engine (`ADR-036`):** Standardized `<x-global-filter>` as the single source of truth for table filtering across the application; position selectors categorized by 3 functional tiers via `<optgroup>` (`Management & Executive Leadership`, `Engineering & Specialist Roles`, `Field Operations & Technicians`); search and filter query string state permanently preserved across language switching, table pagination (`->withQueryString()`), create redirects, edit redirects, and delete redirects (`$request->query()`).
- **Modular Rich Tool & Page Icons Architecture (`<x-tool-icon>`):** 22 standalone componentized micro-illustrations in `resources/views/components/icons/tools/` expressing business domains and operations, adhering to strict design token guidelines, unique gradient ID prefixing (`eq-`, `fc-`, `ms-`, `un-`, `rp-`, `ex-`, `em-`, `mod-md-`, `mod-met-`, `mod-ops-`, `mod-sys-`, `ct-`, `ins-`, `art-`, `at-`, `cm-`, `cl-`, etc.), and zero inline styles. Harmonized `equipment`, `forecasts`, `missions`, `units`, `reports`, `expenses`, `employees`, `module-master-data`, `module-metrology`, `module-operations`, `module-system`, `article-types`, `attachments`, `calibrator-movements` (Fluke 700G gauge with logistics motion arrow), `clients`, and `contracts`.
- **Roles & Permissions Architecture:** Configured Roles and Role Modals (Create & Edit) fully bound to static registry (`config/permissions.php`), rendering 24 configured business entities organized into 5 visually isolated domain categories (`System Security & Administration`, `Metrology & Equipments`, `Operations & Projects`, `Internal and Analytical Management`, `Master Data / Reference Data`) with semantic color accents, category headers containing root module-level view permissions (`view metrology`, `view operations`, `view analytics`, `view master data`), one-click category toggles, and granular CRUD privileges (85 active permissions synchronized with `Super-Admin`, including `view employee compensation`).
- **Business Modules Architecture:** Standardized 4 modular business groups (Metrology & Equipments [5 views], Operations & Projects [5 views], Internal & Analytical Management [4 views], Master Data / Reference Data [3 views]) with dedicated thin controllers (`MetrologyController`, `OperationsController`, `AnalyticsController`, `MasterDataController`), 4 dedicated executive dashboards with cards fully gated by granular Spatie view permissions (`metrology.index`, `operations.index`, `analytics.index`, `master-data.index`) sharing an identical 4-column responsive metrics grid layout (`grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4`), sidebar tab navigation suites (`<x-metrology-tabs>`, `<x-operations-tabs>`, `<x-analytics-tabs>`, `<x-master-data-tabs>`) integrated with rich `<x-tool-icon>` micro-illustrations, zero-permission fallback empty states (`No Accessible Explorers`), and responsive Blade views matching the System Control design layout.
- **Executive Workspace Portal:** `workspace.blade.php` transformed into a central executive dashboard hub displaying role-gated business module cards with rich domain illustrations (`module-metrology`, `module-operations`, `module-analytics`, `module-master-data`), rapid exploration triggers, and direct administrative system infrastructure access.
- **Employees Module & Legacy Migration (`ADR-031`, `ADR-034`):** Production-ready Employees management under Master Data (`/master-data/employees`) featuring complete CRUD, Clean Architecture (Repository/Service pattern), CAS WebP image optimization and avatar synchronization with linked user accounts, financial data quarantine masking compensation unless `view employee compensation` is held, full legacy data migration importing all 7 historical company staff records while strictly preserving original primary keys (1, 2, 3, 4, 5, 25, 26), and standardized `<x-badge>` token integration for positions (`badgeVariant()`) and statuses.
- **Notifications Architecture:** Database notifications explorer updated with localized alert types, semantic action badges, payload title translation (`{{ __($notificationTitle) }}`), and preview modals with 100% key parity across AR, EN, and FR.
- **System Settings Engine:** Enterprise key-value runtime configuration engine with zero-latency persistent caching (`86400` TTL), atomic cache updates, and Super-Admin interactive dashboard.
- **Anti-Self-Action Security Policy:** Administrators are strictly prohibited from self-demotion/role changing, self-suspension, self-locking, or self-deletion via dual-layer protection (controller guards and UI action masking).
- **Media Optimization, CAS Deduplication & Zero Disk Space Leak Engine (`ADR-009`):** Centralized processing gateway (`App\Services\MediaOptimizationService`, `config/media.php`) enforcing compulsory WebP conversion (80% quality, max 1920px width), Ghostscript PDF compression (/ebook 150dpi profile), SHA-256 Content-Addressable Storage (CAS) deduplication, reference-aware safe deletion (preserving shared assets), and the "Process & Destroy" protocol permanently purging temporary uploads and intermediate scratch artifacts from disk.
- **Profile Photo & Media Storage Pipeline:** Public uploads processed via `MediaOptimizationService` directly into optimized `.webp` artifacts; storage linked via NTFS junction (`public/storage` -> `storage/app/public`); domain aligned with Herd virtual host (`http://erp.gmtm-dz.com.test`); instantaneous client-side preview powered by Alpine.js and `URL.createObjectURL(file)` with unified fallback avatars.

---

## 2. Database Schema Snapshot

### Tables:
- `users`: Standard authentication table (`id`, `name`, `email`, `status` (active/suspended, indexed), `email_verified_at`, `password`, `remember_token`, `profile_photo_path`, `photo_hash`, `timestamps`).
- `employees`: Reference staff table (`id`, `user_id` (nullable FK to users), `full_name`, `registration_number` (unique), `position`, `status` (active/inactive/on_leave, indexed), `join_date`, `salary` (decimal 12,2), `daily_rate` (decimal 10,2), `address`, `profile_photo_path`, `photo_hash`, `timestamps`, `deleted_at` (soft deletes)).
- `password_reset_tokens`: Password reset handling (`email`, `token`, `created_at`).
- `sessions`: Database session driver table.
- `cache` & `cache_locks`: Database cache storage.
- `jobs`, `job_batches`, `failed_jobs`: Database queue driver tables.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: Spatie RBAC tables. Configured roles: `Super-Admin` (protected super role), `User` (protected baseline default role, auto-assigned to new registrants), `Admin`, `manager`.
- `activity_log`: Spatie audit trail and event logging table.
- `notifications`: Laravel database notifications table (`id (uuid)`, `type`, `notifiable_type`, `notifiable_id`, `data (json)`, `read_at`, `timestamps`).
- `system_settings`: Key-value configuration table with JSON casting and caching (`id`, `key`, `value`, `group`, `description`, `timestamps`).
- `grandeurs`: Metrological physical quantities and measurement standards (`id`, `name`, `symbol`, `type` (measurement/source, indexed), `timestamps`).
- `equipment`: Metrology and field equipment catalog (`id`, `internal_code`, `full_name`, `short_name`, `serial_number` (unique), `category` (indexed), `package` (indexed), `requires_calibration` (boolean, indexed), `designation`, `status` (indexed), `image_path`, `image_hash` (indexed), `certificate_path`, `notes`, `timestamps`, `deleted_at` (soft deletes)).
- `equipment_specifications`: Technical ranges and precision tolerances per equipment and physical quantity (`id`, `equipment_id` (FK cascade), `grandeur_id` (FK cascade), `range_min`, `range_max`, `accuracy_value`, `accuracy_type` (% / abs), `timestamps`).

---

## 3. Registered Models & Enums
- `App\Enums\AccountStatus`: Backed string enum (`Active = 'active'`, `Suspended = 'suspended'`) with UI labels, translation helpers, and unified `badgeVariant(): string` contract returning `'success'` for `Active` and `'danger'` for `Suspended`.
- `App\Enums\EmployeePosition`: Backed string enum (`GeneralManager`, `SeniorMeteringEngineer`, `MeteringEngineer`, `SeniorInstrumentationEngineer`, `MeteringTechnician`, `InstrumentationTechnician`) with unified semantic badge variants (`badgeVariant()` mapping Management to `primary`, Engineering to `info`, and Technicians to `neutral`) and category checks.
- `App\Enums\EmployeeStatus`: Backed string enum (`Active`, `Inactive`, `OnLeave`) with unified semantic badge variants (`badgeVariant()` mapping to `success`, `danger`, `warning`) and translation helpers.
- `App\Enums\EquipmentCategory`: Backed string enum (`MeasuringInstrument = 'measuring_instrument'`, `WorkTool = 'work_tool'`, `Vehicle = 'vehicle'`, `Other = 'other'`) with `badgeVariant()` and `requiresCalibrationByDefault()`.
- `App\Enums\EquipmentStatus`: Backed string enum (`Active = 'active'`, `Maintenance = 'maintenance'`, `Deployed = 'deployed'`, `Retired = 'retired'`, `Inactive = 'inactive'`) with `badgeVariant()` and `isAvailable()`.
- `App\Enums\EquipmentPackage`: Backed string enum (`Lot01 = 'lot_01'`, `Lot02 = 'lot_02'`, `VehicleLot = 'vehicle_lot'`, `None = 'none'`) with localized labels.
- `App\Enums\GrandeurType`: Backed string enum (`Measurement = 'measurement'`, `Source = 'source'`) with semantic badges.
- `App\Enums\AccuracyType`: Backed string enum (`Percentage = '%'`, `Absolute = 'abs'`).
- `App\Models\User`: Authenticatable user model (implements `MustVerifyEmail`; includes `FilterableTrait`, `HasActivity`, `HasFactory`, `HasRoles`, `Notifiable`; casts `'status' => AccountStatus::class`; attributes `profile_photo_path`, `photo_hash`, `profile_photo_url`; relation `employee(): HasOne`). Automatically assigned default role `'User'` on creation via `UserObserver::created()`. Immediate verification email dispatched on registration.
- `App\Models\Employee`: Master data staff model with soft deletes, Spatie activity logs, CAS image caching, `belongsTo(User::class)`, and filterable scopes.
- `App\Models\Grandeur`: Metrological quantity model defining physical measurement and source units.
- `App\Models\Equipment`: Enterprise equipment model with soft deletes, Spatie activity logs, CAS WebP image optimization, filterable scopes, and specifications relationships.
- `App\Models\EquipmentSpecification`: Technical specification entity binding equipment to physical measurement/source ranges and accuracy limits.
- `App\Observers\EquipmentObserver`: Forensic CAS image hash tracking and automatic media file deletion via `MediaOptimizationService::safeDelete()`.
- `App\Models\SystemSetting`: System configuration overrides model with cached access and JSON value casting.
- `App\Services\PermissionDiscoveryService`: RBAC catalog and sync service reading code-first permissions from `config/permissions.php`, defining super roles (`Super-Admin`) and default baseline role (`User`), with immutability guarantees.
- `App\Services\MediaOptimizationService`: Centralized media processing and compression gateway enforcing compulsory WebP conversion (80% quality, 1920px max width), Ghostscript PDF compression (/ebook 150dpi), and the Process & Destroy lifecycle eliminating temporary file storage leaks.
- `App\Services\EmployeeService`: Business service orchestrating employee creation, update, mission safety validation, photo optimization, and synchronization with linked users.
- `App\Repositories\EmployeeRepository`: Repository managing employee queries and eager loading user relationships to eliminate N+1 query bottlenecks.
- `App\Policies\EmployeePolicy`: Granular policy gating CRUD operations and specialized financial compensation access.
- `App\Services\EquipmentService`: Domain service managing equipment lifecycle, CAS WebP conversion, certificate PDF storage, specification synchronization, and statistics calculation.
- `App\Repositories\EquipmentRepository`: Repository handling equipment queries, relations eager loading (`specifications.grandeur`), and filtering.
- `App\Services\GrandeurService`: Domain service managing physical quantities and units, data integrity guards against equipment usage, and KPI counter analytics.
- `App\Repositories\GrandeurRepository`: Repository handling physical quantities queries, eager loading specification counts, and type filtering.
- `App\Http\Controllers\MetrologyController`: Thin controller managing Metrology & Equipment explorers with granular Gate permission authorization.
- `App\Http\Controllers\OperationsController`: Thin controller managing Operations & Projects explorers with granular Gate permission authorization.
- `App\Http\Controllers\AnalyticsController`: Thin controller managing Internal & Analytical Management explorers with granular Gate permission authorization.
- `App\Http\Controllers\MasterDataController`: Thin controller managing Master Data explorers with granular Gate permission authorization and employee CRUD operations.

---

## 4. Routes & Endpoints
- **Localized Web Routes** (`routes/web.php` wrapped in `LaravelLocalization::groupRoutes`):
  - Arabic (default, no prefix):
    - `GET /` -> Public welcome page (`welcome.blade.php`; automatically intercepts & redirects to `/system-tables/setup` when `User::count() === 0`).
    - `GET /system-tables/setup` -> First-Run Super Admin Setup Wizard (`SystemTableController@setup`; zero-user gate). **[NEW 2026-09-09]**
    - `POST /system-tables/setup` -> Provision First Super Admin Account (`SystemTableController@storeSetup`). **[NEW 2026-09-09]**
    - `GET /dashboard` -> Authenticated user executive workspace portal (`workspace.blade.php`).
    - `GET /metrology` & `GET /dashboard/metrology` -> Metrology & Equipments Dashboard (`MetrologyController@index`). **[NEW 2026-09-12]**
    - `GET /metrology/instruments` -> Measuring Instruments Explorer (`MetrologyController@instruments`). **[NEW 2026-09-11]**
    - `GET /metrology/equipment` -> Equipment Explorer (`MetrologyController@equipment`). **[NEW 2026-09-11]**
    - `GET /metrology/calibrator-movements` -> Calibrator Movements Explorer (`MetrologyController@calibratorMovements`). **[NEW 2026-09-11]**
    - `GET /metrology/calibration-certificates` -> Calibration Certificates Explorer (`MetrologyController@calibrationCertificates`). **[NEW 2026-09-11]**
    - `GET /metrology/units` -> Quantities & Units Explorer (`MetrologyController@units`). **[NEW 2026-09-12]**
    - `GET /operations` & `GET /dashboard/operations` -> Operations & Projects Dashboard (`OperationsController@index`). **[NEW 2026-09-12]**
    - `GET /operations/missions` -> Mission Management Explorer (`OperationsController@missions`). **[NEW 2026-09-11]**
    - `GET /operations/contracts` -> Contracts Explorer (`OperationsController@contracts`). **[NEW 2026-09-11]**
    - `GET /operations/attachments` -> Attachments List Explorer (`OperationsController@attachments`). **[NEW 2026-09-11]**
    - `GET /operations/warranties` -> Warranties Explorer (`OperationsController@warranties`). **[NEW 2026-09-11]**
    - `GET /operations/article-types` -> Classification of Articles Explorer (`OperationsController@articleTypes`). **[NEW 2026-09-12]**
    - `GET /analytics` & `GET /dashboard/analytics` -> Internal & Analytical Management Dashboard (`AnalyticsController@index`). **[NEW 2026-09-12]**
    - `GET /analytics/expenses` -> Expenses & Charges Explorer (`AnalyticsController@expenses`). **[NEW 2026-09-11]**
    - `GET /analytics/forecasts` -> Annual Forecasts Explorer (`AnalyticsController@forecasts`). **[NEW 2026-09-11]**
    - `GET /analytics/statistics` -> Company Statistics Explorer (`AnalyticsController@statistics`). **[NEW 2026-09-11]**
    - `GET /analytics/reports` -> Reports Management Explorer (`AnalyticsController@reports`). **[NEW 2026-09-11]**
    - `GET /master-data` & `GET /dashboard/master-data` -> Master Data Dashboard (`MasterDataController@index`). **[NEW 2026-09-12]**
    - `GET /master-data/clients` -> Clients Explorer (`MasterDataController@clients`). **[NEW 2026-09-11]**
    - `GET /master-data/employees` -> Employees Explorer (`MasterDataController@employees`). **[NEW 2026-09-11]**
    - `POST /master-data/employees` -> Store New Employee (`MasterDataController@storeEmployee`). **[NEW 2026-09-13]**
    - `PUT /master-data/employees/{employee}` -> Update Employee (`MasterDataController@updateEmployee`). **[NEW 2026-09-13]**
    - `DELETE /master-data/employees/{employee}` -> Soft Delete Employee (`MasterDataController@destroyEmployee`). **[NEW 2026-09-13]**
    - `GET /master-data/sites` -> Sites Explorer (`MasterDataController@sites`). **[NEW 2026-09-11]**
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
- **Services (`app/Services`):** `BaseService` (transaction manager & exception handling), `UserService` (domain logic), `ImageOptimizationService` (image compression & scaling), `FileUploadService` (standardized secure file uploads & storage management), `SystemTableService` (centralized queries, metrics aggregation, pagination for all 13 database tables), `DataPruningService` (central automated data pruning engine, lifecycle management, dynamic table discovery, eligible candidate filtering), `DatabaseBackupService` (wraps `Spatie\Backup\BackupDestination`, backup inspection, oldest/latest snapshot tagging, on-demand creation, secure download/delete with path traversal sanitization, point-in-time SQL extraction and database state restoration), `PermissionDiscoveryService` (static code-first catalog engine reading from `config/permissions.php`, automatic standard CRUD permission generator, stale permission pruning, and dynamic matrix aggregation).
- **Notifications (`app/Notifications`):** `SystemActivityAlert` (database-channel-only notification with unified payload: `title`, `message`, `type`, `causer`, `extra`).
- **Observers (`app/Observers`):** `UserObserver` (captures `created` & `deleted` events on User model; auto-dispatches `SystemActivityAlert` to all `Super-Admin` and `Admin` role users; gracefully handles missing roles).
- **Controllers:**
  - `App\Http\Controllers\ProfileController`: Refactored with `declare(strict_types=1);`, delegates `update` and `destroy` to `UserService` (transaction-wrapped).
  - `App\Http\Controllers\SystemTableController`: Thin controller orchestrating requests for the System Tables Explorer suite (includes `settings`, `toggleRegistration`, `updateSetting`).
  - `App\Http\Controllers\Api\NotificationController`: API (`index`, `unread`, `markAsRead`, `markAllAsRead`, `destroy`; uses `ApiResponseTrait`; enforces per-user notification isolation).
- **Console Commands (`app/Console/Commands`):** `SetupProjectCommand` (`php artisan project:setup [--fresh] [--force]`), `OptimizeImagesCommand` (`php artisan images:optimize`), `SyncTablePermissionsCommand` (`php artisan permissions:sync-tables [--dry-run]`), `DataPruneCommand` (`php artisan data:prune`), `ImportLegacyEmployeesCommand` (`php artisan employees:import-legacy`), `ImportLegacyEquipmentCommand` (`php artisan equipment:import-legacy`), `ImportLegacyCustomersCommand` (`php artisan customers:import-legacy`), `ImportLegacySitesCommand` (`php artisan sites:import-legacy`), `ImportLegacyWarrantiesCommand` (`php artisan warranties:import-legacy`), `CheckExpiringCertificatesCommand` (`php artisan metrology:check-expiring-certificates`).
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

## 8. Standing Architectural Directives & Conventions

- **Clean Architecture & Domain Decoupling:**
  - Controllers are ultra-thin: only accept validated FormRequests, delegate execution to Services/Repositories, and return views or redirects with standard flash keys (`with('success')`, `with('error')`, `with('warning')`, `with('info')`). Zero Eloquent queries or transactions in controllers.
  - Repositories (`app/Repositories/` implementing `app/Interfaces/`) encapsulate all data queries, eager loading (`with()`), and dynamic filtering (`FilterableTrait`) to eliminate N+1 bottlenecks.
  - Domain Services (`app/Services/`) manage business logic, arithmetic computations, atomic database transactions (`DB::transaction`), and media pipeline processing.
  - Dedicated FormRequests (`app/Http/Requests/`) are required for all mutative requests (`Store...Request`, `Update...Request`).
- **Design System, UI Tokens & Component Suite:**
  - Authoritative UI standards are centralized in [AGENTS.md](file:///c:/Project%20HARD/erp.gmtm-dz.com/AGENTS.md), `resources/css/tokens.css`, and core Blade components.
  - Standard buttons only: `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`.
  - Standard table suite only: `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`, `<x-table.action-*>` (`action-view`, `action-edit`, `action-delete`).
  - Standard filter: `<x-global-filter>` with permanent query string persistence (`->withQueryString()`, `$request->query()`).
  - Standard badges: `<x-badge :variant="...">` using 3 functional tiers (Management: `primary`, Engineering: `info`, Technicians: `neutral`). Status indicators use status dots (`:dot="true"`). PHP Enums implement `badgeVariant(): string`.
  - Zero inline styles (`style="..."` prohibited) and fluid full-width layout (`w-full px-4 sm:px-6 lg:px-8`).
- **Trilingual Localization Parity (100% English Master Keys):**
  - Canonical master keys in code (`__('...')`) MUST be in English. Arabic and French text exist exclusively as translated values in `lang/ar.json` and `lang/fr.json`. Exact 1-to-1 key parity is maintained (currently 1,237 keys).
- **Static Code-First RBAC Architecture:**
  - Permissions are statically declared in `config/permissions.php` under `$groups` and synchronized via `php artisan permissions:sync-tables`.
  - Super role bypass (`Super-Admin`) is declared in `AppServiceProvider`. Anti-self-action policy strictly blocks self-demotion, self-suspension, or self-deletion.
- **Media Optimization & CAS Engine (`MediaOptimizationService`):**
  - All uploaded media must transit through `MediaOptimizationService` for compulsory WebP conversion (80% quality, max 1920px), SHA-256 CAS deduplication, and "Process & Destroy" temporary file unlinking.
- **Security Quarantine:**
  - System Administration (`resources/views/system/**`, `system-tables.*`) is quarantined behind `Super-Admin` role exclusivity and requires explicit developer verification.
