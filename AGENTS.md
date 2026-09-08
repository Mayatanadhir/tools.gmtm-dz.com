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

## Mandatory Agent Workflow: Living Memory & Documentation (Strict Rule)

Every agent acting on this codebase must adhere strictly to the following documentation protocol:
1. **Pre-Flight (Context Reading):** Always inspect `docs/project_state.md`, `docs/changelog.md`, and `docs/ARCHITECTURE_LOG.md` before executing modifications.
2. **Post-Flight (Documentation Update):** Any action involving writing code, refactoring, modifying migrations, creating routes, actions, or models is **incomplete** until all three documentation files in `docs/` are updated:
   - `docs/changelog.md`: Append the change under the current timestamp.
   - `docs/project_state.md`: Update models, routes, actions, tables, and system state.
   - `docs/ARCHITECTURE_LOG.md`: Document decisions, design patterns, and structural choices.

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

## Mandatory Trilingual Localization Across All Views & Features (Strict UI & Language Rule)
Every AI agent writing, modifying, or refactoring UI templates, views, components, or user-facing messages in this project is strictly mandated to adhere to the trilingual localization protocol:
1. **Zero Hardcoded Strings:**
   - It is strictly forbidden to output raw, unlocalized user-facing text, button captions, table headers, form labels, placeholders, badges, alerts, or tooltips inside Blade templates (`resources/views/**`), scripts (`resources/js/**`), or backend response messages.
   - All user-facing text must be wrapped in `__('...')` or `@lang('...')`.
2. **Mandatory 3-Language Dictionary Synchronization (Arabic, English, French):**
   - Whenever any new translation key is added to a Blade view or backend message, the agent **MUST immediately and simultaneously** add the key and its accurate, high-quality translation to all three dictionary files:
     - `lang/ar.json` (Arabic)
     - `lang/en.json` (English)
     - `lang/fr.json` (French)
3. **Strict Prohibition of Missing Keys & English Fallbacks:**
   - Never rely on Laravel's default fallback to English for missing keys in Arabic or French views.
   - All three files (`lang/ar.json`, `lang/en.json`, `lang/fr.json`) must maintain exact 1-to-1 key parity and identical key counts at all times.
4. **Technical Terminology Precision:**
   - System, forensic, and technical terms must be translated with standard professional precision in both Arabic and French (e.g. TTL, Atomic Locks, UUID, Session Tracker, Audit Log, Queue Daemons, Stack Trace).
5. **Pre-Completion Audit Verification:**
   - Any UI feature or page addition is considered **incomplete and non-compliant** until verified that zero translation keys are missing in any of the 3 languages.


