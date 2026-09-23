<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.4. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files & Living Memory (Token-Efficient Protocol)
- Do not create arbitrary scratch markdown files unless requested.
- **Smart Pre-Flight:** Inspect `docs/project_state.md` for active schema and system state. Do NOT read `docs/ARCHITECTURE_LOG.md` or `docs/changelog.md` up front; read specific ADRs or changelog entries only when relevant to the active task domain.
- **Proportional Post-Flight:** Always log concise entries in `docs/changelog.md`; update `docs/project_state.md` only when models, schema, routes, services, or commands change; record ADRs in `docs/ARCHITECTURE_LOG.md` ONLY for major structural decisions (no ADRs for bug fixes or UI tweaks).
- **Token-Economy Response Directive:** Never echo or dump full document contents into chat responses. Output only the file path and a concise bullet-point diff summary (max 3-5 lines).

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

## Strict Separation of Concerns (Backend, Frontend & CSS)
- **Zero Code Mixing:** Never mix Backend logic, Frontend markup, and CSS styling within the same file or block. Each layer must remain strictly isolated.
- **Backend Isolation (`app/`, `routes/`, `database/`):** 
  - Pure PHP and Laravel logic only. 
  - No HTML tags, inline styles, or frontend scripts inside Controllers, Actions, Services, or Models.
- **Frontend Isolation (`resources/views/`, `resources/js/`):** 
  - UI markup (Blade, Vue, React, or Livewire components) must reside exclusively in designated frontend directories.
  - No database queries, Eloquent calls, or business logic inside frontend views. Pass data strictly via controllers and API Resources/View data.
- **CSS & Styling Isolation (`resources/css/`, Tailwind, etc.):** 
  - **No Inline Styles:** The use of the `style="..."` attribute in HTML/Blade is strictly prohibited.
  - **Utility-First / External CSS:** Use Tailwind CSS utility classes directly in markup, or write clean, separate CSS/SCSS files inside `resources/css/`. 
  - Never inject `<style>` blocks or raw CSS rules inside PHP files or JavaScript components unless architecturally required (and even then, keep it isolated).
- **Multi-File Response Mandate:** When a feature requires both backend changes and frontend/CSS changes, output them as completely separate code blocks with distinct file paths.

## Mandatory Trilingual Localization Across All Views & Features (Strict UI & Language Rule)
1. **Absolute Mandate: 100% English Translation Master Keys (إلزام كتابة كل مفاتيح الترجمة بالإنجليزية حصراً):**
   - **In Code:** Every single translation key passed to `__('...')`, `@lang('...')`, Form Requests, validation attributes, status flashes, or notification messages MUST be written in English as a single unified master key language.
   - **In JSON Dictionaries:** In all three dictionary files (`lang/en.json`, `lang/ar.json`, `lang/fr.json`), the **key** (JSON property name on left side of `:`) MUST strictly and exclusively be in English.
   - **Zero Tolerance Prohibition:** Using Arabic, French, or any non-English string as a dictionary key or inside `__('...')` in code is strictly prohibited. Arabic and French text may ONLY appear as the translated values in `ar.json` and `fr.json`.
2. **3-Language Dictionary Parity:** Every translation key MUST be present in `lang/en.json`, `lang/ar.json`, and `lang/fr.json` simultaneously, with 100% exact 1-to-1 key parity and identical English key names.
3. **Zero Hardcoded Strings:** No raw unlocalized user-facing strings in views or backend responses. Everything must use `__('...')`.

## Mandatory RBAC Authorization & Static Permissions Registry Integration (Strict Rule)
1. **Mandatory Permission Shield:** Every new service, module, route, or new page MUST be integrated with and protected by RBAC permissions (`permission:...` middleware, `$this->authorize()`, `@can`).
2. **Mandatory Static Catalog Registration:** Any new service or page entity MUST be formally declared in `config/permissions.php` under `$groups` and synchronized with `php artisan permissions:sync-tables`.
3. **Mandatory Developer Clarification Protocol:** If the AI assistant is unsure of the exact permission names or grouping for the new service/page, it MUST ask the developer before proceeding:
   > *"ما هي أسماء وصيغ الصلاحيات المعتمدة لهذه الخدمة/الصفحة الجديدة لإضافتها إلى القائمة الثابتة (`config/permissions.php`)؟"*

## Mandatory Media Processing, CAS Deduplication & Zero Disk Space Leak Engine (Strict Architecture & Storage Rule)
Every AI agent writing, modifying, or extending file uploads, image handling, document attachments, avatar management, or file deletion in this project is strictly mandated to adhere to the media optimization and storage protocol:
1. **Mandatory Centralized Gateway (`MediaOptimizationService`):**
   - Direct, uncompressed file storage via raw Laravel methods (e.g. `$request->file('...')->store('...')`, `Storage::putFile(...)`) is **strictly forbidden**.
   - All uploaded images, avatars, profile photos, documents, and media attachments MUST pass through `app(MediaOptimizationService::class)`:
     - `optimizeImage($file, $directory, $disk)` for images.
     - `optimizePdf($file, $directory, $disk)` for PDF documents.
     - `optimize($file, $directory, $disk)` for automatic MIME-based routing.
2. **Compulsory WebP Conversion & Image Compression Standard:**
   - All uploaded images (JPEG, PNG, JPG, etc.) must be compulsory converted to **WebP** format.
   - Quality must be set to **80%**.
   - Width must be proportionately scaled down capped at **1920px** (Full HD standard) using `scaleDown(width: 1920)`. Upscaling smaller images is strictly prohibited.
   - All EXIF, camera metadata, and GPS geolocation data must be stripped for privacy and storage optimization (`strip: true`).
3. **Ghostscript PDF Optimization:**
   - PDF documents must be processed via Ghostscript (`gs`) using the `/ebook` profile (150dpi resolution, compatibility 1.4).
   - Resilient fallback (`fallback_to_original`) is enforced if Ghostscript is unavailable on the host.
4. **Mandatory Content-Addressable Storage (CAS) Deduplication:**
   - All stored media artifacts must be named using their cryptographic SHA-256 content hash (`{sha256}.webp` / `{sha256}.pdf`).
   - If an identical image or document is uploaded (by the same user or different users), the system MUST detect the existing physical file on disk and reuse its path immediately, creating zero redundant duplicate files on disk.
5. **Mandatory Reference-Aware Safe Deletion Protocol:**
   - Direct, unconditional deletion from disk via raw `Storage::delete($path)` is **strictly forbidden** for shared media assets.
   - Before any file is unlinked from storage, the system MUST check whether other users or database entities still reference that path using `MediaOptimizationService::isAssetInUse($path, $excludeEntityId)` or invoke `MediaOptimizationService::safeDelete($path, $disk, $excludeEntityId)`.
   - If other records in the system are still referencing the file, the physical file on disk MUST BE PRESERVED.
   - The physical file on disk is only permanently deleted (`Storage::delete()`) when the active reference count reaches zero.
6. **Strict "Process & Destroy" Protocol (The Kill-Step):**
   - Temporary upload copies and intermediate scratch files in `storage/app/temp-media` or system temp must be immediately and permanently destroyed via `@unlink()` (`destroyTempFiles()`) upon completion.
   - Zero raw, unoptimized, or temporary files may linger on the server disk.

## Mandatory Single Source of Truth for Colors, Badge System & Semantic Functional Classification (Strict UI Rule)
Every AI agent writing, modifying, or refactoring UI views, badge chips, status indicators, or PHP Enums in this project is strictly mandated to adhere to the color unification and functional classification protocol:
1. **Single Source of Truth (`<x-badge>` & `tokens.css`):**
   - The project's unified design system (`resources/css/tokens.css` and `resources/views/components/badge.blade.php`) is the sole authoritative source of truth for colors and badges.
   - Writing raw `<span>` elements with ad-hoc Tailwind color classes (e.g. `bg-amber-50 ... dark:bg-amber-950/60`, `bg-cyan-50`, `border-emerald-500/30`) is **strictly forbidden**.
   - All badges, status labels, job position chips, and category tags across the entire application **MUST** exclusively use the unified `<x-badge>` component (`<x-badge :variant="...">`).
   - **Pure PHP Isolation in Enums:** PHP Enums must never return raw HTML or CSS class strings (e.g. `badgeClass()`). Instead, Enums must strictly declare a `badgeVariant(): string` method returning a recognized semantic token (`primary`, `info`, `neutral`, `success`, `danger`, `warning`).
2. **Mandatory Functional Role & Hierarchy Classification (No "Rainbow / Confetti UI"):**
   - Positions, roles, and categories must strictly be classified into coherent functional tiers rather than assigning arbitrary random colors:
     - **Management & Executive Leadership (`isManagement()`):** Must use the **`primary`** variant (`bg-brand-600/10 text-brand-800 dark:text-brand-300 border-brand-500/20`), representing the official GMTM corporate industrial brand identity.
     - **Engineering & Specialist Roles (`isEngineer()`):** Must use the **`info`** variant (`bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20`), representing technical domain authority.
     - **Field Operations & Technicians (`isTechnician()`):** Must use the **`neutral`** variant (`bg-gray-500/10 text-gray-700 dark:text-gray-300 border-gray-500/20 dark:border-gray-600/30`), maintaining a clean, operational baseline.
3. **Decoupling Operational State/Status from Role/Position:**
   - Operational states (Active, Inactive, Suspended, On Leave, Pending, Completed, Failed) must never be visually confused with functional job positions.
   - Status indicators must use state semantics paired with status dots (`:dot="true"`):
     - `success` (Emerald / Brand) with dot for Active / Completed.
     - `danger` (Rose / Red) with dot for Inactive / Suspended / Failed.
     - `warning` (Amber) with dot for On Leave / Pending / Paused.
4. **Harmonized Light & Dark Mode Standards:**
   - All badges and status elements must rely on curated alpha-transparency tokens (`bg-*/10`, `border-*/20`, `text-* dark:text-*`) managed centrally by `<x-badge>`.
   - Never inject opaque dark classes like `dark:bg-*-950/60` or manual dark overrides that break contrast or look muddy/jarring.
   - Interactive form inputs, select dropdowns, and search filters must strictly use brand-aligned focus states (`focus:border-brand-600 focus:ring-brand-600`) across both light and dark themes.

## Mandatory Unified Search, Filter & State Persistence Architecture (Strict Rule)
Every AI agent writing, modifying, or refactoring table views, repositories, controllers, or filter components in this project is strictly mandated to adhere to the unified search, filtering, and query state persistence protocol:
1. **Single Source of Truth for Search & Filters (`<x-global-filter>`):**
   - The `<x-global-filter>` component is the sole authoritative standard for all table search inputs, status/position dropdowns, and date-range filters across the entire application.
   - Writing ad-hoc, unstyled search bars or custom one-off filter containers is **strictly forbidden**.
2. **Mandatory Functional Role Grouping in Selectors (`<optgroup>`):**
   - In all position filters, dropdowns, and create/edit modal selects, job positions MUST be grouped into the 3 standardized functional tiers using `<optgroup>`:
     - `<optgroup label="{{ __('Management & Executive Leadership') }}">` (`isManagement()`)
     - `<optgroup label="{{ __('Engineering & Specialist Roles') }}">` (`isEngineer()`)
     - `<optgroup label="{{ __('Field Operations & Technicians') }}">` (`isTechnician()`)
   - Flat, unorganized, or arbitrary position lists in dropdowns are strictly prohibited.
3. **Mandatory Filter & Search State Persistence Across All Lifecycle Events:**
   Active search queries and filter parameters (`search`, `position`, `status`, `from_date`, etc.) MUST be permanently preserved without data loss across all operations:
   - **Language Switching (تغيير اللغة):** The language switcher component must retain all active query parameters (`LaravelLocalization::getLocalizedURL($localeCode, null, [], true)`).
   - **Pagination Navigation (رقم صفحة الجدول):** All repositories and controllers returning paginated results MUST append `->withQueryString()` to the paginator instance. Navigating between pages must never strip active search or filter criteria.
   - **Record Creation (الإنشاء الجديد):** Modal form actions must pass active query parameters (`route('...', request()->query())`), and controller store actions must redirect back preserving `$request->query()`.
   - **Record Editing (التعديل):** Modal edit form actions must pass active query parameters (`route('...', array_merge(['model' => $id], request()->query()))`), and controller update actions must redirect back preserving `$request->query()`.
   - **Record Deletion (الحذف):** Delete confirmation forms and modal action URLs must pass active query parameters, and controller destroy actions must redirect back preserving `$request->query()`.

## Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity (Strict Rule)
Every AI agent creating, modifying, or refactoring user alerts, session messages, notifications, or activity feeds in this project is strictly mandated to adhere to the unified alert and notification architecture:
1. **Single Source of Truth for In-Page Alerts (`<x-alert>`):**
   - All in-page alerts, operational notices, and flash feedback MUST exclusively use the `<x-alert>` component (`<x-alert :variant="...">`).
   - Ad-hoc alert boxes, raw `<div>` containers with custom background colors, and inline styles are strictly prohibited.
   - Semantic Variants Standard: `success` (Emerald Green), `danger` (Rose Red), `warning` (Amber Yellow), `info` (Indigo Blue), `primary` (Brand Green).
   - Standardized Session Flash Keys: Controllers must strictly pass standardized session keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, and `with('info', __('...'))`. Views rendering session messages must map these keys directly into the corresponding `<x-alert>` variant.
2. **Unified Notification Architecture (`SystemActivityAlert` & Database Notifications):**
   - All system activity notifications must follow the structured schema (`title`, `message`, `type`, `causer`, `extra`), with `type` mapping to semantic badge tokens (`created` -> `success`, `updated` -> `warning`, `deleted` -> `danger`, `info` -> `info`).
3. **Mandatory Functional Role Classification (التصنيف حسب الوظيفة):**
   - Targeting & Recipient Scoping: Alerts and notifications must strictly be classified and scoped according to the 3 functional tiers: Management & Executive Leadership (`isManagement()`) for administrative/security notices to `Super-Admin`/`Admin`; Engineering & Specialist Roles (`isEngineer()`) for technical and calibration alerts; Field Operations & Technicians (`isTechnician()`) for logistics and field execution notices.
   - Role Badge Harmonization: Inside notification details, modals, and audit feeds, actor roles and employee positions MUST strictly use `<x-badge>` categorized into the 3 functional tiers (`primary`, `info`, `neutral`). State dots (`:dot="true"`) must be reserved for operational status (Read/Unread) to decouple state from role.
4. **Mandatory Trilingual Localization Across 3 Languages (AR, EN, FR):**
   - 100% English Master Keys: Every alert message, notification title, notification body, flash text, and modal description MUST be authored in English as the master key inside `__('...')`.
   - Simultaneous 3-Language Parity: Every key MUST immediately and simultaneously be registered and translated into all 3 dictionary files (`lang/en.json`, `lang/ar.json`, `lang/fr.json`) with zero non-English keys as dictionary keys.

## Mandatory Comprehensive Ecosystem & Dependency Synchronization Upon Adding New Services or Pages (Strict Rule)
Every AI agent adding or modifying any **new service** (`app/Services/`, Actions, Repositories, Jobs) or **new page/view** (`resources/views/`, Controllers, Routes) is strictly prohibited from considering the task complete until it systematically reviews, updates, and synchronizes **ALL directly and indirectly connected ecosystem files**:
1. **Trilingual Localization Synchronization (`lang/en.json`, `lang/ar.json`, `lang/fr.json`):** Extract 100% of user-facing strings, headers, labels, and descriptions into English master keys inside `__('...')`, and simultaneously synchronize with `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with 1-to-1 parity and zero non-English dictionary keys.
2. **Alerts, Flash Messages & UI Feedback (`<x-alert>`, Controllers):** Standardize all controller flash redirects using uniform keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, and `with('info', __('...'))`. The corresponding Blade template MUST contain `<x-alert>` components bound to session status/errors to guarantee immediate visual feedback to the user.
3. **Audit Trail Forensics & Description Localization (`activity_log`, `SystemTableService`):** Record Spatie activity logs with actor (`causedBy`), subject (`performedOn`), and payload properties. Any new dynamic activity log description format MUST immediately be registered with regex matchers in `SystemTableService::translateActivityDescription()` and translated across Arabic, French, and English.
4. **Notifications & System Activity Alerts (`app/Notifications/`, `SystemActivityAlert`):** Dispatched notifications must follow the structured `SystemActivityAlert` schema (`title`, `message`, `type`, `causer`, `extra`) and be scoped strictly by the 3 functional tiers: Management, Engineering, and Technicians.
5. **Search, Filter & Query State Persistence (`<x-global-filter>`, Repositories):** Tabular views MUST integrate `<x-global-filter>`, group positions into the 3 functional `<optgroup>` tiers, append `->withQueryString()` to paginators, and preserve `$request->query()` across CRUD redirects.
6. **Unified Design System & Semantic Color Tokens (`<x-table>`, `<x-badge>`, `<x-*-button>`):** Use the standardized `<x-table>` suite, polymorphic action buttons, `<x-badge>` functional variants (`primary`, `info`, `neutral`), and zero inline styles.
7. **RBAC Permissions & Navigation Hierarchy (`config/permissions.php`, Menus, Tabs):** Declare entity permissions in `config/permissions.php`, synchronize using `php artisan permissions:sync-tables`, guard routes/views with `@can`, and integrate the new page into navigation tabs/sidebars.
8. **Automated Feature Verification & Pint Formatting:** Add Feature tests verifying page rendering, permissions, CRUD flows, query state persistence, and translation parity. Run `vendor/bin/pint --dirty --format agent`.
9. **Mandatory Post-Flight Living Memory Updates:** Apply Proportional Post-Flight Living Memory updates (always `changelog.md`, `project_state.md` for structural changes, ADRs only for major architecture).

## Mandatory Legacy Code Modernization & Migration Protocol (Strict Architectural Directive)
Every AI agent tasked with modernizing legacy codebase artifacts (spaghetti code, legacy SQL tables, raw PHP scripts, or old views) into **SARL GMTM Core Kernel** is strictly and non-negotiably bound by the following enterprise rules:
1. **Role: Strict Enterprise Architect:** The agent operates as a Strict Enterprise Architect. It is strictly forbidden to copy or mirror the legacy spaghetti structure, queries, or architectural flaws. The agent's sole task is **Business Logic Mining**—extracting raw rules, formulas, validations, and workflow constraints, and cleanly rebuilding them inside SARL GMTM Core Kernel.
2. **Mandatory Laravel Eloquent Naming Standards:**
   - **Models:** Singular in `PascalCase` (`User`, `Invoice`, `Mission`, `SparePart`).
   - **Tables:** Plural in `snake_case` (`users`, `invoices`, `missions`, `spare_parts`).
   - **Pivot Tables:** Singular for both models, sorted **Alphabetically**, in `snake_case` (`mission_user`, `permission_role`). Never plural or unordered (`users_missions`).
   - **Foreign Keys:** Singular model name followed by `_id` (`employee_id`, `supplier_id`). Never plural (`employees_id`).
3. **Mandatory Gatekeeper Step: Naming Convention Fixes Table (جدول تدقيق وتصحيح التسميات):**
   For ambiguous, irregular, or non-standard legacy identifiers, before generating executable code, the AI MUST generate the Naming Convention Fixes Table comparing legacy vs. new names with applied rule justifications, and await explicit developer review. Obvious standard 1-to-1 migrations do not require blocking on this step.

4. **Clean Architecture Enforcement:** Ultra-skinny controllers (zero queries/calculations/file handling), dedicated domain services (`app/Services/`), dedicated FormRequests (`app/Http/Requests/`), and strict backed Enums (`app/Enums/`) with `badgeVariant(): string`.
5. **UI & Blade Component Standards:** Zero raw HTML `<table>` elements and zero inline styles (`style="..."`). Exclusively use GMTM component suites (`<x-table>`, `<x-badge>`, `<x-*-button>`, `<x-global-filter>`, `<x-alert>`). 100% English master translation keys in code (`__('...')`) synchronized across `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
6. **Data Migration & Foreign Key Integrity:** Exact mapping of legacy IDs to new standard foreign keys, preventing orphan records or misaligned relations, respecting soft deletes, timestamps, and audit trails.
7. **Sequential Execution Order:**
   1. Naming Convention Fixes Table
   2. Migrations & Eloquent Models
   3. PHP Enums
   4. Form Requests
   5. Domain Services
   6. Ultra-Skinny Controllers
   7. Blade Views
   8. Data Migration Script / Seeder
   9. Trilingual Dictionaries Synchronization (`en.json`, `ar.json`, `fr.json`)
8. **Mandatory Confirmation Trigger:**
   When asked if ready to modernize legacy code under these standards, the AI agent must respond exclusively with:
   **"مستعد لتطبيق معايير التسمية القياسية"**
