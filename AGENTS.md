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

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

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

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

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

## Mandatory Agent Workflow: Living Memory & Token-Efficient Documentation (Strict Rule)

Every agent acting on this codebase must adhere strictly to the following streamlined documentation protocol to preserve tokens while maintaining architectural integrity:
1. **Smart Pre-Flight (Targeted Context Reading):**
   - Always inspect `docs/project_state.md` for active schema, models, services, and system state.
   - **Do NOT read the entire `docs/ARCHITECTURE_LOG.md` or full `docs/changelog.md` up front.** Only read specific ADRs or recent changelog entries relevant to the specific domain you are touching (e.g., metrology, permissions, media, employees). Use targeted line slicing (`StartLine`/`EndLine`) or grep search.
2. **Proportional Post-Flight (Smart Documentation Update):**
   - `docs/changelog.md`: Always append concise entries under the current release/timestamp for all meaningful code changes, refactors, and bug fixes.
   - `docs/project_state.md`: Update ONLY when models, schema, routes, services, commands, or system configurations are created, modified, or deleted.
   - `docs/ARCHITECTURE_LOG.md`: Record a new ADR ONLY for major structural/architectural decisions. NEVER create ADRs for routine bug fixes, styling tweaks, minor refactors, or copy changes.
3. **Strict Token-Economy Response Directive (حظر تكرار نصوص التوثيق في الشات):**
   - When modifying or creating documentation in `docs/`, agents are **strictly forbidden** from dumping or echoing full document contents into chat messages.
   - Responses must strictly state the affected file path and a concise bullet-point diff summary (max 3-5 lines).
   - Avoid conversational filler; deliver code, diffs, and exact results directly.
4. **Targeted Line Slicing for Inspections:**
   - When inspecting `docs/`, never view large line ranges (500+ lines) in bulk. Always use `grep_search` or slice notation (`StartLine`/`EndLine`).


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

## Strict Security Quarantine & Mandatory Developer Verification (System Module)
- **High-Security Quarantine (`resources/views/system/**`, `app/Http/Controllers/SystemTableController.php`, `app/Services/SystemTableService.php`, `system-tables.*` routes):**
  - This module is strictly private, confidential, and security-critical for system forensics, audit trails, and infrastructure inspection.
  - **Zero Code Mixing with Standard Pages:** It must remain completely isolated from public pages, user dashboards, and everyday application features. Never leak or embed system tables, background queue details, or raw forensic logs into regular user interfaces.
  - **Mandatory Developer Pre-Approval for Any Additions:** Any new feature, route, query, or view modification involving or touching this security module must be done under the explicit supervision of the developer.
  - **Mandatory Agent Check Question:** Before adding or modifying any feature that interacts with system tables or security data, the AI agent **MUST explicitly ask the developer**:
    > *"هل هذه الإضافة تنتمي إلى هذا الملف/القسم الأمني السري أم لا؟"* ("Does this addition belong to this private security module or not?")
    and await developer clarification and explicit approval before modifying or creating files.

## Mandatory Unified Button & Semantic Color System (Strict UI Rule)
Every AI agent writing or refactoring UI markup in this project is strictly mandated to adhere to the standardized button and semantic color palette:
1. **Primary Button (`<x-primary-button>`):**
   - **Semantic Usage:** Main calls to action, submit, save, create, confirm, login, register, apply search/filters.
   - **Visual Token:** Brand Safety Orange (`bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 text-white`).
2. **Secondary Button (`<x-secondary-button>`):**
   - **Semantic Usage:** Cancel, dismiss, close modals, reset forms, neutral secondary actions.
   - **Visual Token:** Neutral bordered gray (`bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300`).
3. **Danger Button (`<x-danger-button>`):**
   - **Semantic Usage:** Destructive actions, delete account, purge records, terminate background jobs, drop entities.
   - **Visual Token:** Destructive Rose/Red (`bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 text-white`).
4. **Success Button (`<x-success-button>`):**
   - **Semantic Usage:** Positive confirmations, approvals, resolving errors, marking items as read.
   - **Visual Token:** Emerald Green (`bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 text-white`).
5. **Warning Button (`<x-warning-button>`):**
   - **Semantic Usage:** Cautionary tasks, retry operations, temporary holds/pauses.
   - **Visual Token:** Amber Yellow (`bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 text-white`).
6. **Info Button (`<x-info-button>`):**
   - **Semantic Usage:** Data inspection, view changes/diffs, preview payloads, examine stack traces.
   - **Visual Token:** Indigo Blue (`bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 text-white`).
- **Strict Prohibition of Ad-Hoc Buttons:** Never write raw `<button>` elements with arbitrary, non-standard background classes or inline styles. Always reuse the standardized components above.

## Mandatory Unified Table Component Architecture (Strict UI Rule)
Every AI agent writing or refactoring tabular UI markup in this project is strictly mandated to adhere to the standardized table component suite:
1. **Root Table (`<x-table>`):**
   - Provides a rounded-xl container (`rounded-xl border border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800 shadow-sm`), horizontal auto-scroll container, and slot structure.
   - Named slots: `<x-slot:toolbar>` (optional header/search bar), `<x-slot:header>` (table columns in `thead`), default slot (table rows in `tbody`), and `<x-slot:pagination>` (footer pagination).
2. **Table Header Cell (`<x-table.th>`):**
   - Standard uppercase typography, start alignment, and padding (`px-5 py-3 text-start font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap`).
3. **Table Row (`<x-table.tr>`):**
   - Standard table body row with subtle hover state transitions in light and dark modes (`hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors duration-150`).
4. **Table Data Cell (`<x-table.td>`):**
   - Uniform padding, text tokens, and vertical alignment (`px-5 py-3.5 text-xs text-gray-600 dark:text-gray-300 align-middle`).
5. **Table Empty State (`<x-table.empty>`):**
   - Standard centered SVG icon, customizable `colspan`, and translated message (`<x-table.empty :colspan="6" :message="__('No records found.')" />`).
6. **Table Action Buttons Suite (`<x-table.actions>` and `<x-table.action-*>`):**
   - Actions Wrapper (`<x-table.actions>`): `inline-flex items-center gap-1.5 whitespace-nowrap`.
   - Core Action (`<x-table.action>`): Polymorphic button or link (`href="..."`) with automatic semantic themes, SVG icons, tooltips, and accessibility.
   - View / Inspect (`<x-table.action-view>`): Semantic Indigo button with eye SVG for inspecting, previews, or viewing details.
   - Edit (`<x-table.action-edit>`): Semantic Amber button with pencil SVG for editing or modifying records.
   - Delete (`<x-table.action-delete>`): Semantic Rose/Red button with trash SVG for deleting or terminating records.
- **Strict Prohibition of Ad-Hoc Tables & Row Action Buttons:** Never write raw HTML `<table>` elements or ad-hoc raw buttons with inline SVGs for row operations. Always use the standardized `<x-table>` and `<x-table.action-*>` component suites.

## Mandatory Single Source of Truth for Colors, Badge System & Semantic Functional Classification (Strict UI Rule)
Every AI agent writing, modifying, or refactoring UI views, badge chips, status indicators, or PHP Enums in this project is strictly mandated to adhere to the color unification and functional classification protocol:
1. **Single Source of Truth (`<x-badge>` & `tokens.css`):**
   - The project's unified design system (`resources/css/tokens.css` and `resources/views/components/badge.blade.php`) is the sole authoritative source of truth for colors and badges.
   - Writing raw `<span>` elements with ad-hoc Tailwind color classes (e.g. `bg-amber-50 ... dark:bg-amber-950/60`, `bg-cyan-50`, `bg-teal-50`, `border-emerald-500/30`) is **strictly forbidden**.
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

## Mandatory Trilingual Localization Across All Views & Features (Strict UI & Language Rule)
Every AI agent writing, modifying, or refactoring UI templates, views, components, or user-facing messages in this project is strictly mandated to adhere to the trilingual localization protocol:
1. **Absolute Mandate: 100% English Translation Master Keys (إلزام كتابة كل مفاتيح الترجمة بالإنجليزية حصراً):**
   - **In Code:** Every single translation key passed to `__('...')`, `@lang('...')`, Form Requests, validation attributes, status flashes, or notification messages **MUST be written in English as a single unified master key language** (e.g. `__('Users')`, `__('Confirm Password')`, `__('User created successfully.')`).
   - **In JSON Dictionaries:** In all three dictionary files (`lang/en.json`, `lang/ar.json`, `lang/fr.json`), the **key** (the property name on the left side of the colon `:`) **MUST strictly and exclusively be written in the English language**.
   - **Strict Zero-Tolerance Prohibition:** Writing keys in Arabic, French, or any non-English language as dictionary keys or inside `__('...')` in code is **strictly forbidden with zero tolerance**. Arabic and French text MUST ONLY appear as the **translated values** (on the right side of `:`) in `lang/ar.json` and `lang/fr.json`.
   - **Canonical Dictionary Structure:**
     - `lang/en.json`: `"English Key": "English Key"`
     - `lang/ar.json`: `"English Key": "الترجمة باللغة العربية"`
     - `lang/fr.json`: `"English Key": "Traduction en français"`
2. **Zero Hardcoded Strings:**
   - It is strictly forbidden to output raw, unlocalized user-facing text, button captions, table headers, form labels, placeholders, badges, alerts, or tooltips inside Blade templates (`resources/views/**`), scripts (`resources/js/**`), or backend response messages.
   - All user-facing text must be wrapped in `__('...')` or `@lang('...')`.
3. **Mandatory 3-Language Dictionary Synchronization (Arabic, English, French):**
   - Whenever any new English translation key is added to a Blade view or backend message, the agent **MUST immediately and simultaneously** add the key and its accurate, high-quality translation to all three dictionary files:
     - `lang/en.json` (English master key mapping)
     - `lang/ar.json` (Arabic translation)
     - `lang/fr.json` (French translation)
4. **Strict Prohibition of Missing Keys & English Fallbacks:**
   - Never rely on Laravel's default fallback to English for missing keys in Arabic or French views.
   - All three files (`lang/ar.json`, `lang/en.json`, `lang/fr.json`) must maintain exact 1-to-1 key parity, identical key counts, and identical English key names at all times.
5. **Technical Terminology Precision:**
   - System, forensic, and technical terms must be translated with standard professional precision in both Arabic and French (e.g. TTL, Atomic Locks, UUID, Session Tracker, Audit Log, Queue Daemons, Stack Trace).
6. **Pre-Completion Audit Verification:**
   - Any UI feature or page addition is considered **incomplete and non-compliant** until verified that zero translation keys are missing in any of the 3 languages and zero non-English keys exist in the dictionary keys.

## Mandatory RBAC Authorization & Static Permissions Registry Integration (Strict Security & Architecture Rule)
Every AI agent creating, modifying, or extending any service, module, controller, route, or new page in this project is strictly mandated to adhere to the RBAC authorization protocol:
1. **Mandatory Permission Shield for All New Services & Pages:**
   - Every new service, business feature, administrative dashboard, route, or web page MUST be strictly integrated with and guarded by the system's RBAC permissions architecture.
   - Unprotected, unguarded public access to domain features or management pages is strictly forbidden.
   - Routes must be protected via permission middleware (e.g. `middleware('permission:view ...')`) or controller-level authorization (`$this->authorize(...)`, `$user->can(...)`).
   - Blade navigation links, action buttons, and UI controls must be wrapped in authorization gates (`@can(...)` or `@if(Auth::user()->can(...))`).
2. **Mandatory Registration in the Static Permissions Registry (`config/permissions.php`):**
   - The permissions system is strictly code-first and static. Database schema introspection is deprecated.
   - Any new service or page entity MUST be formally declared in `config/permissions.php` under the appropriate `$groups` entry with its icon, translation key, and entity name.
   - Standard CRUD permissions (`view [entity]`, `create [entity]`, `edit [entity]`, `delete [entity]`) or specialized action permissions must be explicitly registered.
3. **Mandatory Permissions Synchronization:**
   - After updating `config/permissions.php`, the agent MUST run `php artisan permissions:sync-tables` to persist newly registered permissions in the database, automatically assign them to `Super-Admin`, and prune any obsolete permissions.
4. **Mandatory Developer Clarification Protocol (Strict Inquire-First Rule):**
   - If the AI assistant is unsure, in doubt, or does not recognize the exact permission names, verbs, or entity grouping to apply for any new service or page, the AI assistant **MUST explicitly ask the developer for clarification before writing code or modifying files**:
     > *"ما هي أسماء وصيغ الصلاحيات المعتمدة لهذه الخدمة/الصفحة الجديدة لإضافتها إلى القائمة الثابتة (`config/permissions.php`)؟"*
     ("What are the approved permission names and format for this new service/page to add to the static catalog?")
   - The agent MUST wait for developer clarification and approval before proceeding with file creation or modifications.

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
   - Semantic Variants Standard:
     - `success` (Emerald Green): Positive operational confirmations, record creation, update, and deletion success.
     - `danger` (Rose Red): Errors, operation failures, deletion blockers, validation summaries.
     - `warning` (Amber Yellow): Cautionary alerts, retry prompts, data sensitivity warnings, quarantine alerts.
     - `info` (Indigo Blue): Informational tips, technical details, preview notes.
     - `primary` (Brand Green): System-wide executive announcements.
   - Standardized Session Flash Keys: Controllers must strictly pass standardized session keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, and `with('info', __('...'))`. Views rendering session messages must map these keys directly into the corresponding `<x-alert>` variant.
2. **Unified Notification Architecture (`SystemActivityAlert` & Database Notifications):**
   - All system activity notifications must follow the structured schema (`title`, `message`, `type`, `causer`, `extra`).
   - The `type` field must strictly map to semantic tokens: `created` / `success` (`success`), `updated` / `warning` (`warning`), `deleted` / `danger` (`danger`), `info` (`info`).
3. **Mandatory Functional Role Classification (التصنيف حسب الوظيفة):**
   - **Targeting & Recipient Scoping:** Alerts and notifications must strictly be classified and scoped according to the 3 functional tiers:
     - `Management & Executive Leadership` (`isManagement()`): Administrative events, security warnings, user management, and employee compensation notices are dispatched exclusively to `Super-Admin` and `Admin`.
     - `Engineering & Specialist Roles` (`isEngineer()`): Technical notifications, instrument movement, calibration statuses, and metrology alerts.
     - `Field Operations & Technicians` (`isTechnician()`): Operational notices, field mission tasks, and unit maintenance events.
   - **Role Badge Harmonization:** Inside notification details, modals, and audit feeds, actor roles and employee positions MUST strictly use `<x-badge>` categorized into the 3 functional tiers (`primary` for Management, `info` for Engineering, `neutral` for Technicians).
   - **Decoupling State from Role:** Operational states of notifications (Read/Unread) must use `:dot="true"` and never be visually confused with functional job positions.
4. **Mandatory Trilingual Localization Across 3 Languages (AR, EN, FR):**
   - 100% English Master Keys: Every alert message, notification title, notification body, flash text, and modal description MUST be authored in English as the master key inside `__('...')`.
   - Simultaneous 3-Language Parity: Every key MUST immediately and simultaneously be registered and translated into all 3 dictionary files:
     - `lang/en.json` (`"English Key": "English Key"`)
     - `lang/ar.json` (`"English Key": "الترجمة العربية الدقيقة"`)
     - `lang/fr.json` (`"English Key": "Traduction française précise"`)
   - Zero hardcoded text, zero missing keys, zero English fallbacks in Arabic/French views, and zero non-English keys as dictionary keys.

## Mandatory Comprehensive Ecosystem & Dependency Synchronization Upon Adding New Services or Pages (Strict Rule)
Every AI agent adding or modifying any **new service** (`app/Services/`, Actions, Repositories, Jobs) or **new page/view** (`resources/views/`, Controllers, Routes) is strictly prohibited from considering the task complete until it systematically reviews, updates, and synchronizes **ALL directly and indirectly connected ecosystem files**:
1. **Trilingual Localization Synchronization (`lang/en.json`, `lang/ar.json`, `lang/fr.json`):**
   - Extract 100% of user-facing strings, table headers, form labels, validation error messages, badge labels, modal titles, button captions, and activity log descriptions.
   - Author every translation master key strictly in English (`__('...')`).
   - Simultaneously add the master keys and their accurate translations to all 3 dictionary files (`en.json`, `ar.json`, `fr.json`) maintaining exact 1-to-1 parity and zero non-English dictionary keys.
2. **Alerts, Flash Messages & UI Feedback (`<x-alert>`, Controllers):**
   - Standardize all controller flash redirects using uniform keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, and `with('info', __('...'))`.
   - The corresponding Blade template MUST contain `<x-alert>` components bound to session status/errors to guarantee immediate visual feedback to the user.
3. **Audit Trail Forensics & Description Localization (`activity_log`, `SystemTableService`):**
   - If the new service or page executes domain mutations (create, update, delete, toggle, process), it must record Spatie activity logs with actor (`causedBy`), subject (`performedOn`), and payload properties.
   - Any new dynamic activity log description format MUST immediately be registered with regex matchers in `SystemTableService::translateActivityDescription()` and translated across Arabic, French, and English.
4. **Notifications & System Activity Alerts (`app/Notifications/`, `SystemActivityAlert`):**
   - If the service dispatches notifications, it must use the structured `SystemActivityAlert` schema (`title`, `message`, `type`, `causer`, `extra`).
   - Scoped strictly by the 3 functional tiers: Management & Executive Leadership (`isManagement()`) for administrative notices, Engineering & Specialist Roles (`isEngineer()`) for technical events, and Field Operations & Technicians (`isTechnician()`) for logistics/field tasks.
5. **Search, Filter & Query State Persistence (`<x-global-filter>`, Repositories):**
   - If the new page presents tabular or list data, it MUST integrate `<x-global-filter>`.
   - Position selectors MUST be categorized into the 3 functional `<optgroup>` tiers.
   - All paginators must append `->withQueryString()`, and all CRUD redirect actions must preserve `$request->query()`.
6. **Unified Design System & Semantic Color Tokens (`<x-table>`, `<x-badge>`, `<x-*-button>`):**
   - Tables must use the standardized `<x-table>` suite (`<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`).
   - Action buttons must use standardized polymorphic components (`<x-table.action-view>`, `<x-table.action-edit>`, `<x-table.action-delete>`).
   - Badges must use `<x-badge>` mapped to functional tiers (`primary`, `info`, `neutral`).
   - Zero inline styles (`style="..."` strictly forbidden).
7. **RBAC Permissions & Navigation Hierarchy (`config/permissions.php`, Menus, Tabs):**
   - Declare the entity permissions in `config/permissions.php` under `$groups` and synchronize using `php artisan permissions:sync-tables`.
   - Guard routes via `permission:...` middleware and UI elements with `@can`.
   - Integrate the new page into appropriate navigation hubs, sidebar tabs (`<x-*-tabs>`), and tool icons (`<x-tool-icon>`).
8. **Automated Feature Verification & Pint Formatting:**
   - Create or extend automated tests (`tests/Feature/`) verifying page rendering, permissions, CRUD flows, query state persistence, and translation parity.
   - Run `vendor/bin/pint --dirty --format agent` to format all modified PHP code.
9. **Mandatory Post-Flight Living Memory Updates:**
   - Apply Proportional Post-Flight Living Memory updates per Section 1 (always `changelog.md`, `project_state.md` for structural changes, ADRs only for major architecture).

## Mandatory Legacy Code Modernization & Migration Protocol (Strict Architectural Directive)
Every AI agent tasked with modernizing legacy codebase artifacts (spaghetti code, legacy SQL tables, raw PHP scripts, or old views) into **SARL GMTM Core Kernel** is strictly and non-negotiably bound by the following enterprise rules:
1. **Role: Strict Enterprise Architect:**
   - The agent operates as a **Strict Enterprise Architect**.
   - **Zero Spaghetti Mirroring:** Legacy code is often unorganized spaghetti code. It is **STRICTLY FORBIDDEN** to copy or mirror its structure, queries, inline styling, or architectural flaws.
   - The agent's sole task is **Business Logic Mining**—extracting the raw business logic, mathematical formulas, validations, and workflow rules, and rebuilding them cleanly within SARL GMTM Core Kernel.
2. **Mandatory Laravel Eloquent Naming Standards:**
   - **Models:** Must be **Singular** in `PascalCase` (e.g., `User`, `Invoice`, `Mission`, `SparePart`).
   - **Tables:** Must be **Plural** in `snake_case` (e.g., `users`, `invoices`, `missions`, `spare_parts`).
   - **Pivot Tables:** Must be **Singular** for both models, sorted **Alphabetically**, in `snake_case` (e.g., `mission_user`, `permission_role`). Plural names or unordered forms like `users_missions` are strictly prohibited.
   - **Foreign Keys:** Must use the **Singular** model name followed by `_id` in `snake_case` (e.g., `employee_id`, `supplier_id`). Plural variants like `employees_id` are strictly prohibited.
3. **Mandatory Gatekeeper Step: Naming Convention Fixes Table (جدول تدقيق وتصحيح التسميات):**
   - For ambiguous, irregular, or non-standard legacy identifiers, before generating executable code, the AI agent **MUST generate the Naming Convention Fixes Table**:
     | Type (النوع) | Legacy Name (الاسم القديم) | Standard New Name (الاسم المعياري الجديد) | Justification & Applied Rule (سبب التعديل والقاعدة) |
     | :--- | :--- | :--- | :--- |
   - The agent must explicitly justify every correction (singular/plural, snake_case, alphabetical order) and await developer review. Obvious standard 1-to-1 migrations do not require blocking on this step.

4. **Clean Architecture Enforcement:**
   - **Ultra-Skinny Controllers:** Controllers only receive validated requests, delegate to Services, and return responses with standard flash keys (`with('success')`, `with('error')`, `with('warning')`, `with('info')`). Zero DB queries, transactions, calculations, or file processing in Controllers.
   - **Dedicated Domain Services (`app/Services/`):** All business logic, entity state mutations, calculations, file optimization, and activity logging reside in dedicated Service classes.
   - **Dedicated FormRequests (`app/Http/Requests/`):** Zero inline validation. Dedicated `Store...Request` and `Update...Request` classes are mandatory.
   - **Strict Enums (`app/Enums/`):** Categorical fields, statuses, and tiers must be backed PHP Enums providing `badgeVariant(): string`.
5. **UI & Blade Component Standards:**
   - Zero raw HTML `<table>` elements and zero inline styles (`style="..."`).
   - Exclusively use the GMTM unified component suite: `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`, `<x-table.action-view>`, `<x-table.action-edit>`, `<x-table.action-delete>`, `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`, `<x-badge>`, `<x-global-filter>`, and `<x-alert>`.
   - Functional tier badge categorization: Management (`primary`), Engineering (`info`), Technicians (`neutral`). Operational statuses use status dots (`:dot="true"`).
   - 100% English master translation keys in code (`__('...')`). Zero non-English keys in code or as JSON property names. Simultaneous 3-language synchronization in `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
6. **Data Migration & Foreign Key Integrity:**
   - Exact mapping of legacy IDs to new foreign keys, preventing orphan records or misaligned relations.
   - Safe data migration scripts/seeders respecting soft deletes (`deleted_at`), timestamps, and audit trails (`activity_log`).
7. **Sequential Execution Order:**
   The agent must never output code in a chaotic single dump. The generation MUST strictly proceed in this sequence:
   1. Naming Convention Fixes Table (جدول تصحيح التسميات)
   2. Migrations & Eloquent Models
   3. PHP Enums
   4. Form Requests
   5. Domain Services
   6. Ultra-Skinny Controllers
   7. Blade Views
   8. Data Migration Script / Seeder
   9. Trilingual Dictionaries Synchronization (`en.json`, `ar.json`, `fr.json`)
8. **Mandatory Confirmation Trigger:**
   When asked if ready to review and modernize legacy code under these standards, the AI agent must respond exclusively with:
   **"مستعد لتطبيق معايير التسمية القياسية"**
