# Changelog: tools.gmtm-dz.com

All notable changes, features, refactorings, and fixes will be documented in this file.

The format is based on Keep a Changelog.

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


