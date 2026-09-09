# Architecture Log: tools.gmtm-dz.com

This document tracks fundamental architectural patterns, engineering decisions, and conventions adopted across the lifecycle of the project.

---

<<<<<<< HEAD
## [ADR-045] User Model MustVerifyEmail Implementation & Immediate Verification Dispatch
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The application utilizes Laravel Breeze authentication and enforces the `verified` middleware across private routes (`/dashboard`, `/system-tables/*`). However, the `App\Models\User` authenticatable model had `implements MustVerifyEmail` commented out. As a consequence, when new users registered via `RegisteredUserController`, the `Registered` event did not invoke Laravel's `SendEmailVerificationNotification` listener, omitting the verification email. In addition, the developer mandated that the email sender name must match the application name (`Laravel <visitor@gmtm-dz.com>`) and dispatch verification immediately upon registration.
- **Decision:**
  1. **Contract Implementation:** Updated `App\Models\User` to explicitly implement `Illuminate\Contracts\Auth\MustVerifyEmail`.
  2. **Sender Branding Configuration:** Ensured `.env` configures `MAIL_FROM_ADDRESS="visitor@gmtm-dz.com"` and `MAIL_FROM_NAME="${APP_NAME}"`, matching `config('mail.from')` so that emails are sent with the sender name formatted as `config('app.name')` (`Laravel <visitor@gmtm-dz.com>`).
  3. **Immediate Notification Dispatch:** With `MustVerifyEmail` implemented, Laravel's core `SendEmailVerificationNotification` listener triggers automatically when `event(new Registered($user))` is dispatched in `RegisteredUserController@store`, immediately sending the `VerifyEmail` notification.
  4. **Email & View Localization (Rule 17):** Synchronized 8 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` for mail notifications and verification templates, achieving 100% trilingual dictionary parity (499 keys each, 0 missing).
  5. **UI Standardization (Rule 14 & Rule 12):** Modernized `resources/views/auth/verify-email.blade.php` to use `<x-primary-button>` and `<x-secondary-button>` with dark mode text utility classes.
  6. **Automated Feature Testing:** Added `test_email_verification_notification_is_sent_upon_registration` to `tests/Feature/Auth/RegistrationTest.php`. Total test suite: 244 tests, 1045 assertions (100% passing).
- **Consequences:** New registrants immediately receive a verification email from `Laravel <visitor@gmtm-dz.com>` and must verify their email before accessing verified routes.

---

## [ADR-044] Database Notification Payload Localization & Trilingual Key Synchronization
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** In the Notifications Explorer (`resources/views/system/notifications.blade.php`), English viewports displayed mixed language strings (e.g. Arabic string `مستخدم جديد` under the English column header `Type` alongside `System Activity Alert` and `Created`). This occurred because the database notification payload created by `UserObserver` stored dynamic Arabic titles directly (`title: 'مستخدم جديد'`), and the Blade template rendered the variable directly as `{{ $notificationTitle }}` without invoking the translation helper `__()`. Furthermore, `lang/en.json` and `lang/fr.json` lacked dictionary mappings for these payload titles.
- **Decision:**
  1. **Template Localization (Rule 17):** In `resources/views/system/notifications.blade.php`, wrapped dynamic `$notificationTitle` in `{{ __($notificationTitle) }}` for both inline title preview and tooltip attributes.
  2. **Trilingual Payload Key Parity:** Added bidirectional translation entries to `lang/ar.json`, `lang/en.json`, and `lang/fr.json` mapping database payload titles (`"مستخدم جديد"` -> `New User` / `Nouvel utilisateur`, `"حذف مستخدم"` -> `User Deleted` / `Utilisateur supprimé`). This preserves existing database contracts and test assertions while achieving 100% pure English, French, or Arabic rendering depending on the active locale.
  3. **Dictionary Parity Verification:** Verified exact 100% key parity across all three language files (491 keys each, 0 missing).
- **Consequences:** Mixed language rendering is completely eliminated in the notifications explorer across all supported locales without requiring retroactive database mutations.

---

## [ADR-043] Configured Roles Unified Table Architecture & Functional Columns Localization
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The "Configured Roles" section in `system/roles.blade.php` previously rendered role entities as a card grid with raw database strings (`Super-Admin`, `Admin`, `User`) and technical permission keys without explicit functional descriptions. To maintain consistency with the application's unified table component design guidelines (Rule 15) and provide a polished administration experience, the developer selected the option to migrate from cards to a standardized `<x-table>` with functional, localized columns.
- **Decision:**
  1. **Table Component Migration (Rule 15):** Replaced the card grid layout in `resources/views/system/roles.blade.php` with `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.empty>`, and `<x-table.actions>`.
  2. **Functional Column Architecture:**
     - `Role & Function`: Dual-tier typography displaying the translated functional title (`Super Administrator (Full Sovereign Access)`, `System Administrator (Operations & Users)`, `Standard User (General Workspace Services)`) paired with the technical code (`roles.name`) and guard name (`web`).
     - `Functional Scope`: Provides a localized summary of exact role responsibilities, privileges, and boundaries.
     - `Assigned Users`: Centered count of users actively holding the role.
     - `Privileges Scope`: Semantic green badge for sovereign Super-Admin privileges, or indigo badge count with inline permission preview chips.
     - `Role Status`: Semantic status badges distinguishing Protected System Roles, Default Roles, and Custom Roles.
     - `Actions`: Unified `<x-table.actions>` containing `<x-table.action-edit>` and `<x-table.action-delete>` with immutability protection for Super-Admin.
  3. **Service-Driven Introspection:** Extended `App\Services\PermissionDiscoveryService` with `getRoleFunctionalTitle()` and `getRoleFunctionalScope()`. Passed `discoveryService` to `system.roles` view.
  4. **Trilingual Localization (Rule 17):** Synchronized 16 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (479 keys each, 100% key parity).
  5. **Automated Testing:** Added `test_roles_page_renders_unified_table_with_translated_functional_columns` in `tests/Feature/RoleAndPermissionTest.php`. Total test suite: 243 tests, 1042 assertions, 100% passing.
- **Consequences:** The Roles & Permissions view achieves total UI parity with the rest of the System Explorer tables, offering clear functional descriptions and full localization for system roles.

---

## [ADR-042] Zero-State First-Run Super Admin Web Setup & Auto-Migration Architecture (`system-tables.setup`)
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** When the application is deployed on a web server before CLI or SSH access is performed, accessing the public root `/` rendered the standard welcome page without an administrator account existing, leaving users stranded without any means of signing in. Furthermore, when an empty, brand new database has zero tables created, invoking Eloquent queries crashes with SQL table missing exceptions. The developer explicitly requested that if the database is unmigrated or empty, the very first screen displayed prior to the welcome page should be a compact table/card prompt to configure the initial Super-Admin account, and confirmed that this feature belongs under the System Security Module (Rule 13).
- **Decision:**
  1. **System Module Placement (Rule 13):** Created `setup()` and `storeSetup()` inside `App\Http\Controllers\SystemTableController.php` with business logic inside `App\Services\SystemTableService::createInitialSuperAdmin()` and the view in `resources/views/system/setup.blade.php`.
  2. **Root Interception Gate:** In `routes/web.php`, the root route `/` inspects `!Schema::hasTable('users') || User::count() === 0`. If no tables exist or no users exist, it automatically redirects the browser to `route('system-tables.setup')`.
  3. **Zero-CLI Automated Web Migration Pipeline:** If `!Schema::hasTable('users')`, `SystemTableService::createInitialSuperAdmin` automatically calls `Artisan::call('migrate', ['--force' => true])` and `Artisan::call('db:seed', ['--force' => true])` before provisioning the Super Admin, achieving a complete zero-touch installation directly from the web browser.
  4. **Strict Anti-Hijacking Lockout:** If any user already exists in the database:
     - `GET /system-tables/setup` aborts with `404 Not Found`.
     - `POST /system-tables/setup` is rejected by `InitialSystemSetupRequest::authorize()` with `403 Forbidden`.
  5. **Compact Visual Table Form UI:** In `resources/views/system/setup.blade.php`, integrated a compact specification table detailing assigned role (`Super-Admin`), initial status (`Active`), permission scope (`Full System Authority`), and database schema state (`Auto-Migration Required` or `Schema Ready`) alongside the credentials form using standardized components (`<x-badge>`, `<x-input-label>`, `<x-text-input>`, `<x-input-error>`, `<x-primary-button>`).
  6. **Immediate Authentication & Audit Logging:** Upon successful validation, the user is created, assigned the protected `'Super-Admin'` role, permissions discovered/synchronized, logged in via `Auth::login($user)`, and redirected to `dashboard`.
  7. **Trilingual Localization:** Synchronized 17 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` maintaining 100% key parity (463 keys each).
  8. **Middleware Session & Cache Driver Fallback Protection:** Added `AppServiceProvider::ensureSafeDriversWhenUnmigrated()` to dynamically switch `session.driver` and `cache.default` to `file` if tables `sessions` or `cache` do not yet exist, preventing uncaught `QueryException` (1146 Table doesn't exist) during Laravel's `StartSession` middleware before web routes are reached. Upon successful migration inside `storeSetup()`, the active session is mirrored into the database `sessions` table to guarantee session continuity.
  9. **Feature Test Verification:** Added `tests/Feature/InitialSystemSetupTest.php` with 9 test cases verifying the gate, rendering, auto-migration execution, database session driver fallback, creation, validation, anti-hijack lockout, and welcome page restoration. (Total test suite: 242 tests, 1035 assertions, 100% passing).
- **Consequences:** The application offers a self-contained zero-state onboarding and self-installation experience. Fresh installations with empty databases immediately guide the administrator to securely build tables and initialize their Super-Admin account through a beautiful UI, while completely blocking unauthorized access once initialized.

---

## [ADR-041] Standardized Client Handoff & Project Setup Command Architecture (`project:setup`)
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** When handing off or deploying the application to a client or new server environment with an empty database, developers previously had to manually chain multiple commands (`migrate`, `db:seed`, `permissions:sync-tables`) and either seed a default Super-Admin with a hardcoded password or write raw SQL/tinker commands. Hardcoded passwords in seeders or `.env` files create critical security liabilities, and multi-step manual processes introduce deployment errors or incomplete role/permission initialization.
- **Decision:**
  1. **Unified Setup Command (`App\Console\Commands\SetupProjectCommand`):** Created `php artisan project:setup` providing an end-to-end, single-command onboarding pipeline.
  2. **Migration Orchestration:** Runs `migrate` (or `migrate:fresh` when `--fresh` is passed) with `--force` flag for production safety.
  3. **Baseline Seeding & Schema Discovery:** Triggers `db:seed` with `--force`, ensuring `RolesAndPermissionsSeeder` creates `User`, `Admin`, and `Super-Admin` roles and executes `PermissionDiscoveryService::generateCrudPermissionsForTables()` to discover all non-blacklisted database tables and generate standard CRUD permissions.
  4. **Interactive Masked Super-Admin Provisioning:** Prompts the deploying engineer for Super-Admin credentials via interactive masked terminal inputs (`$this->secret()`), enforcing email format validation, password length constraints (>= 8 characters), and confirmation match.
  5. **Domain-Compliant Account Activation:** Updates or creates the user with `AccountStatus::Active`, sets `email_verified_at = now()`, synchronizes the protected `'Super-Admin'` Spatie RBAC role, and invokes `PermissionDiscoveryService::syncSuperAdminPermissions()`.
  6. **Automated Test Suite:** Created `tests/Feature/SetupProjectCommandTest.php` covering the full interactive lifecycle, validation failures, mismatch handling, existing user promotion, and fresh migrations across 6 test cases.
- **Consequences:** Project deployments and client deliveries can be executed with a single secure, interactive command. Zero administrative credentials are hardcoded into migrations, seeders, or version control.

---

=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
## [ADR-040] Dual-Layer Anti-Self-Action Protection Suite (Self-Deletion, Self-Suspension & Edit Modal Field Locks)
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** While basic anti-lockout protection existed for profile deletion, within the System Tables Users Management view an authenticated administrator could theoretically trigger actions against their own account: self-deletion via the row action button or `DELETE /system-tables/users/{user}`, self-suspension via quick toggle or the edit modal's status dropdown, and self-role modification. This risked immediate administrative lockout or operational disruption.
- **Decision:**
  1. **Backend Anti-Self-Deletion Guard (`SystemTableController::destroyUser`):** Added `$user->id === auth()->id()` check that immediately aborts self-deletion and redirects with a localized error: `"You cannot delete your own account."`.
  2. **Backend Anti-Self-Suspension Guard (`SystemTableController::toggleUserStatus` & `updateUser`):** Enforced that an administrator cannot toggle or update their own account status to `suspended`.
  3. **UI Table Action Masking (`resources/views/system/users.blade.php`):**
     - Wrapped `<x-table.action-delete>` in `@if($user->id !== auth()->id())` to prevent rendering the delete action for the admin's own row.
     - Protected quick status toggle button with `@if($user->id !== auth()->id())`.
  4. **Edit User Modal Read-Only Field Architecture (`resources/views/system/users.blade.php`):**
     - In Alpine.js component: initialized `authUserId: {{ auth()->id() }}` and computed `editUserIsSelf: false` upon opening the edit modal.
     - Role Selection: When `editUserIsSelf` is true, the `<select>` dropdown is replaced with a read-only badge display and warning message (`"You cannot change your own role."`).
     - Status Selection: When `editUserIsSelf` is true, the status dropdown is replaced with a read-only status badge display and warning message (`"You cannot suspend your own account."`).
  5. **Trilingual Dictionary Parity (Rule 17):** Added and synchronized keys `"You cannot delete your own account."`, `"You cannot suspend your own account."`, and `"You cannot change your own role."` across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (446 keys each, 100% key parity).
  6. **Automated Feature Verification:** Added comprehensive test cases in `SystemTableTest.php` covering self-deletion rejection, third-party deletion success, toggle self-suspension rejection, and form update self-suspension rejection.
- **Consequences:** Administrators are physically and logically prevented from locking themselves out of the system, whether through inadvertent UI clicks or direct HTTP requests.

---

## [ADR-039] Rank-Lock Guard: Preventing Self-Role Modification by Authenticated Administrators
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** An authenticated administrator could submit a PUT request to `system-tables.users.update` targeting their own user ID and include a `role` field, thereby downgrading or changing their own rank without requiring approval from another administrator. This created a security gap in the RBAC integrity model.
- **Decision:**
  1. **Backend Guard in `updateUser`:** Added a rank-lock check in `SystemTableController::updateUser()`. If `$user->id === auth()->id()` and a `role` key is present in the validated payload, the request is immediately rejected with `withErrors(['role' => __('You cannot change your own role.')])`.
  2. **Consistent Pattern:** The guard is placed immediately after the existing anti-lockout suspend guard, maintaining a clear "self-action safety zone" at the top of `updateUser`.
  3. **Trilingual Translation:** Added `"You cannot change your own role."` to `lang/ar.json`, `lang/en.json`, and `lang/fr.json` with precise Arabic and French equivalents (`لا يمكنك تغيير رتبتك الخاصة.` / `Vous ne pouvez pas modifier votre propre rôle.`).
  4. **Test Coverage:** Added `test_admin_cannot_change_their_own_role_via_system_panel` in `tests/Feature/SystemTableTest.php` to verify the guard fires correctly, the session contains the `role` error, and the admin's role remains `Super-Admin` after the rejected attempt.
- **Consequences:** Administrators cannot inadvertently or maliciously change their own rank. Role modifications for oneself must be performed by a different authorized administrator.

---

## [ADR-038] Default Baseline Role Auto-Assignment, Immutability & Protection Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Newly registered users in the application previously had no role assigned automatically unless configured manually by an administrator. Furthermore, the baseline default role (`User`) was vulnerable to accidental deletion or renaming in the System Tables RBAC management interface, which would compromise user onboarding and role-based authorization boundaries.
- **Decision:**
  1. **Lifecycle Hook Auto-Assignment:** Implemented automatic assignment of the default `'User'` role inside `UserObserver::created()`. The observer resolves the role name dynamically via `PermissionDiscoveryService::getDefaultRole()` and attaches it if the user has no existing roles.
  2. **Service-Layer RBAC Defaults:** Defined `getDefaultRole()` and `isDefaultRole()` in `PermissionDiscoveryService` alongside existing `isSuperRole()` guards to maintain a single source of truth for protected system roles.
  3. **Backend Immutability & Anti-Deletion Enforcement:** In `SystemTableController`:
     - `destroyRole()`: Blocks deletion of any role matching `isDefaultRole()`, returning a session error (*"The default role cannot be deleted."*).
     - `updateRole()`: Prevents renaming the default role (*"The default role cannot be renamed."*), while preserving permission assignment adjustments.
  4. **UI Protection & Visual Indicators:** In `resources/views/system/roles.blade.php`:
     - Default role card renders `<x-badge variant="info">` (`Default Role`) with an explicit checkmark shield SVG.
     - The delete action `<x-table.action-delete>` is completely omitted from the DOM for the default role.
     - The Edit Role modal applies `x-bind:readonly` and muted cursor styling to the name field with a warning note clarifying that the default role name is locked.
  5. **Header Switcher Refinement:** Refactored `theme-switcher.blade.php` to a circular ghost icon button and `language-switcher.blade.php` to a pill flag button with brand orange accents.
  6. **User Role Non-Null & Immutability Enforcement:** In `UpdateSystemUserRequest` and `CreateSystemUserRequest`, role validation was converted from `nullable` to `['sometimes', 'required', 'string', 'exists:roles,name']`, strictly prohibiting setting or modifying a user's role to null or empty string. In `SystemTableController::updateUser`, role synchronization safeguards that no user can be stripped of all roles, falling back automatically to the default baseline role (`User`). In `resources/views/system/users.blade.php`, the `-- Select Role (Optional) --` option was removed and replaced with a disabled placeholder `-- Select Role --` with HTML5 `required` attribute.
- **Consequences:** All new accounts automatically receive the lowest-privilege role upon signup. The default role cannot be deleted or renamed by any administrator, preventing system lockout or registration breakages. Users can never be left with null/empty roles.

---

## [ADR-037] Profile Suite Modernization — Hero Card, 2-Column Grid & Anti-Lockout Guard
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The `profile/edit.blade.php` page used a single-column `max-w-xl` constrained layout, leaving significant whitespace on wide screens and providing no visual identity context for the authenticated user. The delete-user form had no visual protection against Super-Admin self-deletion (backend guard existed but the UI offered no warning). No profile photo feature existed.
- **Decision:**
  1. **User Identity Hero Card** — A full-width gradient banner + overlapping avatar (photo or initial monogram) with role, verification, status, and membership date badges is rendered at the top of the profile page. This follows the same card design pattern used in system explorer views.
  2. **2-Column Responsive Layout** — `profile/edit.blade.php` uses `grid grid-cols-1 lg:grid-cols-2 gap-6` with the Profile Information card on the left and Password + Danger Zone stacked vertically on the right. This matches the information density pattern of the system explorer, eliminating dead space.
  3. **Photo Upload Suite (Alpine.js)** — An `x-data` Alpine.js island provides instant file preview and name display without a round-trip. File stored to `storage/app/public/profile_photos/` via the `public` disk. Removal flag (`remove_photo=1`) is validated and handled in `ProfileController`. Old file is deleted from disk on replacement or removal.
  4. **Anti-Lockout Guard (Dual Layer)** — Backend: `ProfileController::destroy()` guards against Super-Admin deletion, returning a redirect with a session error. Frontend: `delete-user-form.blade.php` checks `$user->isSuperAdmin()` and replaces the delete button with an amber informational card containing a shield SVG and the localized protection message.
  5. **Success Alerts** — Both `update-profile-information-form` and `update-password-form` use `<x-alert variant="success">` for flash feedback, following the unified semantic alert system established in ADR-035.
- **Consequences:** Profile page now has first-class UX parity with the rest of the system. Super-Admin lockout risk is eliminated at both the UI and controller layer. All behavior is covered by 9 PHPUnit tests.

---

## [ADR-036] Table Action Suite Expansion & Inline Button Submit Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** In `Database Backups & Disaster Recovery` (`resources/views/system/backups.blade.php`), download and restore row actions used ad-hoc raw `<a>` and `<button>` HTML elements with manual Tailwind classes and inline SVGs, violating Rule 15. Furthermore, `<x-table.action-delete>` contained a critical defect where duplicate `type="delete" type="submit"` attributes caused Blade to override the component prop `$type` with `'submit'`, resulting in default indigo styling, eye icon, and rendering `<button type="button">` which prevented form submission.
- **Decision:**
  1. **Core Action Component Expansion (`action.blade.php`):** Added `buttonType` prop (`buttonType="button"`, passes down to `<button type="...">`), enabling `<button type="submit">` when wrapped in forms. Added native support for `type="download"` (Indigo Info theme with download SVG) and `type="restore"` (Amber Warning theme with restore SVG).
  2. **Dedicated Table Action Components:** Created `<x-table.action-download>` and `<x-table.action-restore>` in `resources/views/components/table/` adhering to Rule 15.
  3. **Fixed Inline Delete Submission (`action-delete.blade.php`):** Replaced duplicate `type` attributes with `button-type="submit"`, ensuring the button renders with the proper rose destructive styling, trash icon, and genuine HTML submit behavior.
  4. **Double-Submission Protection in Backups View:** Implemented Alpine.js `isCreating` and `isRestoring` states with animated SVG spinners on the "Create Backup Now" button and restore modal confirmation button.
- **Files:** `components/table/action.blade.php`, `components/table/action-delete.blade.php`, `components/table/action-download.blade.php`, `components/table/action-restore.blade.php`, `system/backups.blade.php`, `lang/ar.json`, `lang/en.json`, `lang/fr.json`.
- **Result:** Complete adherence to Rule 14 & Rule 15 across backups view, zero ad-hoc buttons/links, and fully functional destructive/restore actions with double-submission protection.

---

## [ADR-035] Unified CRUD Suite Architecture — x-crud-modal Component Group
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The System Explorer views (`users.blade.php`, `roles.blade.php`) contained 9 hand-coded modal dialogs (4 delete + 5 form modals). Each duplicated ~80–120 lines of identical scaffold code totaling ~900 lines of repetitive markup.
- **Decision:**
  1. **`<x-crud-modal.delete>`** — Accepts Alpine variable names as plain string props (`show`, `action-url`, `item-name`). Always pass WITHOUT the Blade `:` prefix since the value is an Alpine variable name, not a PHP URL.
  2. **`<x-crud-modal.form>`** — For CREATE forms, use `:action-url="route(...)"` (PHP). For EDIT forms, use `alpine-action="editActionUrl"` (Alpine variable name as string). `method` prop auto-selects icon, spoofing, and default text. Named `$hidden` slot for hidden inputs.
  3. **`destroyUser()` added to `SystemTableController`** — Follows the activity-log-before-delete pattern from ADR-033. Route: `DELETE /system-tables/users/{user}`.
- **Files:** `crud-modal.blade.php`, `crud-modal/delete.blade.php`, `crud-modal/form.blade.php`, `users.blade.php`, `roles.blade.php`, `routes/web.php`, `SystemTableController.php`.
- **Result:** 9 modal dialogs → 2 reusable components. ~900 lines of markup eliminated. Consistent UX enforced at component level.

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

---

## [ADR-033] Unified CRUD Suite Architecture & Trilingual Dictionary Parity Audit
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Previously, entity creation, modification, and deletion modals in the System Explorer were hand-coded per view (`users.blade.php`, `roles.blade.php`). This duplicated backdrop transitions, accessibility attributes, form scaffolding, header icons, and action buttons (~250 lines per modal). Furthermore, simple row-level deletions required either separate full modals or verbose raw `<form>` boilerplate in Blade templates. An audit of translation dictionaries also detected 12 duplicate keys and 3 missing keys across `lang/*.json`.
- **Decision:**
  1. **Standardized Reusable CRUD Suite Components (`resources/views/components/crud-modal/`):**
     - `<x-crud-modal.delete>`: Unified confirmation modal taking Alpine variable names as standard props (`show`, `action-url`, `item-name`). Features danger icon, translated warning text, Cancel and Danger buttons.
     - `<x-crud-modal.form>`: Unified form modal supporting `POST`/`PUT`, dynamic Alpine action binding (`alpine-action`), named `$hidden` slot, and configurable semantic icon color themes (`orange`, `amber`, `indigo`, `emerald`, `rose`).
     - `<x-crud-modal.blade.php>`: Group wrapper component.
  2. **Enhanced Inline Table Deletion (`<x-table.action-delete>`):**
     - Added optional `action-url`, `confirm-message`, and `method` props.
     - When `action-url` is passed, automatically encapsulates a self-contained POST form with `@method('DELETE')`, `@csrf`, and optional JavaScript confirmation prompt, eliminating manual form boilerplate.
     - When `action-url` is omitted, continues functioning as a standard polymorphic action button for Alpine modal triggers.
  3. **Views Refactoring:**
     - Refactored `users.blade.php` to use `<x-crud-modal.form>` and `<x-crud-modal.delete>`.
     - Refactored `roles.blade.php` to use `<x-crud-modal.form>` for 4 modals and `<x-crud-modal.delete>` for 2 modals.
     - Refactored `backups.blade.php` to use self-contained `<x-table.action-delete :action-url="..." :confirm-message="...">`.
  4. **Full Trilingual Localization Audit & Cleanup (Rule 17):**
     - Deduplicated all 12 repeated keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
     - Added missing translation keys (`Form`, `Create`, `Create New User`).
     - Standardized dictionary sorting and validated 100% key parity (366 keys in each language, 0 missing, 0 duplicates).
  5. **Automated Verification:**
     - 183/183 tests pass (708 assertions).
     - Code style validated via `vendor/bin/pint`.
- **Consequences:** Drastic reduction in Blade template size (-44% in users, -64% in roles), zero duplicated modal boilerplate, unified UX and accessibility across CRUD operations, and guaranteed 100% trilingual dictionary consistency.

---

## [ADR-034] Activity Log Forensic Description Dynamic Localization Engine
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The forensic audit trail (`activity_log`) records historical event descriptions in English (`Created new system user 'John'`, `Auto-pruned 100 records from table 'notifications' ...`, `User has been created`, etc.). In the Activity Log Explorer and Recent Activities Stream, these descriptions were displayed as raw unlocalized English text, causing language fragmentation for Arabic and French administrators.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Prompted developer with mandatory Rule 13 question (*"هل هذه الإضافة تنتمي إلى هذا الملف/القسم الأمني السري أم لا؟"*), receiving explicit authorization.
  2. **Centralized Architectural Service Layer (`SystemTableService::translateActivityDescription`):**
     - Engineered a pattern-matching localization engine parsing dynamic parameterized activity descriptions via regex (`user`, `role`, `permission`, `data pruning`, `database backup` operations).
     - Bound static event descriptions directly through Laravel's dictionary lookup (`__($description)`).
     - Built safe fallback guaranteeing that unmapped or third-party log strings gracefully return their original text without throwing errors.
  3. **Data Layer Preparation & Blade Isolation (Strict Separation of Concerns):**
     - Maintained zero business logic in Blade templates.
     - Injected pre-translated `$activity->translated_description` via `LengthAwarePaginator::through()` in `getActivityLogs()` and `Collection::map()` in `getRecentActivities()`.
     - Rendered `{{ $act->translated_description ?? $act->description }}` in `resources/views/system/activity-log.blade.php` (table and changes modal) and `resources/views/system/index.blade.php`.
  4. **Strict Trilingual Localization Parity (Rule 17):**
     - Added and synchronized 21 new activity translation keys with placeholder interpolation across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (387 keys in each language, 0 missing, 0 duplicates).
  5. **Automated Verification:**
     - Created feature tests in `tests/Feature/SystemTableTest.php` covering localized activity descriptions in Arabic, French, and English, and multi-locale service translation.
     - Entire application test suite passes with 100% success (185/185 tests, 722 assertions).
- **Consequences:** Forensic activity descriptions are now displayed with native multilingual clarity in Arabic, English, and French, while the database records remain untampered and forensically consistent.

---

## [ADR-035] Backup Storage Test Suite Namespace Isolation
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** An issue was identified where database backup files in "Available Database Backups" (`resources/views/system/backups.blade.php`) intermittently disappeared without any deletion action or activity log entry. Investigation revealed that `tests/Feature/DatabaseBackupTest.php` had been configured to resolve `$this->backupDir` against the default application backup folder (`storage/app/private/Laravel`) and executed `File::cleanDirectory()` during test runs. Whenever automated tests executed, all real user snapshots were wiped clean.
- **Decision:**
  1. **Test Namespace Isolation:**
     - Overrode `backup.backup.name` inside `DatabaseBackupTest::setUp()` with a dedicated, isolated test folder name (`TestingBackup`).
     - Pointed `$this->backupDir` strictly to `storage/app/private/TestingBackup`.
  2. **Safe Tear-Down Cleanup:**
     - Updated `tearDown()` to remove only the `TestingBackup` directory (`File::deleteDirectory($this->backupDir)`), strictly leaving the real application backup folder `storage/app/private/Laravel` untouched.
  3. **Verification:**
     - Ran full test suite (185/185 passing) and verified with filesystem inspection that real snapshots remain preserved and persistent.
- **Consequences:** Permanent protection for production and user-generated backup archives against automated test runs, eliminating unexpected disappearance of snapshots.

---

## [ADR-036] Action Buttons Unified Component Suite & Form Method Isolation
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** An audit of row action buttons across system explorer views revealed two concerns: (1) in `backups.blade.php`, ad-hoc raw `<a>` and `<button>` elements were used for download and restore operations instead of standard `<x-table.action-*>` components; (2) in `<x-table.action-delete>`, specifying `type="delete"` alongside `type="submit"` caused an HTML attribute collision where `$type` defaulted to `'submit'`, inadvertently applying default Indigo styling, eye icon, and `<button type="button">`, which prevented form submission.
- **Decision:**
  1. **Disambiguation of Action Props:**
     - Separated semantic theme/icon type (`$type`) from native HTML button element type (`button-type="submit"|"button"`).
     - Enhanced `<x-table.action.blade.php>` to accept `$buttonType` and pass it directly to `<button type="{{ $buttonType }}">`.
  2. **Dedicated Standardized Action Components:**
     - Created `<x-table.action-download>` (Indigo Info theme with download archive SVG).
     - Created `<x-table.action-restore>` (Amber Warning theme with counter-clockwise rotation restore SVG).
  3. **Double-Click & Idempotency Safeguards:**
     - Enhanced `resources/views/system/backups.blade.php` with Alpine.js state guards (`isCreating`, `isRestoring`) and loading spinners to prevent concurrent backup creations or race conditions during point-in-time state restores.
  4. **Strict Trilingual Localization Parity (Rule 17):**
     - Added localized strings for `Download`, `Restore`, `Creating Backup...`, and `Restoring Database...` in `lang/ar.json`, `lang/en.json`, and `lang/fr.json`.
- **Consequences:** 100% adherence to Rule 14 & Rule 15; elimination of attribute collision bugs in inline deletion forms; complete visual and functional consistency across all action buttons.

---

## [ADR-037] Automated Table Discovery & Dynamic Permission Matrix Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Managing system permissions manually via hardcoded seeds or static lists creates maintenance bottlenecks, risks missing permissions when new business entities are added, and offers poor UX when assigning permissions to roles through flat lists of checkboxes. An enterprise-grade, zero-maintenance dynamic permission architecture was required.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Received explicit developer authorization for adding the introspection engine and matrix within the private system security module.
  2. **Pure Live Schema Introspection Engine (`App\Services\PermissionDiscoveryService`):**
     - Introspects the live database schema via `Schema::getTables()` with zero hardcoded business tables.
     - Handles MySQL multi-database scoping: filters by `$tableInfo['schema'] === $currentDatabase` to prevent discovering tables from other databases on the same host, with fallback for SQLite in-memory testing.
     - Enforces an immutable `SYSTEM_BLACKLIST` filtering 17 internal/forensic tables (`migrations`, `sessions`, `cache`, `jobs`, `activity_log`, Spatie RBAC tables).
     - Discovers exclusively physical business tables present in the active schema (currently `users`).
  3. **Standard CRUD Generator, Dual Auto-Sync & Stale Pruning:**
     - Generates 4 standard permissions for each discovered entity: `view {entity}`, `create {entity}`, `edit {entity}`, `delete {entity}` using `Permission::firstOrCreate(['name' => ..., 'guard_name' => 'web'])`.
     - **Stale Permission Pruning (`pruneStaleCrudPermissions`):** Automatically detects and purges obsolete CRUD permissions for tables that no longer exist in the schema (e.g. phantom `instruments` legacy seeds), keeping the permissions catalog completely clean and in sync with reality.
     - Automatically synchronizes all permissions to the `Super-Admin` role via `$role->syncPermissions(...)` and clears Spatie permission cache via `app(PermissionRegistrar::class)->forgetCachedPermissions()`.
     - **Real-Time Auto-Sync:** Integrated into `getGroupedPermissionMatrix(autoSync: true)` so that simply opening the Roles & Permissions dashboard (`/system-tables/roles`) automatically generates any missing permissions for newly created business tables without manual intervention.
     - **CLI Sync:** Exposed via Artisan command `php artisan permissions:sync-tables [--dry-run]`.
  4. **Dynamic Grid Matrix UI & Collapsible Catalog (`resources/views/system/roles.blade.php`):**
     - Replaced flat permission lists in Create Role and Edit Role modals with an interactive, responsive table grid (`<x-crud-modal.form max-width="3xl">`).
     - Entity rows with View, Create, Edit, Delete columns.
     - 1-click Row Toggle ("تحديد الكل") per entity.
     - Global "Select All" and "Deselect All" master toggles.
     - Seamless integration with Alpine.js `x-model` array binding and native POST/PUT submission (`name="permissions[]"`).
     - **Collapsible Permissions Catalog:** Encapsulated the raw permissions catalog table into a clean on-demand card hidden by default (`showPermissionsCatalog`), positioned prominently above `Configured Roles`. It features an automated engine status badge, robust RTL/LTR icon margins (`me-3.5`), and an Alpine toggle button allowing administrators to expand it only when direct permission inspection, creation, or deletion is required.
  5. **Anti-Lockout Defense (Dual-Layer):**
     - **Controller Level:** In `SystemTableController::destroyRole()` and `updateRole()`, requests targeting `Super-Admin` are blocked with HTTP redirect and translated error message (*"The Super-Admin role is protected and cannot be modified or deleted."*).
     - **UI Level:** In `roles.blade.php`, action buttons for `Super-Admin` are suppressed and replaced with a prominent locked system badge (`<span class="..."><svg /> {{ __('Protected System Role') }}</span>`).
  6. **Trilingual Localization Parity (Rule 17):**
     - Added 17 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (408 keys each, 100% parity, 0 missing).
  7. **Comprehensive Automated Verification:**
     - Unit tests in `tests/Unit/PermissionDiscoveryServiceTest.php` (7 tests, 50 assertions).
     - Feature tests in `tests/Feature/SyncTablePermissionsCommandTest.php` (3 tests, 13 assertions).
     - Feature tests in `tests/Feature/SystemTableTest.php` covering anti-lockout protection and view rendering.
     - Full application test suite: 197/197 tests passing, 795 assertions.
- **Consequences:** New database tables automatically gain standard CRUD permissions with zero developer intervention; dropped tables have their permissions pruned automatically; roles are managed intuitively through an interactive matrix; raw permissions are neatly tucked away and accessible on demand; the Super-Admin role is permanently protected from accidental lockout.

---

## [ADR-038] User Account Status Lifecycle & Profile Photo Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** To enhance user governance, account security, and personalization, the system requires an explicit account lifecycle mechanism (Active vs Suspended) rather than relying solely on email verification timestamps. In addition, users require support for profile photos and file integrity hashes (`profile_photo_path` and `photo_hash`), quick status toggles from the System Explorer, security guards preventing suspended logins and self-suspension, and full filterability.
- **Decision:**
  1. **Schema Expansion (`database/migrations/2026_09_09_083203_add_status_and_photo_fields_to_users_table.php`):**
     - Added `status` (`varchar(32)`, default `'active'`, indexed) positioned after `email`.
     - Added `profile_photo_path` (`varchar(2048)`, nullable) positioned after `remember_token`.
     - Added `photo_hash` (`varchar(64)`, nullable) positioned after `profile_photo_path`.
  2. **Type-Safe Account Status Enum (`app/Enums/AccountStatus.php`):**
     - Strongly typed string enum with cases: `Active = 'active'` and `Suspended = 'suspended'`.
     - Provides localized UI labels (`label()`), semantic color tokens (`color()`), and badge CSS classes (`badgeClass()`).
  3. **User Model Integration (`app/Models/User.php`):**
     - Casts `'status' => AccountStatus::class`.
     - Fillable attributes: `status`, `profile_photo_path`, `photo_hash`.
     - Registered in `$filterable` for dynamic querying via `FilterableTrait`.
     - Helper methods: `isActive(): bool`, `isSuspended(): bool`, `getProfilePhotoUrlAttribute(): ?string`.
  4. **Authentication Boundary Guard (`app/Http/Requests/Auth/LoginRequest.php`):**
     - Intercepts authentication attempts: if a user is marked as `Suspended`, the session is rejected, rate limiters are cleared, and a localized `ValidationException` is thrown: *"Your account is suspended. Please contact the administrator."*
  5. **Background Photo Hash Generation & Lifecycle (`app/Observers/UserObserver.php`):**
     - Automatically generates `photo_hash` (`hash('sha256', ...)`) in the background via the `saving(User $user)` observer hook whenever `profile_photo_path` is provided or dirty.
     - Automatically clears `photo_hash` to `null` if the profile photo is removed.
     - Completely omitted from user-facing inputs and form requests to ensure strict system-level integrity.
  6. **Explorer UI & Photo Picker Suite (`resources/views/system/users.blade.php`):**
     - **Interactive Photo Picker Button & Preview:** Standardized `<x-secondary-button>` ("Choose Photo" / "اختيار صورة") triggering native device file picker, displaying instant round avatar preview, file name, and remove/clear action.
     - **Dual Input Capability:** Admins can either choose an image file directly from their device or enter a custom path/URL.
     - **Multipart Form Support (`crud-modal/form.blade.php`):** Prop `enctype="multipart/form-data"` added to the unified modal form suite to seamlessly handle file uploads.
     - **Storage & Cleanup (`SystemTableController`):** Uploaded files are securely stored to `storage/app/public/photos`; old photos are automatically purged from disk when updated or removed.
     - **Semantic Status Badges:** Emerald badge for Active, Rose badge for Suspended.
     - **Row Action Quick Toggle:** Interactive toggle button in table row actions with safety confirmation dialog and instant status update.
     - **Status Filter:** Added status filter dropdown (`All`, `Active`, `Suspended`) to the table toolbar.
  7. **Trilingual Localization Parity (Rule 17):**
     - Synchronized 7 new translation keys across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (428 keys each, 100% parity, 0 missing).
  8. **Comprehensive Automated Verification:**
     - `tests/Feature/Auth/AuthenticationTest.php`: `test_suspended_users_cannot_authenticate`.
     - `tests/Feature/SystemTableTest.php`: `test_admin_can_toggle_user_account_status`, `test_admin_cannot_suspend_their_own_account`, `test_users_explorer_can_filter_by_account_status`, `test_clearing_profile_photo_path_clears_photo_hash`, `test_authenticated_admin_can_upload_photo_file_for_user`, `test_authenticated_admin_can_remove_photo_via_edit`.
     - Total tests: 206 passing, 842 assertions.
- **Consequences:** Account status is strictly managed and audited; photo selection is intuitive with native file picking and instant previews; uploaded files are safely stored and purged upon change; photo hashes are generated seamlessly in the background; 100% test coverage and translation parity maintained.

---

## [ADR-039] Fluid Full-Width Container Architecture for High-Density Tabular Data
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Previous layout structures utilized Tailwind's `max-w-7xl` (`1280px`) container constrained with `mx-auto`. In multi-column views—especially in the System Tables Explorer which features a 280px left/right sidebar navigation menu alongside wide relational tables (such as users, sessions, backups, pruning, queues, activity logs, and permission matrices)—the effective width allocated to table content was reduced to ~936px. This caused severe horizontal column crowding, unwanted text clipping, and reduced readability on modern high-resolution displays (1080p, 1440p, 4K).
- **Decision:**
  1. **User Consultation & Strategy Selection:**
     - Prompted the developer regarding container width strategy: (A) Fluid 100% Full Width, (B) Ultra-wide fixed (`max-w-screen-2xl` / 1536px), or (C) Dynamic toggle.
     - Developer explicitly approved **Option A: 100% Fluid Full Width (`w-full`)** across all application views.
  2. **Standard Container Token Adoption:**
     - Standardized on `<div class="w-full px-4 sm:px-6 lg:px-8">` across all top-level layout wrappers and view templates.
     - Preserves responsive lateral breathing room (`px-4` on mobile, `sm:px-6` on tablet, `lg:px-8` on desktop) while ensuring the interface dynamically expands to fill 100% of the viewport width.
  3. **Universal Layout Synchronization:**
     - Base Layouts: `resources/views/layouts/app-ltr.blade.php` and `resources/views/layouts/app-rtl.blade.php`.
     - Navigation Headers: `resources/views/layouts/navigation-ltr.blade.php` and `resources/views/layouts/navigation-rtl.blade.php`.
     - System Views: `users.blade.php`, `roles.blade.php`, `queues.blade.php`, `pruning.blade.php`, `notifications.blade.php`, `index.blade.php`, `cache.blade.php`, `backups.blade.php`, `activity-log.blade.php`.
     - General Views: `dashboard.blade.php`, `profile/edit.blade.php`.
  4. **Modals Scoping:**
     - Maintained focused max-width boundaries on dialog modals (`max-w-md`, `max-w-lg`, `max-w-2xl`, `max-w-3xl`) to ensure centered readability without stretching forms across extreme desktop viewports.
  5. **Automated Verification & Asset Compilation:**
     - Recompiled production frontend assets via `npm run build` (Vite 8.2.2).
     - Verified entire PHPUnit test suite: 206/206 tests passing, 842 assertions.
- **Consequences:** Tables, matrices, and administrative explorers now receive full desktop viewport width (~1576px on standard 1080p screens, gaining +640px horizontal space). Multi-column tables no longer crowd or squeeze columns, and responsive layouts remain completely fluid and mobile-friendly.

---

## [ADR-040] Super Roles Authorization Bypass & Anti-Lockout Governance Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The application previously relied on a hardcoded check for `'Super-Admin'` in several controller actions, views, and sync services. Furthermore, users with the super role still depended on having specific permission records explicitly present in the database to pass authorization checks. The developer requested a clean, standardized property `protected array $superRoles = ['...']` with Arabic documentation annotations (*"الأدوار الخارقة التي تتجاوز كل الصلاحيات"*) to centrally define super roles, bypass all permission checks via Laravel's authorization Gate, and protect these roles from modification or deletion.
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Prompted developer with mandatory Rule 13 question and received explicit confirmation.
     - Confirmed placement in `AppServiceProvider` and `PermissionDiscoveryService` with default `['Super-Admin']`.
  2. **Configurable Property Standard:**
     - Defined `protected array $superRoles = ['Super-Admin'];` in `App\Providers\AppServiceProvider` and `App\Services\PermissionDiscoveryService`.
     - Provided `getSuperRoles(): array` and `isSuperRole(string $roleName): bool` on `PermissionDiscoveryService`.
  3. **Universal Authorization Gate Bypass (`Gate::before`):**
     - In `AppServiceProvider::boot()`, registered `Gate::before(function ($user, string $ability) { return (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($this->superRoles)) ? true : null; })`.
     - Automatically grants all ability and permission checks to users holding any super role without requiring database permission checks.
     - Safely returns `null` for non-super users so standard Spatie permissions and Laravel policies continue to evaluate normally.
  4. **Dynamic Schema Synchronization Integration:**
     - In `PermissionDiscoveryService::syncSuperAdminPermissions()`, iterates across all roles in `$this->superRoles` to ensure they exist and inherit all discovered CRUD permissions.
     - Updated `SyncTablePermissionsCommand` to output the full list of synchronized super roles.
  5. **Dynamic Anti-Lockout Enforcement & UI Integration:**
     - Replaced hardcoded string checks in `SystemTableController::updateRole()` and `destroyRole()` with `$this->permissionDiscoveryService->isSuperRole($role->name)`.
     - Passed `$superRoles` to `resources/views/system/roles.blade.php`, dynamically rendering the locked system role badge (`Protected System Role`) for all super roles and suppressing edit/delete actions.
     - Added `isSuperAdmin(): bool` helper method to `App\Models\User`.
  6. **Automated Verification:**
     - Extended `tests/Feature/RoleAndPermissionTest.php` with tests verifying `Gate::before` bypass for super roles, failure of bypass for normal users, and `isSuperAdmin()` helper.
     - Extended `tests/Unit/PermissionDiscoveryServiceTest.php` with super roles configuration tests.
     - Entire application test suite passing cleanly: 210/210 tests passing, 851 assertions.
- **Consequences:** Super roles now possess true authorization bypass capabilities across the entire application; new super roles can be configured simply by modifying `$superRoles`; the anti-lockout defense dynamically protects all configured super roles; 100% test coverage and documentation integrity are maintained.

---

## [ADR-041] System Tables High-Security Quarantine & Super-Admin Exclusivity Lock
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Previously, the System Tables Explorer routes were protected only by `auth` and `verified` middleware, and the navigation link was visible to any logged-in user. The developer requested to completely close, lock down, and hide the System Tables module from non-super-admin users ("غلق System Tables وحجبه تماماً").
- **Decision:**
  1. **Strict Security Quarantine (Rule 13 Compliance):**
     - Prompted developer with mandatory Rule 13 question and received explicit confirmation.
     - Confirmed strategy: restrict access strictly to `Super-Admin` via middleware, and cloak the navigation link so it is completely hidden from non-super users.
  2. **Route Middleware Hardening:**
     - Attached Spatie's `role:Super-Admin` middleware to the `/system-tables` route group in `routes/web.php`.
     - Guests are redirected to `login`; any authenticated user without `Super-Admin` role is blocked with HTTP `403 Forbidden`.
  3. **Navigation Cloaking (Desktop & Mobile):**
     - In `resources/views/layouts/navigation-ltr.blade.php` and `resources/views/layouts/navigation-rtl.blade.php`, wrapped `<x-nav-link>` and `<x-responsive-nav-link>` with `@if(Auth::user()?->isSuperAdmin() || Auth::user()?->hasRole('Super-Admin'))`.
     - Regular users, admins, and members have zero visibility of the System Tables link in the interface.
  4. **Factory & Test Verification:**
     - Added `superAdmin()` state to `Database\Factories\UserFactory`.
     - Added automated security boundary tests in `tests/Feature/SystemTableTest.php` proving that regular users receive 403 Forbidden on all system table endpoints and cannot see the link in the dashboard, while Super-Admin users have full access.
     - Full application test suite passing: 212/212 tests passing, 864 assertions.
- **Consequences:** The entire forensic and system administration suite is now completely dark and inaccessible to anyone other than Super-Admin accounts.

---

## [ADR-042] Pruning Audit Trail Dynamic Operation Localization
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** In the Data Pruning dashboard (`/system-tables/pruning`), the "Pruning Audit Trail" table displayed English activity descriptions in the "Operation" column (e.g. `Updated automated data pruning settings`, `Reset automated data pruning settings to defaults`, custom table additions/removals, and auto-pruned executions). While the column header was translated via `__('Operation')`, the table cells rendered raw `$item->description` strings without localization. The developer requested full localization of this column ("هنا Pruning Audit Trail نريد ترجمة العمود Operation").
- **Decision:**
  1. **Strict Separation of Concerns (Rule 12 Compliance):**
     - Rather than injecting service method calls or translation logic inside `resources/views/system/pruning.blade.php`, transformed the collection in `SystemTableController::pruningSettings()`:
       ```php
       $history = $this->dataPruningService->getPruningHistory(10)->map(function ($item) {
           $item->translated_description = $this->systemTableService->translateActivityDescription($item->description);
           return $item;
       });
       ```
     - Keeps Blade views clean, declarative, and focused solely on markup.
  2. **View Binding:**
     - In `pruning.blade.php`, updated the table cell to:
       ```blade
       <span>{{ $item->translated_description ?? $item->description }}</span>
       ```
  3. **Localization Dictionary Parity:**
     - Verified that all pruning activity message patterns already exist with high-quality translations in `lang/ar.json`, `lang/en.json`, and `lang/fr.json`:
       - `Updated automated data pruning settings` -> `"تم تحديث إعدادات التقليم التلقائي للبيانات"` / `"Paramètres d'élagage automatique des données mis à jour"`
       - `Reset automated data pruning settings to defaults` -> `"تمت استعادة إعدادات التقليم التلقائي للبيانات إلى الإعدادات الافتراضية"` / `"Paramètres d'élagage automatique des données réinitialisés par défaut"`
       - `Added custom table ':table' to automated data pruning`
       - `Removed custom table ':table' from automated data pruning`
       - `Auto-pruned :total records from table ':table' (Date: :date, Capacity: :capacity)`
  4. **Automated Test Verification:**
     - Added `test_pruning_audit_trail_operation_column_displays_translated_descriptions` in `tests/Feature/PruningSettingsTest.php` asserting that Arabic and French locale requests render translated operation text.
     - Full application test suite passing: 213/213 tests, 868 assertions.
- **Consequences:** The Pruning Audit Trail table is now 100% localized in Arabic, French, and English, maintaining architectural consistency and strict separation of concerns.

---

## [ADR-043] User Dropdown Navigation Border Radius & Polished Margins Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** In both navigation headers (`navigation-ltr.blade.php` and `navigation-rtl.blade.php`), the user dropdown trigger was styled as a raw, flat button (`border border-transparent text-gray-500 rounded-md`), creating a stark visual dissonance with the adjacent Theme and Language switchers which feature defined borders, elevation shadows, and backgrounds (`border border-gray-300 dark:border-gray-600 shadow-sm`). The developer requested adding border radius with a small margin on the edges ("هنا نريد اضافة بوردور ريديو مع هامش صغير على الحواف").
- **Decision:**
  1. **Harmonized Action Toolbar Button Standard:**
     - Styled the user settings button to match the theme and language switcher components:
       ```blade
       <button class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
       ```
     - Provides clear borders, `rounded-lg` border radius, subtle elevation shadow, and smooth hover/dark-mode transitions.
  2. **Dynamic User Avatar Integration:**
     - Incorporated profile photo rendering directly inside the navigation trigger button: displays `img` with `h-5 w-5 rounded-full object-cover ring-1` if `profile_photo_path` is uploaded, or a stylized initial badge (`h-5 w-5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 text-xs font-semibold`) if none exists.
  3. **Dropdown Container & Menu Items Refinement:**
     - Upgraded `<x-dropdown>` container from `rounded-md` with flush items to `rounded-lg shadow-lg p-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10`.
     - Refined `<x-dropdown-link>` with `rounded-md px-3 py-2 font-medium hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400`. The `p-1` container padding introduces an elegant margin between the hover highlight and the outer dropdown border.
  4. **Compilation & Verification:**
     - Compiled assets with `npm run build` (Vite 8.2.2).
     - Verified entire PHPUnit test suite: 213/213 tests passing, 868 assertions.
- **Consequences:** Navigation action buttons now form a cohesive, visually balanced top-right toolbar across both LTR and RTL layouts; user avatars are displayed natively; dropdown menus feel modern with clean radius and spacing.

---

## [ADR-044] Google-Style Account Popover Card Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The developer requested updating the user dropdown menu to match the iconic Google Account profile popover ("نريد تحديث القائمة المنسدلة للمستخدم مثل قائمة غوغل"). Specifically, the user confirmed preference for a full Google Account card layout with a circular avatar in the navbar, a large centered avatar with photo upload badge, user email and greeting, role/status badges, the signature oval pill "Manage your Account" button, quick navigation shortcuts, and a Google-style sign out footer.
- **Decision:**
  1. **Navbar Circular Trigger:**
     - Replaced text-based dropdown trigger with an interactive round avatar circle (`w-9 h-9 rounded-full ring-2 ring-gray-200 dark:ring-gray-700 hover:ring-orange-500 p-0.5 transition-all`).
     - Renders live uploaded profile photo or high-contrast stylized initial badge.
  2. **Google Card Component Structure (`<x-dropdown width="80">`):**
     - Extended `<x-dropdown>` to support `width="80"` (320px) with `rounded-2xl shadow-xl`.
     - **Email Header:** Truncated user email at the top.
     - **Hero Section:** Large centered avatar circle (`w-20 h-20 ring-4 shadow-md`) with an interactive camera badge overlay linking to photo editing in `profile.edit`.
     - **Personalized Greeting & Badges:** `Hi, :name!` with role badge (`Super-Admin` shield badge or assigned role) and active account status pill (`AccountStatus::badgeClass()`).
     - **Signature Google Pill Button:** Centered oval pill button `Manage your Account` (`rounded-full px-5 py-2 border shadow-sm`).
     - **Quick Links Section:** Clean shortcuts to Dashboard and System Tables.
     - **Footer Action Bar:** Google-style `Log Out` button with door/exit icon in a subtle contrasted footer card.
  3. **Mobile Drawer Harmony:**
     - Enhanced responsive navigation headers in mobile drawers with circular user avatar integration across LTR and RTL layouts.
  4. **Enum Enhancement:**
     - Added `badgeClass(): string` method to `App\Enums\AccountStatus` providing semantic badge styling for active and suspended states.
  5. **Trilingual Dictionary Parity (Rule 17):**
     - Synchronized 3 new translation keys (`Manage your Account`, `Change Photo`, `Hi, :name!`) across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (431 keys each, 100% parity, 0 diffs).
  6. **Automated Verification:**
     - Verified frontend asset compilation (`npm run build`).
     - Verified entire PHPUnit test suite: 213/213 tests passing, 868 assertions.
- **Consequences:** The application now features an authentic, polished Google-style account popover card matching modern web standards; user profile photo and status are prominently highlighted; 100% test coverage and translation parity maintained.

---

## [ADR-045] Dropdown Viewport Bounds & Logical RTL Positioning Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Following the introduction of the wider 320px (`w-80`) Google-style profile popover card, the dropdown was found to overflow the page viewport to the left in Arabic/RTL layouts ("القائمة المنبثقة تخرج خارج الاطار في الصفحة").
- **Root Cause:** In `navigation-rtl.blade.php`, `<x-dropdown align="left">` was passed. In `dropdown.blade.php`, `'left'` was mapped directly to `start-0`. Because Tailwind compiles `start-0` to `right: 0` in RTL, the 320px dropdown anchored its right edge to the trigger button situated at the far left edge of the screen, causing the card body to project 260px into negative off-screen coordinates.
- **Decision:**
  1. **Logical Alignment Standardization:**
     - In `dropdown.blade.php`, updated `$alignmentClasses` so that both `'right'` and `'end'` (as well as default) map to `ltr:origin-top-right rtl:origin-top-left end-0`.
     - In Tailwind, `end-0` compiles to `right: 0` in LTR (expanding leftward towards page center) and `left: 0` in RTL (expanding rightward towards page center).
  2. **View Harmonization:**
     - In `navigation-rtl.blade.php`, changed `<x-dropdown align="left">` to `<x-dropdown align="right">`, matching `navigation-ltr.blade.php`.
  3. **Viewport Overflow Safeguard:**
     - Added `max-w-[calc(100vw-2rem)]` to `<x-dropdown>`'s floating container to mathematically guarantee that the menu never exceeds the visible viewport width on any screen.
  4. **Compilation & Verification:**
     - Recompiled production assets via `npm run build` (Vite 8.2.2).
     - Verified full test suite: 213/213 tests passing, 868 assertions.
- **Consequences:** The Google Account popover now opens inward towards the center of the page in both LTR and RTL layouts with zero overflow or clipping across all viewports.

---

## [ADR-046] Streamlined User Dropdown Navigation Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** Following the implementation of the Google-style user profile popover card, the developer requested removing the redundant "Dashboard" and "System Tables" navigation buttons from inside the popover card ("في القائمة المنبثقة يوجد زرين [Dashboard] [System Tables] احذف هذا الجزء لانه موجود فعليا في الشريط العلوي"), as both destinations are already permanently visible and accessible in the primary application navigation bar.
- **Decision:**
  1. **Elimination of Redundant Quick Links:**
     - Removed the middle `<!-- Quick Links (Middle Section) -->` block from both `resources/views/layouts/navigation-ltr.blade.php` and `resources/views/layouts/navigation-rtl.blade.php`.
  2. **Cohesive Card Hierarchy & Focus:**
     - The Google Account popover card now maintains a singular focus on user identity, profile management, and session control:
       - Header & Large Hero Avatar with live camera edit badge.
       - User Name, Email, Role badges, and Account Status badge.
       - Signature Oval Pill Button (`Manage your Account`) linking to `profile.edit`.
       - Direct transition to the contrasted bottom footer with the Google-style `Log Out` button.
  3. **Verification & Asset Recompilation:**
     - Recompiled frontend assets via `npm run build`.
     - Code formatting verified via `vendor/bin/pint --dirty --format agent`.
     - PHPUnit test suite confirmed: 213/213 tests passing, 868 assertions.
- **Consequences:** Cleaner, more compact user profile popover with zero UI duplication; seamless visual flow directly to account management and sign out.

---

## [ADR-047] Standardized Semantic Color Hierarchy & Unified Design Token Architecture
- **Date:** 2026-09-09
- **Status:** Accepted / Active
- **Context:** The developer requested a rigorous review and unification of all application colors, establishing a carefully selected list based on category and exact usage, while strictly unifying styles across views to eliminate randomness and visual dissonance ("يجب مراجعة الالوان و تعيين قائمة مختارة بعناية و حسب الصنف و الاستعمال و يجب ايضا ان توحد الستايل لتجنب العشوائية").
- **Decision:**
  1. **Six Curated Semantic Color Categories:**
     - **Primary / Brand (Safety Orange):** Platform core identity, primary action buttons (`<x-primary-button>`), active toggles, and high-priority focus badges.
     - **Success / Positive (Emerald Green):** Active user statuses, completed jobs, healthy automated engines, and positive confirmations.
     - **Danger / Destructive (Rose Red):** Suspended user accounts, failed jobs, permanent deletions, and error banners.
     - **Warning / Cautionary (Amber Yellow):** Protected system roles, retries, caution notices, and hold states.
     - **Info / Forensic (Indigo Blue):** Metrics, technical counters, audit logs, caches, atomic locks, payloads, and discovered permissions (systematically replacing ad-hoc `blue` classes to prevent color drift).
     - **Neutral / Structural (Cool Gray):** Container cards, borders, dividers, secondary action buttons, and secondary descriptions.
  2. **Standardized Reusable Blade Components:**
     - `<x-badge>` (`resources/views/components/badge.blade.php`): Polymorphic badge supporting all 6 semantic variants, dual sizing (`sm`, `md`), and optional status dot (`:dot="true"`) with animated ping pulse (`:dot-ping="true"`).
     - `<x-alert>` (`resources/views/components/alert.blade.php`): Standardized, accessible flash message banner featuring variant-mapped SVG icons and smooth Alpine.js dismissal transitions.
  3. **Universal View Modernization:**
     - Refactored `users.blade.php`, `roles.blade.php`, `queues.blade.php`, `pruning.blade.php`, `cache.blade.php`, `backups.blade.php`, `notifications.blade.php`, `activity-log.blade.php`, and `index.blade.php` to use the unified component and token system.
     - Standardized container cards across `dashboard.blade.php` and `profile/edit.blade.php` to `rounded-xl border border-gray-100 dark:border-gray-700/60 shadow-sm`.
  4. **Verification & Asset Recompilation:**
     - Production assets compiled via `npm run build` (Vite 8.2.2).
     - Code formatting validated via `vendor/bin/pint --dirty --format agent`.
     - Full PHPUnit test suite passing: 213/213 tests, 868 assertions.
     - 100% trilingual key parity maintained across `ar.json`, `en.json`, and `fr.json` (432 keys each, 0 diffs).
     - Enhanced `<x-badge>` slot layout to guarantee direct flex child inheritance for icons and text.
- **Consequences:** Eliminates visual drift and ad-hoc CSS classes; guarantees institutional consistency and predictable user affordances across the entire platform.



