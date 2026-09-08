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




