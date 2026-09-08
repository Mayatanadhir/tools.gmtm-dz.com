# Architecture Log: tools.gmtm-dz.com

This document tracks fundamental architectural patterns, engineering decisions, and conventions adopted across the lifecycle of the project.

---

## [ADR-001] Backend Architecture & Separation of Concerns
- **Date:** 2026-09-06
- **Status:** Accepted / Active
- **Context:** The application needs a robust, scalable backend structure adhering to Clean Architecture and SOLID principles while avoiding bloated controllers.
- **Decision:**
  1. **Thin Controllers:** Controllers only accept requests and delegate directly to FormRequests, Actions, or Services.
  2. **Action Pattern (`app/Actions`):** Dedicated classes for single, distinct operations.
  3. **Service Layer (`app/Services`):** Dedicated classes for multi-step workflows or coordinating external integrations.
  4. **Strict Typing:** `declare(strict_types=1);` on all PHP files with strict return and argument typing (PHP 8.4+).
  5. **API Contract:** Unified JSON responses (`{"success": bool, "message": string, "data": mixed, "errors": mixed}`).
- **Consequences:** Clean separation of concerns, high testability, and isolated domain logic.

---

## [ADR-002] Enforced Project Memory & Documentation Protocol
- **Date:** 2026-09-06
- **Status:** Accepted / Active
- **Context:** Ensure every AI coding assistant maintains full historical awareness and systematically updates documentation on each intervention.
- **Decision:**
  - Enforce the 3-File Protocol (`docs/project_state.md`, `docs/changelog.md`, `docs/ARCHITECTURE_LOG.md`) across all system prompt rules (`.antigravityrules`, `AGENTS.md`, `CLAUDE.md`).
  - Read-First & Update-Last policy is non-negotiable for all code changes.

---

## [ADR-003] Antigravity MCP Integration for Laravel Boost
- **Date:** 2026-09-06 (Updated 2026-09-07)
- **Status:** Accepted / Active
- **Context:** Antigravity IDE requires MCP servers in `~/.gemini/config/mcp_config.json` or `.agents/plugins/`, whereas Laravel Boost defaults to `.mcp.json` (Claude standard). On Windows with Laravel Herd, PHP is located in `~/.config/herd/bin/php84/php.exe` and is not present in standard system PATH by default. Additionally, workspace paths with spaces (e.g. `c:\Project HARD`) require deterministic absolute paths.
- **Decision:**
  1. Populate global `~/.gemini/config/mcp_config.json` with `laravel-boost` stdio server entry explicitly invoking `C:\Users\HP\.config\herd\bin\php84\php.exe` and `c:\Project HARD\tools.gmtm-dz.com\artisan boost:mcp`.
  2. Keep `.agents/mcp_config.json` and `.agents/plugins/laravel-boost/mcp_config.json` updated with matching absolute paths.
  3. Append `C:\Users\HP\.config\herd\bin` to Windows User `PATH` environment variable.
- **Consequences:** Eliminates "No MCP servers installed" errors in Antigravity IDE and provides stable agent access to `database-query`, `database-schema`, `get-absolute-url`, `browser-logs`, and `search-docs`.

---

## [ADR-004] Core Foundation Layer (Repository, Service & API Contract)
- **Date:** 2026-09-06
- **Status:** Accepted / Active
- **Context:** Promote DRY code, unified error handling, safe database transactions, and consistent API responses across all domain modules.
- **Decision:**
  1. **ApiResponseTrait (`app/Traits/ApiResponseTrait.php`):** Standardizes API responses (`successResponse`, `errorResponse`) conforming to ADR-001 payload format.
  2. **BaseRepository (`app/Interfaces/BaseRepositoryInterface.php` & `app/Repositories/BaseRepository.php`):** Abstract repository encapsulating common Eloquent queries (`all`, `find`, `findOrFail`, `create`, `update`, `delete`, `paginate`).
  3. **BaseService (`app/Services/BaseService.php`):** Centralizes database transaction orchestration (`executeInTransaction`) and unified error logging.
- **Consequences:** Domain repositories and services extend robust, reusable foundation classes rather than reinventing query and transaction boundaries.

---

## [ADR-005] User Module Architecture (Repository-Service Pattern)
- **Date:** 2026-09-06
- **Status:** Accepted / Active
- **Context:** Isolate user management and domain logic cleanly from HTTP transport layers and framework internals, focusing exclusively on the `User` domain.
- **Decision:**
  1. **UserRepositoryInterface (`app/Interfaces/UserRepositoryInterface.php`):** Extends `BaseRepositoryInterface` with user-specific query contracts (`findByEmail`, `updatePassword`, `getVerifiedUsers`).
  2. **UserRepository (`app/Repositories/UserRepository.php`):** Concrete implementation injected with `App\Models\User`.
  3. **UserService (`app/Services/UserService.php`):** Encapsulates core user business logic (`register`, `updateProfile`, `changePassword`, `deleteAccount`, `listUsers`) executed within safe database transactions.
  4. **RepositoryServiceProvider (`app/Providers/RepositoryServiceProvider.php`):** Registers IoC container bindings for repositories and interfaces, registered in `bootstrap/providers.php`.
- **Consequences:** High testability, decoupled persistence layer, and strict adherence to SOLID principles.

---

## [ADR-006] Image Optimization Service & Artisan CLI Pipeline
- **Date:** 2026-09-06
- **Status:** Accepted / Active
- **Context:** Refactor raw procedural GD image resizing script with hardcoded paths into a robust, object-oriented service and CLI tool adhering to Clean Architecture.
- **Decision:**
  1. **Package Adoption:** Integrated `intervention/image` (v4/v3 API) using PHP GD driver.
  2. **Service Layer (`app/Services/ImageOptimizationService.php`):**
     - Encapsulates aspect-ratio scaling (`scaleDown`), dimension constraints (`client-` => 600, `MAYATA` => 800, default => 1400), and compression.
     - Strict zero-division and invalid dimension protection (`width > 0 && height > 0`).
     - Preserves alpha transparency channels without unnecessary canvas flattening.
     - Supports non-destructive operations via an explicit `--backup` flag (`.bak` copies).
     - Centralizes exception handling and structured error reporting without `@` error suppression.
  3. **CLI Interface (`app/Console/Commands/OptimizeImagesCommand.php`):** Exposes `images:optimize` Artisan command with path argument, `--backup`, `--quality`, `--max-width`, and `--files` options, rendering tabular progress metrics and bandwidth savings.
- **Consequences:** Fully testable, reusable image optimization pipeline suitable for web assets and background workers.

---

## [ADR-007] Role-Based Access Control (RBAC) via Spatie Laravel Permission
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** The system requires an industrial-strength, performant, and extensible authorization system to handle hierarchical user permissions and route protection without hardcoded logic.
- **Decision:**
  1. **Package Adoption:** Standardized on `spatie/laravel-permission` (v8.3), leveraging its built-in cached permissions (reduced query overhead) and granular role-to-permission mapping.
  2. **Model Integration:** Enabled `HasRoles` on `App\Models\User` to unify authorization queries (`can()`, `hasRole()`, `hasPermissionTo()`).
  3. **Middleware Routing:** Registered `role`, `permission`, and `role_or_permission` aliases in `bootstrap/app.php` using Laravel 11/13 fluent middleware configuration.
  4. **Initial Seeding (`database/seeders/RolesAndPermissionsSeeder.php`):**
     - Clears cached permissions on seed run via `PermissionRegistrar::forgetCachedPermissions()`.
     - Establishes canonical roles (`Super-Admin`, `Admin`, `User`) and core permissions (`view instruments`, `create instruments`, `edit instruments`, `delete instruments`, `manage users`).
     - Ensures `Super-Admin` receives all permissions, and assigns the role to the first user in the system.
- **Consequences:** Robust, secure access control with cached checks, comprehensive test coverage in `tests/Feature/RoleAndPermissionTest.php`, and streamlined route-level security.

---

## [ADR-008] Automated Audit Trail and Activity Logging via Spatie Laravel Activitylog
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Precise tracking of model lifecycle mutations (who created, updated, or deleted records, when, and exact delta of modified attributes) is critical for system auditing, data integrity, and forensic analysis.
- **Decision:**
  1. **Package Adoption:** Standardized on `spatie/laravel-activitylog` (v5.1), providing structured JSON-based delta storage across `activity_log` table with dedicated `attribute_changes` column.
  2. **Model Integration (`app/Models/User.php`):**
     - Integrated `HasActivity` concern (combines `LogsActivity` and `CausesActivity`).
     - Explicit attribute whitelisting: `logOnly(['name', 'email'])`.
     - Security policy: Strictly excluded sensitive credentials (`password`, `remember_token`) from audit storage.
     - Performance optimization: Enabled `logOnlyDirty()` and `dontLogEmptyChanges()` to prevent database bloat when unmonitored attributes change.
  3. **Causer Resolution:** Automated tracking through Laravel session/auth resolver ensuring every mutation is tied to authenticated actor `causer_id` and `causer_type`.
- **Consequences:** High-fidelity, tamper-evident audit trails with verified test coverage in `tests/Feature/ActivityLogTest.php` without credential leakage.

---

## [ADR-009] Centralized File Upload & Storage Management Architecture
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Unifying file upload, validation, deletion, and replacement workflows across services and controllers prevents code duplication, arbitrary file execution, and storage path collisions.
- **Decision:**
  1. **Service Layer (`app/Services/FileUploadService.php`):**
     - Encapsulates `Storage` facade interactions with customizable disk targets (`public`, `local`, `s3`).
     - Non-deterministic unique filename generation using cryptographically secure UUID (`{uuid}.{extension}`) preventing path traversal and name conflicts.
     - Multi-tier validation: Enforces valid file status, extension whitelisting, and strict blacklisting of dangerous executable extensions (`php`, `exe`, `sh`, `bat`, `cmd`, etc.).
     - Atomic replacement pattern (`replaceFile`): Successfully stores new file before purging legacy file.
  2. **Public Web Assets:** Established `public/storage` symlink targeting `storage/app/public` via `php artisan storage:link`.
- **Consequences:** Safe, standardized file handling readily injectable into controllers and services with 100% test coverage in `tests/Unit/FileUploadServiceTest.php`.

---

## [ADR-010] Declarative Dynamic Filtering Architecture (FilterableTrait)
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Applications requiring versatile search, sort, and filter features across APIs and data tables typically suffer from duplicated boilerplate query methods in controllers and repositories.
- **Decision:**
  1. **Dynamic Local Scope (`app/Traits/FilterableTrait.php`):**
     - Exposes `scopeFilter(Builder $query, array|Request $filters = [], array $allowedFilters = [])`.
     - Strict Security Whitelisting: Only applies criteria matching model-defined `$filterable` property or explicitly passed `$allowedFilters`, silently discarding unapproved parameters (such as `password`).
     - Extensibility via Custom Hooks: Intercepts and delegates to `filter{Field}()` methods on the model (e.g. `filterRole` using Spatie relationships).
     - Multi-Column Search: Transparently handles `search` or `q` keywords against `$searchable` model attributes via grouped `orWhere LIKE`.
     - Range & Array Support: Automatically processes `from`/`to`, `min`/`max` ranges and `whereIn` array values.
     - Dynamic Sorting: Applies safe column ordering via `sort_by` and `sort_direction`.
  2. **Repository Integration:** Extended `BaseRepositoryInterface` and `BaseRepository` with `filter()` and `paginateWithFilter()`.
- **Consequences:** Eliminates repetitive query construction across controllers and repositories while maintaining strict parameter whitelisting and 100% test coverage in `tests/Feature/FilterableTraitTest.php`.

---

## [ADR-011] Automated Backup & Disaster Recovery Architecture
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Production resiliency requires robust, automated, zero-downtime database and file backup schedules, along with cleanup retention strategies to prevent disk saturation.
- **Decision:**
  1. **Package Adoption:** Standardized on `spatie/laravel-backup` (v10.3) for industrial backup workflows.
  2. **Database Dump Configuration (`config/database.php`):**
     - Configured `dump` options on the `mysql` connection with `dump_binary_path` pointing to `C:\Program Files\MySQL\MySQL Server 8.0\bin` (overridable via `DB_DUMP_BINARY_PATH`).
     - Enabled `use_single_transaction` to prevent InnoDB table locking during dumps.
     - Set a 5-minute timeout threshold (`timeout => 300`) to accommodate growing datasets.
  3. **Storage Destination & Cleanup Strategy (`config/backup.php`):**
     - Targeted `local` disk destination with automated ZIP archive compression.
     - Enabled daily retention cleanup to automatically prune old backups based on exponential decay rules.
  4. **Automated Scheduling (`routes/console.php`):**
     - `backup:clean` scheduled at `01:00` daily.
     - `backup:run` scheduled at `01:30` daily.
- **Consequences:** Safe, automated daily backups of MySQL and application files with comprehensive test coverage in `tests/Feature/BackupConfigurationTest.php`.

---

## [ADR-012] Database Notifications & Automated Activity Alert Architecture
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Administrators (`Super-Admin`, `Admin`) need real-time, persistent awareness of sensitive system events (user creation, deletion) without requiring email or external services. The system must support future extension to other entity types (equipment, reports, etc.).
- **Decision:**
  1. **Storage:** Used Laravel's built-in `database` notification channel with the standard `notifications` table (UUID PK, polymorphic notifiable, JSON data, `read_at`). Generated via `php artisan make:notifications-table`.
  2. **Centralized Notification Class (`app/Notifications/SystemActivityAlert.php`):**
     - Single class handles all system activity alert types, discriminated by a `type` field in the payload.
     - Unified payload: `{title, message, type, causer, extra[]}` stored as JSON.
     - Only the `database` channel is declared — no mail/broadcast bloat.
     - PHP 8 constructor property promotion with `readonly` fields enforces payload immutability.
  3. **Observer Pattern (`app/Observers/UserObserver.php`):**
     - Observer is the architectural insertion point — keeps notification logic out of models and services.
     - Registered via `#[ObservedBy([UserObserver::class])]` PHP attribute on `User` model (no ServiceProvider boot() registration needed).
     - Targets recipients via Spatie Permission: `User::role(['Super-Admin', 'Admin'])->get()`.
     - Graceful degradation: catches `RoleDoesNotExist` to prevent test suite contamination when roles are not seeded.
     - Designed as the canonical pattern — new observers (e.g. `EquipmentObserver`, `ReportObserver`) can be added following the exact same structure.
  4. **API Layer (`app/Http/Controllers/Api/NotificationController.php` + `routes/api.php`):**
     - Five RESTful endpoints under `/api/notifications` protected by `auth` middleware.
     - Per-user isolation enforced at the query level (`$request->user()->notifications()->find($id)`) — cross-user access returns 404.
     - Uses `ApiResponseTrait` for ADR-001-compliant JSON envelope.
     - `markAllAsRead` uses a bulk `update(['read_at' => now()])` for O(1) DB writes regardless of unread count.
- **Consequences:** Extensible, persistent, zero-dependency notification architecture with 14 tests (48 assertions) in `DatabaseNotificationTest`. Future entity observers follow the same pattern with no architectural changes required.

---

## [ADR-013] URL Localization & Architectural View Isolation Architecture
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** The application requires multilingual capabilities across Arabic (`ar`), English (`en`), and French (`fr`), with Arabic designated as the default locale. Arabic requires Right-to-Left (RTL) reading order, tailored font metrics (Noto Kufi Arabic), and specific layout alignments, while English and French require Left-to-Right (LTR) structure (Figtree). Single-layout templates dynamically toggling `dir="rtl"` and conditional inline classes introduce significant visual bugs, CSS bloat, and maintainability friction.
- **Decision:**
  1. **Routing Package & Configuration (`config/laravellocalization.php`):**
     - Adopted `mcamara/laravel-localization` (v2.4.2).
     - Restricted supported locales strictly to `['ar', 'en', 'fr']`.
     - Set `hideDefaultLocaleInURL => true` to keep root URLs clean (`/dashboard` for Arabic, `/en/dashboard` for English, `/fr/dashboard` for French).
     - Enabled browser header auto-detection via `env('LARAVELLOCALIZATION_USE_ACCEPT_LANGUAGE_HEADER', true)`, configured in `phpunit.xml` as `false` to avoid host machine OS locale leak during automated test runs.
  2. **Strict Architectural View Isolation:**
     - Separated layouts into dedicated files: `layouts/app-rtl.blade.php` and `layouts/guest-rtl.blade.php` for Arabic vs `layouts/app-ltr.blade.php` and `layouts/guest-ltr.blade.php` for LTR languages.
     - Separated navigation into `layouts/navigation-rtl.blade.php` and `layouts/navigation-ltr.blade.php`.
     - Eliminated conditional `dir="{{ ... }}"` and conditional inline CSS classes inside layouts.
  3. **Isolated Vite Bundling (`vite.config.js`):**
     - Created independent stylesheet and script entry points:
       - `resources/css/app-rtl.css` & `resources/js/app-rtl.js`
       - `resources/css/app-ltr.css` & `resources/js/app-ltr.js`
     - Bundled as distinct assets via Vite, preventing RTL/LTR CSS conflicts.
  4. **Component-Level Dynamic View Resolution (`AppLayout` & `GuestLayout`):**
     - Updated `App\View\Components\AppLayout` and `App\View\Components\GuestLayout` to inspect `app()->getLocale() === 'ar'`.
     - Dynamically returns `layouts.app-rtl` / `layouts.guest-rtl` or `layouts.app-ltr` / `layouts.guest-ltr`.
     - Zero refactoring required in existing page views; all views utilizing `<x-app-layout>` or `<x-guest-layout>` automatically inherit the correct layout and stylesheet bundle.
  5. **Runtime Locale Synchronization (`app/Http/Middleware/SetLocale.php`):**
     - Registered custom `SetLocale` middleware in the `web` pipeline (`bootstrap/app.php`) to ensure `app()->getLocale()`, `LaravelLocalization::getCurrentLocale()`, and the Laravel translator instances remain in sync for both URL-prefixed and root requests.
  6. **Route Name Collision Prevention (`routes/web.php`):**
     - Wrapped routes in `LaravelLocalization::groupRoutes()`. For non-default locales, added `'as' => "{$locale}."` so default named routes (`dashboard`, `profile.edit`, etc.) remain intact without name collision.
  7. **Reusable Language Switcher (`components/language-switcher.blade.php`):**
     - Standalone Alpine.js dropdown displaying flags (🇩🇿, 🇬🇧, 🇫🇷), language names, active checkmark indicator, and non-active redirect links generated via `LaravelLocalization::getLocalizedURL()`.
  8. **Comprehensive Translation Catalogs (`lang/`):**
     - Unified JSON dictionaries: `lang/ar.json`, `lang/en.json`, `lang/fr.json`.
     - PHP validation, auth, password, and pagination catalogs for Arabic, English, and French.
- **Consequences:** Clean separation of concerns between RTL and LTR view tiers, zero style bleed, optimal font loading per language, and full backward compatibility with existing Blade templates. Verified by 16 feature tests (48 assertions) with all 108 application tests passing.

---

## [ADR-014] Theme Management Architecture: Zero-FOUC & Alpine.js Tri-State Architecture
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Applications with dark mode capabilities frequently suffer from Flash of Unstyled Content (FOUC), where dark mode users encounter an abrupt flash of white during initial page load before CSS/JS bundles hydrate. Furthermore, modern UX requires supporting three distinct states (`light`, `dark`, `system`), reactive synchronization across multiple UI controls (e.g. desktop navbar, mobile drawer, guest views), and live tracking of operating system color scheme changes.
- **Decision:**
  1. **Zero-FOUC Prevention Script:**
     - Injected a minimal, synchronous inline JavaScript snippet in the `<head>` of all layouts (`layouts/app-rtl.blade.php`, `layouts/app-ltr.blade.php`, `layouts/guest-rtl.blade.php`, `layouts/guest-ltr.blade.php`, and `resources/views/welcome.blade.php`).
     - Script reads `localStorage.getItem('theme')` or queries `window.matchMedia('(prefers-color-scheme: dark)').matches`, applying or removing the `.dark` CSS class on `document.documentElement` *before* DOM rendering begins.
     - Guarantees 0ms flicker regardless of connection speed or asset download time.
  2. **Tailwind CSS Configuration:**
     - Enabled `darkMode: 'class'` in [tailwind.config.js](file:///d:/HARD%20Project/tools.gmtm-dz.com/tailwind.config.js) to activate Tailwind's `dark:` modifier when the `.dark` class is present on `<html>`.
  3. **Alpine.js Tri-State Management (`components/theme-switcher.blade.php`):**
     - Implemented an Alpine.js component tracking three states:
       - `light`: Forces light mode, persists `'light'` to `localStorage.theme`.
       - `dark`: Forces dark mode, persists `'dark'` to `localStorage.theme`.
       - `system`: Removes `localStorage.theme`, delegates to OS color preference via `matchMedia('(prefers-color-scheme: dark)')`.
     - Uses a custom `theme-changed` window event so toggling the theme in one component (e.g. desktop menu) instantly synchronizes other components on the page (e.g. mobile drawer) without a page reload.
     - Adds a change event listener to `window.matchMedia('(prefers-color-scheme: dark)')` to react live to OS dark/light mode toggles while in `system` mode.
  4. **Component Design & Isolation:**
     - Created reusable `<x-theme-switcher />` featuring dynamic icons (Sun for light, Moon for dark, Monitor for system), translated labels (`__('Light')`, `__('Dark')`, `__('System')`), and an accessible dropdown menu matching `<x-language-switcher />`.
     - Integrated `<x-theme-switcher />` adjacent to `<x-language-switcher />` across all desktop and mobile navigation layouts.
  5. **Component Styling & Token Enhancements:**
     - Added comprehensive `dark:` classes (`dark:bg-gray-800`, `dark:bg-gray-900`, `dark:border-gray-700`, `dark:text-gray-100`, `dark:text-gray-200`) across navigation menus, headers, guest cards, and [dashboard.blade.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/resources/views/dashboard.blade.php).
  6. **Translation Catalogs:**
     - Added `"Theme"`, `"Light"`, `"Dark"`, and `"System"` keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
- **Consequences:** Seamless dark mode experience with zero visual flicker, unified UI styling across RTL and LTR view tiers, full accessibility, reactive cross-component synchronization, and 100% test coverage (9 tests, 54 assertions in `ThemeManagementTest`, 117/117 total tests passing).

---

## [ADR-015] Strict Separation of Concerns (Backend, Frontend & CSS)
- **Date:** 2026-09-07
- **Status:** Accepted / Active
- **Context:** Large-scale maintainability and security require a zero-tolerance policy against code mixing between server-side PHP logic, client-side Blade markup, and presentation styles. Mixing database queries in Blade views or embedding inline `style="..."` attributes creates security vulnerabilities (e.g. CSP violations), code bloat, and architectural degradation.
- **Decision:**
  1. **Strict Layer Boundary Isolation:**
     - **Backend Layer (`app/`, `routes/`, `database/`):** Exclusively pure PHP logic. Absolutely zero HTML tags, inline styles, or frontend scripts inside Controllers, Actions, Services, Repositories, or Models.
     - **Frontend Layer (`resources/views/`, `resources/js/`):** Exclusively presentation markup and client-side interactions. Strictly prohibited from running raw SQL queries, Eloquent queries (`Model::find()`, `Model::where()`), or business logic. All data must be passed from controllers or view composers via API Resources / View Data.
     - **CSS & Styling Layer (`resources/css/`, Tailwind):**
       - Complete prohibition of the `style="..."` attribute in HTML/Blade.
       - Pure utility-first styling with Tailwind CSS or external stylesheet declarations in `resources/css/`.
       - Prohibited raw `<style>` blocks inside PHP files or JavaScript components unless structurally required.
  2. **Refactoring Existing Inline Styles:**
     - Eliminated legacy inline `style="display: none;"` in interactive dropdown components (`theme-switcher.blade.php`, `language-switcher.blade.php`), replacing them with standard Alpine.js `x-cloak` and dedicated stylesheet rules in `resources/css/app-rtl.css` and `resources/css/app-ltr.css`.
  3. **Multi-File Response Mandate:**
     - Features requiring changes across backend and frontend/CSS layers must be structured as discrete, isolated file implementations with explicit paths.
- **Consequences:** Strong Content Security Policy (CSP) compatibility, elimination of style leakage, clean testability, and total architectural clarity across all future features.

---

## [ADR-016] Profile Module Clean Architecture Delegation & UI Theme Alignment
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** The profile views and `ProfileController` retained default Laravel Breeze starter code, lacking dark mode theme alignment (producing glaring white containers against dark mode backgrounds) and bypassing the domain `UserService` layer by manipulating Eloquent models directly in controller methods without explicit typing.
- **Decision:**
  1. **Backend Delegation to Domain Service:**
     - Injected `UserService` into `ProfileController` via constructor property promotion.
     - Added `declare(strict_types=1);` and explicit return type hinting.
     - Delegated `update` and `destroy` operations directly to `UserService::updateProfile()` and `UserService::deleteAccount()`, guaranteeing execution within safe atomic database transactions (`executeInTransaction`).
  2. **UI Theme Alignment & Dark Mode Support:**
     - Added Tailwind `dark:` variant classes across `resources/views/profile/edit.blade.php` and its sub-views (`update-profile-information-form.blade.php`, `update-password-form.blade.php`, `delete-user-form.blade.php`).
     - Upgraded shared UI components (`input-label`, `text-input`, `input-error`, `primary-button`, `secondary-button`, `danger-button`, `modal`, `dropdown`, `dropdown-link`, `responsive-nav-link`) with consistent dark backgrounds, borders, text, and focus ring offset classes.
  3. **Strict Compliance with Separation of Concerns (Rule 12):**
     - Zero inline `style="..."` attributes utilized.
     - Complete presentation logic isolated in Blade templates, with zero queries or business logic in views.
- **Consequences:** Flawless visual consistency across light and dark modes in both RTL and LTR orientations, complete adherence to Clean Architecture principles across the profile domain, and full automated test verification (118/118 tests passing).

---

## [ADR-017] Navigation Bar Brand Identity & Logo Aspect Ratio Optimization
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** The application brand logo contains an integrated graphic emblem, the "ENGI-MATE" title, and a subtitle. Previously, rendering this full composite image in the navigation bar constrained to a tiny 40x40 (`h-10 w-10`) box alongside a duplicate text `<span>ENGI-MATE</span>` caused the internal logo typography to become an illegible, blurry smear while redundantly repeating the company name. Furthermore, unnecessary transparent margins within the image canvas wasted more than 21% of the vertical display area.
- **Decision:**
  1. **Canvas Bounding Box Optimization:**
     - Trimmed excessive transparent margins from `public/images/logo.png` and `public/images/logo-dark.png` to a tight 426x426 canvas, maximizing optical clarity.
  2. **Elimination of Redundant Adjacent Typography:**
     - Removed the duplicate `<span ...>ENGI-MATE</span>` element from `resources/views/layouts/navigation-ltr.blade.php` and `resources/views/layouts/navigation-rtl.blade.php`.
  3. **Responsive Dimension Scaling:**
     - Scaled the standalone brand logo lockup to `h-12 w-auto sm:h-14` (48px to 56px within the 64px header), providing clean vertical centering, clear legibility of the integrated brand name, and subtle hover micro-scaling.
  4. **Aspect Ratio Preservation in Component:**
     - Adjusted `resources/views/components/application-logo.blade.php` to use `h-full w-auto object-contain`, ensuring the `<picture>` element preserves natural proportions across all breakpoints.
  5. **Vite Production Bundling:**
     - Recompiled production assets with `npm run build` to ensure all responsive Tailwind dimension tokens are embedded in the CSS bundles.
  6. **Purge of Legacy Single-Layout Templates:**
     - Deleted obsolete, non-compliant Breeze templates (`resources/views/layouts/navigation.blade.php`, `layouts/app.blade.php`, `layouts/guest.blade.php`) to eliminate dead code and prevent any architectural regression or confusion.
  7. **Navigation Link Dark Mode Alignment:**
     - Upgraded `resources/views/components/nav-link.blade.php` with explicit active/inactive dark mode tokens (`dark:text-gray-100`, `dark:border-indigo-500`, `dark:text-gray-400`, `dark:hover:text-gray-300`), preventing black text in dark navigation bars.
- **Consequences:** Clean, modern, high-contrast navbar presentation across both Light and Dark themes in RTL and LTR modes, zero duplicate branding text, completely streamlined view directory, and 100% passing test suite (118 tests, 378 assertions).

---

## [ADR-018] Centralized System & Database Tables Explorer Architecture
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** The application utilizes 13 interconnected database tables across multiple domains (authentication, RBAC, audit trails, notifications, queue jobs, cache memory, and sessions). Administrators and developers previously lacked a unified, modern interface to inspect, monitor, and troubleshoot database state, activity mutations, failed background jobs, and active user sessions without direct database access.
- **Decision:**
  1. **Backend Isolation & Domain Service Layer:**
     - Created `App\Services\SystemTableService` to encapsulate all database aggregation, counting, relation querying, searching, and pagination.
     - Implemented safe table existence checks (`Schema::hasTable`) to guard against missing tables.
     - Kept `App\Http\Controllers\SystemTableController` strictly thin, accepting requests and delegating directly to the service.
  2. **Domain-Driven Categorization & Unified Navigation:**
     - Clustered the 13 tables into 6 intuitive domains:
       1. **Users & Sessions:** `users`, `sessions`, and `password_reset_tokens`.
       2. **Roles & Permissions:** `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.
       3. **Activity Log:** `activity_log` with JSON mutation diff inspection.
       4. **Notifications:** `notifications` with payload preview.
       5. **Queues & Jobs:** `jobs`, `failed_jobs` (with exception trace modal), and `job_batches`.
       6. **Cache & Locks:** `cache` and `cache_locks` with TTL and lock status.
     - Implemented `<x-system-tabs>` universal navigation component with responsive horizontal scroll and active state indicators.
  3. **Strict Compliance with Separation of Concerns (Rule 12):**
     - Absolute zero inline `style="..."` attributes used across all templates.
     - Pure utility-first Tailwind CSS classes with dual Light/Dark Mode parity.
     - Interactive inspection modals (JSON delta comparisons and exception traces) implemented via Alpine.js with clean backdrop blur.
  4. **Multi-Locale & Auth Security:**
     - All routes registered under `LaravelLocalization` with `auth` and `verified` middleware protection.
     - Comprehensive localized dictionary keys across Arabic, English, and French (`lang/ar.json`, `lang/en.json`, `lang/fr.json`).
  5. **Verification & Quality:**
     - 100% automated test coverage in `tests/Feature/SystemTableTest.php` (8 feature tests, 41 assertions).
     - Formatted with Laravel Pint (`vendor/bin/pint --dirty --format agent`).
  6. **Security Quarantine & Developer Oversight (Rule 13):**
     - Designated the entire module (`resources/views/system/**`, `SystemTableController`, `SystemTableService`, `system-tables.*` routes) as high-security internal infrastructure.
     - Strictly quarantined from public and standard user areas.
     - Mandated that the AI assistant must explicitly ask the supervising developer: *"هل هذه الإضافة تنتمي إلى هذا الملف/القسم الأمني السري أم لا؟"* before applying any modifications or additions touching this security scope.
- **Consequences:** High-fidelity observability into all application database tables directly from the web interface, zero security leakage of sensitive fields, complete dark mode and RTL/LTR compatibility, and total test suite passing (126/126 tests).

---

## [ADR-019] Standardized Semantic Button & Color Token System
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Across different views and authentication forms, buttons historically suffered from disparate, non-standard styling, ranging from default Breeze generic black/white styles to ad-hoc Tailwind button classes. A unified design system requires strict visual hierarchy and deterministic color semantics mapped to user actions.
- **Decision:**
  1. **Standardized Reusable Blade Button Components (`resources/views/components/`):**
     - `<x-primary-button>`: Aligned to ENGI-MATE Brand Safety Orange (`bg-orange-500`, `dark:bg-orange-600`) for primary calls to action (Login, Register, Save, Submit, Filter).
     - `<x-secondary-button>`: Neutral bordered gray for non-destructive, dismissive, or secondary actions (Cancel, Close, Reset).
     - `<x-danger-button>`: High-contrast Rose/Red (`bg-rose-600`) for permanent deletions and destructive tasks.
     - `<x-success-button>`: Emerald Green (`bg-emerald-600`) for approvals, resolutions, and marking tasks completed.
     - `<x-warning-button>`: Amber Yellow (`bg-amber-500`) for retries, cautions, and holding operations.
     - `<x-info-button>`: Indigo Blue (`bg-indigo-600`) for data inspection, payload viewing, and mutation diffs.
  2. **Strict Design & AI Enforcement (Rule 14):**
     - Prohibition of raw `<button>` elements with ad-hoc backgrounds or arbitrary inline styling.
     - Mandated usage of standardized components across all AI assistants and developers in `AGENTS.md` and `.ai/rules/views.md`.
     - 100% Dark Mode and RTL/LTR parity guaranteed across all button states (hover, active, focus-ring).
- **Consequences:** Cohesive visual identity aligned with ENGI-MATE brand, zero visual regressions, predictable user affordances, and streamlined maintainability.

---

## [ADR-020] Unified Reusable Table Component Architecture
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Tabular data presentation across the application—especially in the newly implemented 13-table System & Database Tables Explorer—initially made use of repeated, raw HTML `<table>`, `<thead>`, `<tbody>`, `<th>`, `<tr>`, and `<td>` markup with duplicative Tailwind utility classes. This presented maintenance overhead, inconsistency risks across light/dark modes, and divergence in empty states and pagination styling.
- **Decision:**
  1. **Standardized Reusable Blade Table Suite (`resources/views/components/table/` and `table.blade.php`):**
     - `<x-table>`: Primary wrapper component providing a rounded-xl container, light/dark mode background, border, horizontal overflow auto-scroller, optional `<x-slot:toolbar>`, structured `<thead class="...">` when `<x-slot:header>` is provided, default slot for `<tbody>`, and an optional `<x-slot:pagination>` container.
     - `<x-table.th>`: Standardized header cell with uppercase font, bold tracking, text-start alignment, and padding.
     - `<x-table.tr>`: Standardized table row wrapper with subtle hover state transitions in light (`hover:bg-gray-50/70`) and dark (`dark:hover:bg-gray-700/30`) modes.
     - `<x-table.td>`: Standardized table body cell with uniform typography, text color tokens, and vertical centering.
     - `<x-table.empty>`: Standardized empty state row accepting a dynamic `colspan` and optional `message`, rendering a clean SVG icon and translated message.
  2. **Complete System Views Refactoring:**
     - Fully refactored `users.blade.php`, `roles.blade.php`, `activity-log.blade.php`, `notifications.blade.php`, `queues.blade.php`, and `cache.blade.php` to exclusively employ the `<x-table>` suite.
     - 100% elimination of raw `<table>` elements across the `resources/views/system/` domain.
  3. **Strict Design & Architectural Enforcement (Rule 15):**
     - Prohibition of ad-hoc HTML `<table>` elements without using the standardized component suite.
     - Zero inline `style="..."` attributes.
     - Zero FOUC, full RTL/LTR compatibility, and dual Light/Dark mode parity.
- **Consequences:** Dramatically cleaner Blade templates, dry markup, centralized table design adjustments, consistent pagination and empty states across the entire application, and 100% passing test suite (126/126 tests).

---

## [ADR-021] Unified Table Action Buttons Architecture
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Table row actions (View, Edit, Delete, Inspect, Download) historically relied on arbitrary button implementations with inline SVGs, inconsistent padding, and mixed styling. A unified design system requires standardized, semantic table action button components that maintain strict color mappings, accessibility, and visual consistency across all data tables.
- **Decision:**
  1. **Standardized Reusable Action Components (`resources/views/components/table/`):**
     - `<x-table.actions>`: Row action wrapper with `inline-flex items-center gap-1.5 whitespace-nowrap`.
     - `<x-table.action>`: Core polymorphic component supporting `href` (renders `<a>` with role="button" or `<button>`), automatic semantic themes, SVG icons, and dual display modes (icon-only with `sr-only` accessibility text and tooltips when empty, or icon + text when label is passed).
     - `<x-table.action-view>`: Semantic Indigo action button with eye icon for inspecting records, viewing payloads, and examining diffs.
     - `<x-table.action-edit>`: Semantic Amber action button with pencil icon for updating and modifying records.
     - `<x-table.action-delete>`: Semantic Rose/Red action button with trash icon for destructive deletions, terminations, and error stack trace inspections.
  2. **Refactoring Existing System Tables:**
     - Replaced custom buttons and inline SVGs with `<x-table.actions>` and `<x-table.action-*>` in `activity-log.blade.php`, `notifications.blade.php`, and `queues.blade.php`.
  3. **Strict UI Enforcement:**
     - Never write raw `<button>` or `<a>` elements for table row operations. Always use the `<x-table.action-*>` suite.
- **Consequences:** Highly consistent, reusable table row action design, zero inline SVGs repeated across views, full Dark Mode and RTL/LTR compatibility, and 100% passing test suite.

---

## [ADR-022] Context-Aware CRUD & Action Button Placement Strategy
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** When expanding administrative controls across the 13 tables of the System & Database Tables Explorer, generic CRUD operations (Create, Edit, Delete) cannot be uniformly applied across all tables without violating domain semantics and forensic integrity. Specifically:
  - An immutable audit trail (`activity_log`) must never permit creation, editing, or selective row deletion, as this would compromise forensic evidentiary standards.
  - Runtime worker states (`jobs`, `cache_locks`, `sessions`) are managed by backend daemons; arbitrary edits would induce concurrency deadlocks or desynchronization.
- **Decision:**
  1. **Domain-Restricted Full CRUD:**
     - Reserved full Create, Edit, and Delete actions exclusively for entity/catalog tables: `users`, `roles`, and `permissions`.
     - Creation CTAs are placed in the table or section toolbar (`<x-primary-button>`).
     - Modification and deletion actions are placed in table row actions (`<x-table.action-edit>` and `<x-table.action-delete>`).
  2. **Context-Specific Action Semantics:**
     - `sessions`: Terminate Session (`<x-table.action-delete :title="__('Terminate Session')">`).
     - `jobs`: Cancel Job (`<x-table.action-delete :title="__('Cancel Job')">`).
     - `failed_jobs`: View Stack Trace (`<x-table.action-view>`), Retry Job (`<x-table.action type="primary" :title="__('Retry Job')">`), Delete Record (`<x-table.action-delete :title="__('Delete Record')">`).
     - `job_batches`: Delete Batch (`<x-table.action-delete :title="__('Delete Batch')">`).
     - `cache`: Forget Key (`<x-table.action-delete :title="__('Forget Key')">`).
     - `cache_locks`: Release Lock (`<x-table.action-delete :title="__('Release Lock')">`).
     - `notifications`: View Payload (`<x-table.action-view>`), Delete Notification (`<x-table.action-delete :title="__('Delete Notification')">`).
     - `activity_log`: Exclusively read-only (`<x-table.action-view>`) to safeguard tamper-proof forensic immutability.
  3. **Visual & Layout Uniformity:**
     - Standardized right-aligned action column header (`<x-table.th class="text-end">{{ __('Actions') }}</x-table.th>`) and row container (`<x-table.td class="whitespace-nowrap text-end"><x-table.actions class="justify-end">...`).
     - Empty states updated with calibrated `colspan` counts across all tables.
- **Consequences:** Guarantees architectural integrity and audit immutability while delivering a unified, intuitive management experience for system administrators.

---

## [ADR-023] Stateful Unified Global Filter Architecture (`<x-global-filter />`)
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Search and filtering functionality across tables originally relied on scattered, ad-hoc `<form>` and `<input>` markup. This created duplicated styling, inconsistent reset behaviors, and fragile query parameter handling during pagination and language switching. Furthermore, security concerns regarding data leakage had to be rigorously addressed.
- **Decision:**
  1. **Zero Data Leakage Architectural Isolation:**
     - Clarified and formalized that `<x-global-filter />` is purely a presentation layer (Dumb UI component).
     - The component never queries models or databases directly; it strictly sends standard HTTP GET parameters to the specific caller route (`action="..."`).
     - Backend queries are handled exclusively by the corresponding Controller and Service layer, safeguarded by route-level authorization and `FilterableTrait` column whitelisting (`$filterable`, `$searchable`).
  2. **Standardized Component Suite (`resources/views/components/global-filter/` and `global-filter.blade.php`):**
     - `<x-global-filter>`: Root wrapper managing Alpine.js reactive state, built-in search with instant clear button (`x-show="searchQuery"`), slot for custom filter controls, optional sorting slot, submit button, and automatic reset button shown only when active filters exist.
     - `<x-global-filter.search>`: Standalone/composable search input with SVG icon and clear toggle.
     - `<x-global-filter.select>`: Standardized dropdown supporting array/collection option binding, dark mode styles, and optional auto-submit (`auto-submit="true"`).
     - `<x-global-filter.sort>`: Compact sorting selector with ascending/descending toggle, seamlessly mapped to `sort_by` and `sort_direction`.
  3. **State Preservation & Query Continuity:**
     - Query parameters are treated as the single source of truth for filter state.
     - Pagination links preserve active filters via `->withQueryString()`.
     - Language switching preserves query parameters via `LaravelLocalization::getLocalizedURL()`.
     - Reset button clears filters back to the clean base route without dropping required path parameters.
  4. **Strict UI Enforcement (Rule 12 & Rule 14):**
     - Zero inline styles (`style="..."`).
     - Integration with standardized `<x-primary-button>` and `<x-secondary-button>`.
     - 100% Dark Mode, responsive, and RTL/LTR parity.
- **Consequences:** Eliminates boilerplate filter code across views, guarantees zero data leakage, provides persistent state across pagination and language switches, and passes 133/133 tests with 100% success.

---

## [ADR-024] Trilingual Localization Integrity & Synchronization Protocol
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** While the application was built on a trilingual architecture (`ar`, `en`, `fr`), an exhaustive audit of all Blade templates revealed that 62 translation keys (primarily within the administrative and database inspection modules) were present in English source code inside `__('...')` calls, but lacked explicit entries in `lang/ar.json` and `lang/fr.json`. In Laravel, missing keys default to returning the key string itself, causing English text fallback in Arabic and French views.
- **Decision:**
  1. **Exhaustive Automated Template Scanning:**
     - Developed audit scripts parsing all Blade templates in `resources/views/` via regex extraction of `__('...')` and `@lang('...')`.
     - Confirmed zero hardcoded strings outside translation functions across all UI views.
     - Identified and wrapped remaining inline UI strings in `resources/views/system/cache.blade.php`.
  2. **Trilingual Dictionary Normalization & Synchronization:**
     - Created comprehensive, verified Arabic and French translation mappings for all 64 database inspection and system terms (including specialized technical terminology such as TTL, Atomic Locks, UUID, User Agent, Stack Trace, etc.).
     - Implemented bidirectional synchronization ensuring that all three root language files (`lang/ar.json`, `lang/en.json`, `lang/fr.json`) maintain 100% key parity (186 identical keys in each).
  3. **Continuous Audit Gate:**
     - Automated verification script confirms `Missing in AR: 0`, `Missing in FR: 0`, `Missing in EN: 0`.
- **Consequences:** Eliminates untranslated English fallbacks across all administrative and user pages, achieving true 100% trilingual parity in Arabic, English, and French across the entire web application.

---

## [ADR-025] Unified Automated Data Pruning & Lifecycle Management System
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Over time, operational and telemetry tables (specifically `activity_log`, `notifications`, and `failed_jobs`) accumulate extensive volumes of historical records. Without lifecycle pruning, excessive data accumulation leads to degraded query speeds, bloated database snapshots, and memory exhaustion. However, pruning must be strictly controlled to prevent accidental data loss in sovereign tables (such as `users`, `roles`, and `permissions`).
- **Decision:**
  1. **Centralized Architectural Service (`App\Services\DataPruningService`):**
     - Implements dual pruning criteria: Date-based retention (`retention_days`) and maximum capacity count pruning (`max_records`).
     - Executes chunked batch deletion (default 1,000 records per iteration) using primary key ID chunking to eliminate memory spikes and database lock contention.
     - Supports simulation via `--dry-run` mode.
  2. **Hardcoded Immutable Sovereign Blacklist:**
     - Prohibits automated pruning of sovereign tables (`users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `migrations`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`).
     - Any attempt to target a protected table immediately throws an `InvalidArgumentException`.
  3. **Audit Trail Transparency:**
     - Deletion runs automatically record an event to `spatie/laravel-activitylog` (`log_name = 'data_pruning'`) documenting the exact number of purged records and applied criteria.
  4. **Configuration & Scheduling:**
     - Managed via [config/pruning.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/config/pruning.php) with environment variable overrides.
     - Scheduled in [routes/console.php](file:///d:/HARD%20Project/tools.gmtm-dz.com/routes/console.php) to execute daily at 02:00 midnight via `php artisan data:prune` without overlapping.
- **Consequences:** Provides automated, bounded database growth, high performance, complete audit visibility, and 100% test coverage (142/142 tests passing).

---

## [ADR-026] Manual Settings & Control Dashboard for Automated Data Pruning
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Following the initial implementation of the automated data pruning system, system administrators needed a dynamic, user-friendly control interface to adjust lifecycle policies (retention days, max records, engine toggles) in runtime without having to modify server-side `.env` or `config/pruning.php` files directly. Furthermore, administrators required on-demand simulation (`--dry-run`) and manual prune triggers directly from the web interface.
- **Decision:**
  1. **Persistent Database Storage (`system_settings` Table & `SystemSetting` Model):**
     - Stored JSON-encoded custom overrides in a dedicated `system_settings` table (`key = 'data_pruning_settings'`).
     - Integrated 1-hour Cache caching with automatic cache invalidation on updates.
     - Implemented graceful fallback in `DataPruningService::getEffectiveConfig()`: if no custom settings exist in database, configuration defaults from `config/pruning.php` are seamlessly utilized.
  2. **Security Quarantine & Verification (Rule 13 Compliance):**
     - Confirmed by developer that the dashboard belongs to the high-security system module.
     - Registered under `/system-tables/pruning` protected by `auth` and `verified` middleware.
  3. **Strict UI & Component Reuse (Rule 12, 14, 15):**
     - Sidebar tab integrated in `<x-system-tabs active="pruning" />`.
     - Standardized Action Buttons: `<x-primary-button>` for saving, `<x-info-button>` for dry-run simulation, `<x-danger-button>` for immediate prune with confirmation modal, `<x-secondary-button>` for cancel/reset.
     - Standardized Table Component `<x-table>` for the pruning audit history.
     - Zero inline styles.
  4. **Strict Trilingual Parity (Rule 17):**
     - Synchronized 58 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (256 keys each, 0 missing).
- **Consequences:** Provides complete operational control to administrators, persistent and auditable configuration changes, on-demand simulations, and 100% passing test suite (148/148 tests).

---

## [ADR-027] Dynamic Database Table Onboarding for Automated Data Pruning & Lifecycle Management
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** While the automated pruning engine supported built-in telemetry tables (`activity_log`, `notifications`, `failed_jobs`), administrators requested the ability to onboard **any arbitrary database table** in the schema (e.g. analytics events, audit logs, audit revisions, temporary staging data) to the lifecycle management service dynamically through the web interface, while maintaining strict sovereign protection over security-critical tables.
- **Decision:**
  1. **Multi-Driver Schema Discovery & Prefix Normalization:**
     - Added `DataPruningService::getAvailableDatabaseTables()` supporting both MySQL (`SHOW TABLES FROM ...`) and SQLite (`Schema::getTableListing()`).
     - Normalized table names by stripping database/schema prefixes (`main.*`, `gmtmdz_tools.*`), guaranteeing reliable cross-engine behavior.
     - Implemented `DataPruningService::getEligibleTablesForPruning()` which discovers non-configured, non-sovereign tables, auto-suggests primary keys (`id`) and timestamp columns (`created_at`, `failed_at`, `logged_at`, `record_date`, etc.), and calculates live record counts.
  2. **Strict Sovereign Blacklist Safeguard (Immutable):**
     - Hardcoded blacklist constant `IMMUTABLE_PROTECTED_TABLES` (`users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `migrations`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`) is checked both in FormRequest (`AddPruningTableRequest`) and within `DataPruningService::addCustomTable()`.
     - Attempting to target a sovereign table throws an `InvalidArgumentException` and is blocked prior to any database operation or configuration mutation.
  3. **Custom Table Lifecycle Configuration & Detachment:**
     - Dynamically onboarded tables are stored in `system_settings` under `data_pruning_settings.tables.{table}` with metadata (`primary_key`, `date_column`, `retention_days`, `max_records`, `is_custom = true`).
     - Built-in tables (`activity_log`, `notifications`, `failed_jobs`) have `is_custom = false` and cannot be deleted via the UI, though they can be enabled/disabled.
     - Custom tables can be removed via `DELETE /system-tables/pruning/tables/{table}` (`DataPruningService::removeCustomTable()`), which purges their configuration entry and logs an activity audit record.
  4. **Strict UI & Localization Compliance (Rules 13, 14, 15, 17):**
     - Quarantined in high-security system module under `/system-tables/pruning/tables`.
     - "Add Table to Pruning" modal implemented via Alpine.js with reactive column auto-selection and sovereign security notice.
     - Unified delete actions (`<x-table.action-delete>`) for custom table cards.
     - Synchronized 17 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (273 keys each, 1-to-1 parity, 0 missing).
  5. **Automated Verification:**
     - Added feature test suite `DynamicTablePruningTest.php` with 6 tests verifying sovereign exclusion, custom table onboarding, dual date/capacity pruning execution, and custom table deletion (all 154 tests passing across project).
- **Consequences:** Provides ultimate operational flexibility to onboard any table to lifecycle management while maintaining bulletproof data integrity on sovereign user and security data.

---

## [ADR-028] Database Backups & Point-in-Time Disaster Recovery System
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** While automated cron backups were configured in `routes/console.php` using `spatie/laravel-backup`, system administrators lacked an interactive, secure web management interface to inspect existing backup archives, generate instant database dumps, download compressed backups, delete obsolete snapshots, and perform point-in-time state restorations (including a dedicated one-click option to roll back to the earliest recorded snapshot point).
- **Decision:**
  1. **Centralized Service Architecture (`DatabaseBackupService.php`):**
     - Wrapped Spatie's `BackupDestination` to discover, parse, and sort all backup zip archives stored on the `local` disk (`storage/app/private/Laravel`).
     - Enhanced snapshots collection with formatted sizes, relative ages (`diffForHumans`), and explicit classification tags: `is_oldest = true` on the earliest snapshot and `is_newest = true` on the most recent.
     - Implemented `createBackup()` invoking `backup:run --only-db` with notification suppression and activity log auditing.
  2. **Strict Security & Path Traversal Safeguards:**
     - Enforced `sanitizeFileName()` prohibiting path traversal tokens (`/`, `\`, `..`) and enforcing valid `.zip` extensions before resolving relative paths or interacting with disk storage.
  3. **Safe Point-in-Time Restoration Engine:**
     - Extracted SQL dumps dynamically using PHP's native `ZipArchive` into isolated temporary storage (`storage/app/backup-temp/restore_*`).
     - Detected active database driver: on MySQL connections, executed `SET FOREIGN_KEY_CHECKS=0` and restored with `SET FOREIGN_KEY_CHECKS=1` in a `finally` block to prevent foreign key order dependency errors during mass schema/data reconstitution.
     - Executed database dump statements via `DB::unprepared($sqlContent)`.
     - Automatically purged temporary extraction directories upon completion.
     - Provided `restoreOldestBackup()` convenience method resolving the oldest available snapshot and restoring it.
  4. **Security Quarantine (Rule 13 Compliance):**
     - Confirmed with developer that backup operations belong exclusively to the quarantined system module.
     - Registered routes under `/system-tables/backups` guarded by `auth` and `verified` middleware.
  5. **Standardized UI, Modals & Trilingual Localization (Rules 12, 14, 15, 17):**
     - Embedded in `<x-system-tabs active="backups" />`.
     - Standardized Action Buttons: `<x-primary-button>` for instant backup creation, `<x-warning-button>` for quick rollback to oldest snapshot, `<x-table.action-view>` / `<x-table.action-delete>` for row actions.
     - Standardized Table Component: `<x-table>` with download, restore, and delete actions.
     - Interactive Alpine.js confirmation modal in prominent red alerting administrators to irreversible database overwrites.
     - Synchronized 41 translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (314 keys each, 0 missing).
  6. **Automated Verification:**
     - Created comprehensive Feature test suite `tests/Feature/DatabaseBackupTest.php` (11 tests, 57 assertions) covering guest redirects, empty states, snapshot tagging, download, delete, restore, restore-oldest, and path traversal rejection.
- **Consequences:** Provides full disaster recovery capabilities, safe point-in-time restorations, tamper-proof forensic logging, and 100% test coverage across the entire application (165/165 tests passing, 631 assertions).

---

## [ADR-029] Administrative User Creation System in System Tables Explorer
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** In the System Tables Users Explorer (`resources/views/system/users.blade.php`), the "New User" button was an inert UI placeholder without a functioning interactive modal or backend submission endpoint. System administrators needed the ability to quickly onboard and create new user accounts, assign initial roles, set credentials securely, and audit the creation event.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Prompted developer with mandatory Rule 13 question and received explicit confirmation.
     - Kept all endpoints quarantined under `/system-tables/users` (`POST`) protected by `auth` and `verified` middleware.
  2. **Form Request & Input Validation:**
     - Created `CreateSystemUserRequest` enforcing string sanitization, unique email across `users` table, confirmed password rules, and existence checks for assigned roles.
  3. **Domain Layer & Transaction Management:**
     - Injected `UserService` into `SystemTableController` and executed creation through `UserService::register()` within database transactions.
     - Bound Spatie role assignment (`$user->assignRole($role)`).
     - Recorded audit trail entry in `spatie/laravel-activitylog` (`log_name = 'system_users'`).
  4. **Standardized UI, Modals & Trilingual Localization (Rules 12, 14, 15, 17):**
     - Built an Alpine.js modal with dynamic role selection populated from `SystemTableService::getRoles()`.
     - Retained error state on validation failures (`showCreateModal: {{ $errors->any() ? 'true' : 'false' }}`).
     - Standardized Action Buttons: `<x-primary-button>` and `<x-secondary-button>`.
     - Synchronized 10 translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (324 keys each, 0 missing).
  5. **Automated Verification:**
     - Extended `tests/Feature/SystemTableTest.php` covering guest access rejection, admin user creation with roles, and validation bounds. (Overall test suite: 168/168 tests passing, 643 assertions).
- **Consequences:** The "New User" button is now fully functional, securely wired to the backend with audit trails, and provides a polished, interactive modal.

---

## [ADR-030] Administrative User Editing System in System Tables Explorer
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Following the implementation of user creation, the row-level "Edit User" button (`<x-table.action-edit>`) in the Users Explorer remained an inert placeholder. System administrators needed an intuitive way to modify user names, emails, passwords, and assigned roles directly from the interface.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Prompted developer with mandatory Rule 13 question and received explicit confirmation.
     - Registered route `PUT /system-tables/users/{user}` (`system-tables.users.update`) protected by `auth` and `verified` middleware.
  2. **Form Request & Input Validation:**
     - Created `UpdateSystemUserRequest` validating name and email (ignoring current user ID via `Rule::unique('users')->ignore($userId)`).
     - Made password updates optional (`nullable`), requiring confirmation and minimum strength standards when provided.
     - Enforced valid role existence checks.
  3. **Domain Layer & Activity Logging:**
     - Implemented `updateUser` in `SystemTableController` with conditional password hashing and Spatie `syncRoles()`.
     - Logged audit record in `spatie/laravel-activitylog` (`log_name = 'system_users'`).
  4. **Standardized UI, Modals & Trilingual Localization (Rules 12, 14, 15, 17):**
     - Attached `@click="openEditModal(...)"` to `<x-table.action-edit>`.
     - Built an Alpine.js modal with pre-populated values, dynamic role selection, and optional password fields.
     - Synchronized 9 translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (333 keys each, 0 missing).
  5. **Automated Verification:**
     - Added 3 feature tests in `tests/Feature/SystemTableTest.php` verifying guest protection, successful admin updates, and email uniqueness rules (Overall test suite: 171/171 tests passing, 654 assertions).
- **Consequences:** Provides a complete, secure, audited user editing experience directly from the system tables interface.

---

## [ADR-031] Administrative Role and Permission Creation System in System Tables Explorer
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** In the Roles & Permissions Explorer (`resources/views/system/roles.blade.php`), both the "New Role" button and "New Permission" button were inert UI placeholders without interactive modals or backend submission endpoints. System administrators needed the ability to create new Spatie RBAC roles (with optional permission assignments) and create new Spatie permissions in the system catalog.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Developer provided explicit authorization ("نعم") confirming this addition belongs exclusively to the quarantined system module.
     - Registered routes `POST /system-tables/roles` (`system-tables.roles.store`) and `POST /system-tables/permissions` (`system-tables.permissions.store`) protected by `auth` and `verified` middleware.
  2. **Form Requests & Input Validation:**
     - Created `CreateRoleRequest` enforcing unique role names in the `roles` table (with `web` guard) and optional permissions array where all permission names must exist in `permissions`.
     - Created `CreatePermissionRequest` enforcing unique permission names in the `permissions` table (with `web` guard).
  3. **Domain Layer & RBAC Synchronization:**
     - Added `storeRole` in `SystemTableController` creating the role with `guard_name = 'web'`, syncing permissions via `$role->syncPermissions()`, and logging to `spatie/laravel-activitylog` (`log_name = 'roles_permissions'`).
     - Added `storePermission` in `SystemTableController` creating the permission with `guard_name = 'web'` and logging to `spatie/laravel-activitylog` (`log_name = 'roles_permissions'`).
  4. **Standardized UI, Alpine Modals & Trilingual Localization (Rules 12, 14, 15, 17):**
     - Attached `@click="showRoleModal = true"` on the New Role button and `@click="showPermissionModal = true"` on the New Permission button.
     - Built two distinct Alpine.js modals (`showRoleModal` and `showPermissionModal`) with form type detection (`form_type = 'role'` or `'permission'`) ensuring the correct modal stays open when validation errors occur.
     - Created a scrollable checkbox grid of all existing permissions inside the Role creation modal.
     - Standardized Action Buttons: `<x-primary-button>` and `<x-secondary-button>`.
     - Absolute prohibition of inline styles.
     - Synchronized 15 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (348 keys each, exact 1-to-1 key parity, 0 missing).
  5. **Automated Verification:**
     - Added 4 feature tests in `tests/Feature/SystemTableTest.php` covering guest protection, admin role creation with permissions, admin permission creation, and unique name validation rules.
     - Entire application test suite passes with 100% success (175/175 tests, 674 assertions).
- **Consequences:** Both "New Role" and "New Permission" buttons are now fully functional, secure, audited, localized across 3 languages, and comprehensively tested.

---

## [ADR-032] Administrative Role and Permission Editing System in System Tables Explorer
- **Date:** 2026-09-08
- **Status:** Accepted / Active
- **Context:** Following the implementation of role and permission creation, the row-level "Edit Role" button on role cards and "Edit Permission" button in the permissions table of `resources/views/system/roles.blade.php` remained inert placeholders. System administrators needed the ability to modify role names, reassign granted permissions via a dynamic checkbox grid, and modify permission names in the catalog.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Developer provided explicit authorization ("نعم") confirming this addition belongs exclusively to the quarantined system module.
     - Registered routes `PUT /system-tables/roles/{role}` (`system-tables.roles.update`) and `PUT /system-tables/permissions/{permission}` (`system-tables.permissions.update`) protected by `auth` and `verified` middleware.
  2. **Form Requests & Input Validation:**
     - Created `UpdateRoleRequest` validating role name uniqueness while ignoring the current role ID (`Rule::unique('roles', 'name')->ignore($roleId)`) and validating the optional `permissions` array.
     - Created `UpdatePermissionRequest` validating permission name uniqueness while ignoring the current permission ID (`Rule::unique('permissions', 'name')->ignore($permissionId)`).
  3. **Domain Layer & RBAC Synchronization:**
     - Added `updateRole` in `SystemTableController` updating the role and syncing permissions with `$role->syncPermissions()`, auditing changes to `spatie/laravel-activitylog` (`log_name = 'roles_permissions'`).
     - Added `updatePermission` in `SystemTableController` updating the permission name, auditing changes to `spatie/laravel-activitylog` (`log_name = 'roles_permissions'`).
  4. **Standardized UI, Modals & Trilingual Localization (Rules 12, 14, 15, 17):**
     - Wired `<x-table.action-edit>` triggers in role cards and permission rows with `@click="openEditRoleModal(...)"` and `@click="openEditPermissionModal(...)"`.
     - Built two distinct Alpine.js modals (`showEditRoleModal` and `showEditPermissionModal`) with reactive permission checkbox management (`isPermissionSelected`, `togglePermission`), pre-populated fields, and sticky validation error retention.
     - Standardized Action Buttons: `<x-primary-button>` and `<x-secondary-button>`.
     - Synchronized 6 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (354 keys each, exact 1-to-1 key parity, 0 missing).
  5. **Automated Verification:**
     - Added 4 feature tests in `tests/Feature/SystemTableTest.php` covering guest protection, admin role updates with permission synchronization, admin permission name updates, and unique name validation rules ignoring self.
     - Entire application test suite passes with 100% success (179/179 tests, 694 assertions).
- **Consequences:** Provides a complete, secure, audited editing capability for both Spatie roles and permissions directly from the interface.

