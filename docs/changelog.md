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








