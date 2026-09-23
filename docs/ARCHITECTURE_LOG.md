# Architecture Log: ENGI-GMTM Core Kernel (Point Zero)

This document tracks foundational architectural patterns, engineering decisions, and conventions adopted across the lifecycle of this application kernel.

> [!NOTE]
> The full historical backlog of past iterations (ADR-001 through ADR-048) prior to Point Zero Core Kernel extraction is preserved in `docs/archive/legacy_ARCHITECTURE_LOG.md`.

---

## [ADR-053] Documentation Token Optimization & Streamlining Overhaul
- **Date:** 2026-09-23
- **Status:** Accepted / Implemented
- **Context:**
  The `docs/` catalog had grown to ~265 KB (>65,000 input tokens per pre-flight read). Compounding this was an aggressive instruction in `AGENTS.md` mandating that every agent read `project_state.md`, `changelog.md`, and `ARCHITECTURE_LOG.md` in full before every turn. In addition, `ARCHITECTURE_LOG.md` contained 9 separate visual micro-ADRs (ADR-022 through 030) for CSS icon color changes, and `project_state.md` Section 8 duplicated hundreds of lines of standing rules already in `AGENTS.md`. This was consuming tens of thousands of tokens unnecessarily on every agent turn.
- **Decision:**
  1. **Smart Pre-Flight Context Protocol:** Updated `AGENTS.md` to require targeted context reads (reading `project_state.md` for active system state, and only checking domain-relevant ADRs or recent changelog entries on demand rather than bulk-reading all files on every turn).
  2. **Proportional Post-Flight Protocol:** Always append to `changelog.md`; only update `project_state.md` when models, schema, routes, or commands change; record ADRs only for true architectural decisions, preventing micro-ADR proliferation for UI tweaks and bug fixes.
  3. **ADR Consolidation:** Consolidated visual micro-ADRs into unified thematic records:
     - `ADR-016..020`: Business Modules UI Harmonization, Terminology & Metric Grid Standardization.
     - `ADR-022..030`: Industrial Green Design System Harmonization for Tool Icons & Navigation.
  4. **Changelog Historical Archiving:** Archived releases `v1.0.0` through `v1.0.35` into `docs/archive/core_kernel_archive.md`, reducing active `changelog.md` from 90 KB to 29 KB (~68% reduction).
  5. **Project State Streamlining:** Replaced 160 lines of duplicate rules in `project_state.md` Section 8 with a concise 30-line architectural reference pointing to `AGENTS.md` as the single source of truth, reducing file size from 60 KB to 36 KB (~40% reduction).
  6. **Gatekeeper Table Flexibility:** Made the legacy migration Gatekeeper comparison table conditional on ambiguous or non-standard legacy identifiers rather than blocking obvious 1-to-1 mappings.
- **Consequences:**
  Achieved a total documentation size reduction of >100 KB (>25,000 tokens saved per context cycle), drastically speeding up agent comprehension and eliminating token wastage while preserving 100% of architectural and technical requirements.

---

## [ADR-052] Calibration Curves Subsystem Deprecation & Total Purge (Metrology Domain)
- **Date:** 2026-09-23
- **Status:** Accepted / Implemented
- **Context:** The metrology domain originally included calibration curves (correction and uncertainty time-series) backed by `CalibrationChartService` and an `<x-metrology-curves>` Chart.js component across equipment and certificate views. The developer decided to completely purge this feature and any associated prompt explanations to simplify the codebase, eliminate chart calculation overhead, and save token context.
- **Decision:**
  1. **Complete Service Purge:** Deleted `app/Services/CalibrationChartService.php`.
  2. **Component & View Purge:** Deleted `resources/views/components/metrology-curves.blade.php`. Removed curves tab buttons, panels, and Alpine defaults from `resources/views/metrology/equipment/show.blade.php` and `resources/views/metrology/certificates/show.blade.php`.
  3. **Controller Cleanup:** Removed `CalibrationChartService` imports, constructor injections, and chart data calculations from both `MetrologyController` and `CalibrationCertificateController`.
  4. **Test Cleanup:** Deleted obsolete `tests/Feature/Metrology/CalibrationChartFeatureTest.php`.
- **Consequences:** Completely removes the curves service, Blade component, controller injections, and AI prompt overhead from the project. All metrology tests pass cleanly.

---

## [ADR-051] Quantities & Units Service Modernization, Data Integrity Safeguards & Physical Dimensions Registry
- **Date:** 2026-09-22
- **Status:** Accepted / Implemented
- **Context:** Metrological physical quantities and units (`grandeurs`) define the fundamental dimensional standards (e.g. Pressure in bar/mbar, Temperature in °C, Current in mA, Voltage in V) utilized across equipment calibration, instrument capabilities, and verification reports. In the legacy application, logic was procedural in `GrandeurController` without clean repository abstraction or safeguards against deleting quantities linked to active equipment.
- **Decision:**
  1. **Clean Architecture Isolation:** Implemented `GrandeurRepositoryInterface` / `GrandeurRepository` and domain business logic in `GrandeurService`.
  2. **Data Integrity Safeguards:** Enforced strict deletion guards in `GrandeurService::deleteGrandeur()` that verify whether any active equipment or instrument specifications depend on the targeted physical quantity, throwing a domain exception to prevent referential integrity corruption.
  3. **Strict FormRequests & Enums:** Validated all inputs through `StoreGrandeurRequest` and `UpdateGrandeurRequest` with backing enum `GrandeurType` (`measurement`, `source`) providing semantic badge variants.
  4. **Componentized Explorer UI:** Rebuilt `metrology/units.blade.php` with 4-card KPI counter grid, `<x-global-filter>` with query persistence, `<x-table>`, and Alpine.js interactive modals.
  5. **Trilingual Localization:** Expanded translation dictionaries to 1122 keys with 100% parity across `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
- **Consequences:** Provides a secure, performant, and reliable physical dimensional standards catalog that underpins all metrology, calibration, and instrumentation operations across ENGI-GMTM Core Kernel.

---

## [ADR-050] Equipment Service Modernization, Metrology Capabilities & CAS Media Engine Integration
- **Date:** 2026-09-22
- **Status:** Accepted / Implemented
- **Context:** The legacy `app.gmtm-dz.com` application handled equipment via unnormalized tables (`equipements`, `grandeurs_physiques`), monolithic procedural scripts, lack of audit trails, non-standard naming conventions, and raw unprocessed image uploads.
- **Decision:**
  1. **Strict Domain Normalization:** Created 3 normalized tables (`grandeurs`, `equipment`, `equipment_specifications`) with strict foreign keys and soft deletes.
  2. **Laravel Standard Naming:** Migrated from `equipements` (French plural) to `equipment` (English Eloquent singular model), `grandeurs_physiques` to `grandeurs`, foreign key `equipment_id`.
  3. **Repository & Service Pattern:** Abstracted data access into `EquipmentRepositoryInterface` / `EquipmentRepository` and domain operations into `EquipmentService` with atomic database transactions.
  4. **CAS WebP Image Pipeline & PDF Storage:** Integrated `MediaOptimizationService` to automatically convert equipment photos into WebP with SHA-256 CAS hash deduplication and safe deletion upon asset modification.
  5. **100% Componentized UI & Trilingual Localization:** Built modern views using `<x-table>`, `<x-badge>`, and `<x-global-filter>` with zero inline styles, and expanded trilingual dictionaries to 1098 keys with 100% exact parity across AR, EN, and FR.
  6. **Data Migration with ID Preservation:** Imported all 29 historical equipment records and 39 physical specifications while strictly preserving primary IDs 1 to 29 to protect future foreign key integrity.
- **Consequences:** Provides a high-performance, maintainable, and audit-ready metrology equipment management service fully compliant with ENGI-GMTM Core Kernel standards.

---

## [ADR-049] Brand Identity Modernization (ENGI-GMTM) & Unified Favicon Architecture
- **Date:** 2026-09-22
- **Status:** Accepted / Implemented
- **Context:** The application brand is SARL GMTM ("Générale Maintenance Et Travaux Montage"). The legacy working moniker "ENGI-MATE" required harmonization to official corporate engineering branding "ENGI-GMTM", and the application lacked an active, unified browser favicon across its isolated RTL/LTR layout structure.
- **Decision:**
  1. **Brand Alignment:** Update moniker from "ENGI-MATE" to "ENGI-GMTM" across controllers, flash redirects, documentation, and the trilingual translation dictionary matrix (`lang/en.json`, `lang/ar.json`, `lang/fr.json`).
  2. **Favicon Harmonization:** Standardize favicon integration via `<link rel="icon" type="image/x-icon" href="{{ asset('images/LogoP.jpg') }}">` across all application layouts (`app-ltr`, `app-rtl`, `guest-ltr`, `guest-rtl`, `welcome`).
  3. **Multi-Path & Direct Probe Asset Resilience:** Mirror `LogoP.jpg` into `public/assets/image/` and replace the 0-byte `public/favicon.ico` to ensure zero broken icon requests across legacy paths and automatic browser probes.
- **Consequences:** Provides a polished, unified browser tab experience, eliminates 404s/empty icon loads, and achieves complete alignment with the GMTM corporate identity.

---

## [ADR-001] Point Zero Master Core Architecture & SaaS Starter Baseline
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Building complex enterprise software or multi-tenant SaaS applications requires an immutable, standardized architectural baseline to avoid repeating 3-6 weeks of infrastructure and setup work per new project.
- **Decision:** Establish this repository as the **Point Zero Core Kernel**, serving as the master foundation template. All derived applications inherit zero-touch database provisioning, enterprise RBAC, trilingual localization, and unified design components out-of-the-box.
- **Consequences:** Accelerates time-to-market for future projects from weeks to minutes while enforcing architectural consistency across all systems.

---

## [ADR-002] Strict Separation of Concerns & Clean Architecture
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Monolithic controllers that mix database queries, validation, and presentation lead to high technical debt, regression risks, and unmaintainable code.
- **Decision:** Enforce strict separation across distinct architectural layers:
  1. **Thin Controllers:** Solely handle HTTP requests, invoke FormRequests, and delegate execution to Actions or Services.
  2. **Dedicated FormRequests:** All input validation resides in `app/Http/Requests/`.
  3. **Actions & Services:** Single-action operations in `app/Actions/`; complex domain workflows in `app/Services/`.
  4. **Database Repositories:** Data access abstraction via `app/Repositories/`.
  5. **PHP 8.4 Strict Typing:** Every PHP file must declare `declare(strict_types=1);` and use explicit parameter and return types.
- **Consequences:** Ensures testability, loose coupling, and clean dependency injection throughout the lifecycle of the application.

---

## [ADR-003] Dual-Layer Database Provisioning, Self-Healing Auto-Migration & Schema Verification
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Connecting the application to a fresh, uninitialized, or missing database (e.g., editing `DB_DATABASE` in `.env`) previously threw fatal `QueryException` or `1049 Unknown database` errors.
- **Decision:** Implement `EnsureDatabaseIsMigrated` middleware with two resilient layers:
  1. **Layer 1 (Smart Database Auto-Creation):** Catches missing database exceptions, connects via raw PDO without a database specifier, and silently provisions the database (`CREATE DATABASE IF NOT EXISTS \`db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`), reconnecting immediately on the fly.
  2. **Layer 2 (Graceful Error Fallback View):** If the database cannot be reached or automatically created (e.g. MySQL server down, invalid credentials, or lack of privileges), catches the exception and renders `resources/views/errors/database.blade.php` with HTTP status `503 Service Unavailable`.
  3. **Auto-Migration & Seeding:** Inspects schema status and silently runs `Artisan::call('migrate', ['--force' => true])`, `Artisan::call('db:seed', ['--force' => true])`, and dynamic table CRUD permission discovery.
  4. **Performance Memoization:** Employs per-database persistent caching (`system_schema_migrated_{dbName}`) with 3600-second TTL and static in-memory memoization to ensure zero runtime penalty on subsequent requests.
- **Consequences:** Eliminates manual terminal intervention for database provisioning while providing a graceful diagnostic screen if the database server is offline.

---

## [ADR-004] Zero-State Super Admin Onboarding & Anti-Hijacking Route Sealing Gate
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Freshly provisioned databases contain no users. If anyone visits the site, there must be a seamless way to create the initial Super Admin, but this onboarding page must be strictly sealed immediately after account creation to prevent unauthorized takeovers.
- **Decision:** Implement `EnsureSuperAdminExists` middleware:
  1. **Zero-State Interception:** When `User::count() === 0`, intercepts all inbound web traffic (`/`, `/login`, `/register`, `/dashboard`) and redirects visitors directly to `/system-tables/setup`.
  2. **Anti-Hijacking Lockdown:** As soon as at least one user exists, the setup route permanently aborts with `404 Not Found` (GET) and `403 Forbidden` (POST), locking the setup wizard forever.
  3. **Immediate Authentication:** The created user is assigned `Super-Admin` role, verified email, logged in, and redirected directly to the dashboard.
- **Consequences:** Guarantees a smooth first-run onboarding experience while securing the root account from hijacking.

---

## [ADR-005] Native Trilingual Localization & Isolated RTL/LTR Asset Compilation
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Enterprise applications serving international or multilingual audiences require native support for Arabic (RTL), English (LTR), and French (LTR) without hardcoded strings, broken text direction, or visual flashing.
- **Decision:**
  1. **Zero Hardcoded Strings (Rule 17):** Every user-facing string is wrapped in `__('...')`.
  2. **1-to-1 Dictionary Parity:** Exact 1-to-1 key synchronization maintained across `lang/ar.json`, `lang/en.json`, and `lang/fr.json` (559 keys each, exactly 0 missing keys).
  3. **Isolated RTL/LTR Compilation:** Vite compiles separate entry bundles (`resources/css/app-rtl.css` and `resources/css/app-ltr.css`) ensuring zero visual flash (Zero-FOUC) and proper text alignment per locale.
- **Consequences:** Delivers a world-class, professional multilingual user experience across all three official application languages.

---

## [ADR-006] Unified Design System, Semantic Color Tokens & Zero Inline Styles
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Inconsistent UI elements, ad-hoc button colors, raw HTML tables, and inline `style="..."` attributes create visual degradation and maintenance nightmares.
- **Decision:**
  1. **Zero Inline Styles (Rule 12):** Strictly prohibit `style="..."` in Blade views; rely exclusively on Tailwind CSS utility classes and isolated CSS files.
  2. **Standardized Semantic Buttons (Rule 14):**
     - Primary: `<x-primary-button>` (Safety Orange: `bg-orange-500 hover:bg-orange-600`)
     - Secondary: `<x-secondary-button>` (Neutral bordered gray)
     - Danger: `<x-danger-button>` (Destructive Red: `bg-rose-600`)
     - Success: `<x-success-button>` (Emerald Green: `bg-emerald-600`)
     - Warning: `<x-warning-button>` (Amber Yellow: `bg-amber-500`)
     - Info: `<x-info-button>` (Indigo Blue: `bg-indigo-600`)
  3. **Standardized Table Suite (Rule 15):** Enforce `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, and `<x-table.action-*>` suite for all tabular data.
  4. **Zero-FOUC Theme Synchronizer:** Integrated Light, Dark, and System mode switcher backed by Alpine.js and localStorage.
- **Consequences:** Guarantees a cohesive, premium, and easily re-brandable design system across all screens and themes.

---

## [ADR-007] Enterprise RBAC, Automated Dynamic CRUD Permission Discovery & Anti-Self-Action Policy
- **Date:** 2026-09-10
- **Status:** Accepted (Partially Superseded by ADR-010 for static catalog discovery)
- **Context:** Managing permissions manually for dozens of database tables is error-prone. Administrators also need protection against accidental self-lockout or privilege loss.
> [!NOTE]
> Dynamic database schema introspection via `Schema::getTableListing()` originally introduced in this ADR was subsequently deprecated and replaced by the deterministic code-first static catalog in `config/permissions.php` under [ADR-010].
- **Decision:**
  1. **Spatie Roles & Permissions:** Baseline roles configured: `Super-Admin` (protected super role), `Admin`, `User` (default assigned to new registrants).
  2. **Automated Permission Discovery (`PermissionDiscoveryService`):** Scans the database schema, discovers application tables, dynamically creates standard CRUD permissions (`view`, `create`, `edit`, `delete`), and assigns them to `Super-Admin`.
  3. **Anti-Self-Action Security Policy:** Administrators cannot demote their own role, toggle their own account status to suspended/locked, or delete their own user account via dual-layer enforcement (controller guards and UI action masking).
  4. **Security Quarantine Module (Rule 13):** Private forensic dashboards (`system-tables.*`) are strictly quarantined from public user views.
- **Consequences:** Provides automated permission management, robust administrative safety guarantees, and defense-in-depth isolation.

---

## [ADR-008] Cached Dynamic System Settings Registry & Real-Time Registration Shield
- **Date:** 2026-09-10
- **Status:** Accepted / Implemented
- **Context:** Configuration flags (such as enabling/disabling public registration) need runtime modification by Super-Admins without code deployment, while maintaining zero latency on public traffic.
- **Decision:**
  1. **Persistent Key-Value Store (`system_settings` table):** JSON-cast values backed by persistent caching (`86400` TTL) and write-through cache refresh (`Cache::put()`) upon mutation.
  2. **Global Helpers:** `system_setting($key, $default)` and `is_registration_open()`.
  3. **Route Shield Middleware (`EnsureRegistrationIsOpen`):** Intercepts `/register` when disabled and redirects to `/login` with an alert, returning 403 for API/JSON calls.
  4. **Super Admin Reactive Switch:** Interactive Alpine.js toggle switch with bilingual knob alignment (RTL: open right / closed left; LTR: open left / closed right).
- **Consequences:** Delivers a zero-database-overhead configuration engine suitable for real-time feature toggles across the entire application.

---

## [ADR-009] Media Optimization, Profile Photo Storage & Zero Disk Space Leak Engine
- **Date:** 2026-09-11
- **Status:** Accepted / Implemented
- **Context:** Two interrelated challenges emerged: (1) In Laravel applications on Laravel Herd (Windows), uploaded files fail to serve over HTTP when `public/storage` is a physical directory instead of an NTFS junction, and incorrect `APP_URL` causes 403 errors. Client-side file preview using `FileReader` caused flickers. (2) Raw file uploads (10MB+ photos, 25MB+ PDFs) cause silent disk space exhaustion, degrade server I/O, inflate bandwidth, and retain sensitive EXIF metadata.
- **Decision:**
  1. **Storage Link Restoration:** Purge detached physical `public/storage` directory and establish an NTFS directory junction linking `public/storage` directly to `storage/app/public` via `php artisan storage:link`.
  2. **Domain Configuration Alignment:** Synchronize `APP_URL` in `.env` to `http://erp.gmtm-dz.com.test` to match Laravel Herd virtual host mappings.
  3. **Reactive Client-Side Preview:** Modernize the profile photo form using instantaneous `URL.createObjectURL(file)`, robust `x-show` bindings (eliminating `<template x-if>` race conditions), and graceful error fallback via `x-on:error`.
  4. **View Unification:** Canonical `$user->profile_photo_url` accessor across profile, navigation-rtl, and navigation-ltr views.
  5. **Mandatory Media Gateway (`MediaOptimizationService`):** All uploaded media must transit through `app/Services/MediaOptimizationService.php` configured via `config/media.php`.
  6. **Compulsory WebP Image Processing:** Force Intervention Image v4 with Imagick driver; convert all uploads to WebP (80% quality, 1920px max, EXIF strip), reducing disk footprint by >70%.
  7. **Ghostscript PDF Optimization:** Utilize `gs` via Symfony Process (`/ebook` profile, 150dpi, compatibility 1.4) with resilient fallback.
  8. **Content-Addressable Storage (CAS) & SHA-256 Deduplication:** Name stored files by their SHA-256 content hash (`{hash}.webp` / `{hash}.pdf`). Identical re-uploads match existing assets without creating redundant files.
  9. **Reference-Aware Safe Deletion:** `isAssetInUse()` and `safeDelete()` preserve files on disk while other records reference them; unlink only when reference count drops to 0.
  10. **Strict "Process & Destroy" Protocol:** Raw uploads received in `storage/app/temp-media`; optimized artifacts written to public storage; `destroyTempFiles()` immediately unlinks all temporary files via `@unlink()`, guaranteeing zero lingering artifacts.
- **Consequences:** Provides instantaneous client-side preview, zero HTTP 404/403 upload issues, and a centralized media processing engine that prevents disk exhaustion while enforcing automatic deduplication.

---

## [ADR-010] Code-First Static Permissions Registry & Schema Introspection Retirement
- **Date:** 2026-09-11
- **Status:** Accepted / Implemented
- **Context:** The system previously relied on dynamic database schema introspection (`Schema::getTableListing()`) to discover tables and auto-generate CRUD permissions. Whenever migrations introduced infrastructure or polymorphic tables, unwanted permissions were dynamically registered, polluting the RBAC matrix. The user mandated a deterministic, code-first static catalog.
- **Decision:**
  1. **Config-Driven Static Registry (`config/permissions.php`):** Define the permissions catalog strictly in PHP: `users_management`, `roles_management`, `permissions_management`.
  2. **Service Layer Modernization (`PermissionDiscoveryService`):** Abolished runtime `getTableListing()`; `getDiscoveredTables()` reads from `config('permissions.groups')`; auto-generates 4 CRUD permissions per entity; `pruneStaleCrudPermissions()` purges orphaned entries; `syncSuperAdminPermissions()` guarantees 100% coverage.
  3. **Console & UI Alignment:** Updated `php artisan permissions:sync-tables`; modernized `resources/views/system/roles.blade.php` with `Static Registry` badge; maintained trilingual parity (470 keys each).
  4. **Business Module Tabs Architecture (Extension):** Created dedicated Blade components (`<x-metrology-tabs>`, `<x-operations-tabs>`, `<x-analytics-tabs>`, `<x-master-data-tabs>`); created thin business controllers (`MetrologyController`, `OperationsController`, `AnalyticsController`, `MasterDataController`) with `Gate::authorize()`; registered all 4 modules and 17 sub-entities in `config/permissions.php`; replaced `href="#"` placeholders in navigation with named routes and `@can`/`@canany` gates; added 17 descriptive subtitles to trilingual dictionaries (610 keys).
- **Consequences:** Completely decouples permissions from the physical database schema, eliminates ghost permissions, and provides a cohesive enterprise-grade UI with strict RBAC across all business modules.

---

## [ADR-011] Unified English Localization Master Keys, Mandatory RBAC Protocol & Module Dashboards
- **Date:** 2026-09-11 / 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** Two recurring risks: (1) multilingual inconsistency from mixed-language translation keys; (2) new pages created without permission guards or static catalog registration. Additionally, individual module explorer pages lacked centralized executive dashboards and a consolidated workspace portal.
- **Decision:**
  1. **Unified English Master Key Standard:** Every `__('...')` call in code must use English as the canonical master key. Arabic/French appear only as values in `lang/ar.json` and `lang/fr.json`. 100% 3-file key parity enforced at all times.
  2. **Mandatory Permission Shield:** All new services, controllers, routes, or pages must be guarded by RBAC (`@can`, `Gate::authorize()`, `permission:...` middleware). Unprotected domain routes are strictly prohibited.
  3. **Mandatory Static Catalog Registration:** New entities must be declared in `config/permissions.php` and synced with `php artisan permissions:sync-tables`.
  4. **Mandatory Developer Clarification:** AI assistant must explicitly ask the developer for permission naming before writing code for new services.
  5. **Module Executive Dashboards:** Implemented 4 executive dashboards (`metrology/index.blade.php`, `operations/index.blade.php`, `analytics/index.blade.php`, `master-data/index.blade.php`). Upgraded `workspace.blade.php` into an executive portal. Registered dual-named route aliases. Synchronized navigation with active state matching and permission gates. Added 63 localized strings (671 keys).
- **Consequences:** Zero fragmentation in i18n, absolute security coverage, complete executive hierarchy from Workspace down to individual explorer tables.

---

## [ADR-012] Static System Roles Registry, Permission Matrix & Business Modules Boundary Realignment
- **Date:** 2026-09-11 / 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** Roles remained partially hardcoded in `PermissionDiscoveryService`. The role creation modal matrix only listed `Users`, omitting `Roles` and `Permissions`. The matrix header read `Table / Entity` (legacy schema introspection). Additionally, `Quantities & Units` and `Article Types` were in wrong modules.
- **Decision:**
  1. **Static Roles Registry (`config/permissions.php`):** Formalized `'roles'` catalog (`Super-Admin`, `Admin`, `User`) with localized titles, functional scopes, and behavioral flags (`is_super`, `is_default`). Service delegates `getSuperRoles()`, `getDefaultRole()` to config.
  2. **Static Permission Matrix Correction:** Replaced legacy `SYSTEM_BLACKLIST` with clean whitelist against `getDiscoveredTables()`; all 3 entities (`users`, `roles`, `permissions`) now render with full CRUD checkboxes.
  3. **UI Modernization:** `Table / Entity` → `Module / Entity` in role modals; `<x-badge variant="success">Static Registry</x-badge>` token on Configured Roles header. Normalized `SystemActivityAlert::$causer` to English `'System'`.
  4. **Business Modules Boundary Realignment:** `Quantities & Units` moved from `master-data` to `metrology` (`metrology.units`). `Article Types` moved from `master-data` to `operations` (`operations.article-types`). Static permissions catalog updated and synced. Tests updated (279 tests, 1201 assertions passing).
  5. **Trilingual Parity:** Maintained 100% key parity across AR/EN/FR (570–682 keys each).
- **Consequences:** Code-first static roles/permissions catalog; zero raw DB table references in UI; logical business domain boundaries across all modules.

---

## [ADR-013] Domain Module Categorization of RBAC Permission Matrices
- **Date:** 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** As the ERP grew to 24 configured business entities with 84 granular permissions, the permission assignment matrix in the System Role creation and editing modals (`Create New System Role` / `Edit System Role`) rendered entities in a flat, purely alphabetical sequence. This made it difficult for administrators to discern entity domains, locate related resources, or efficiently grant permissions per business module.
- **Decision:**
  1. **Domain Category Metadata Registry (`config/permissions.php`):**
     - Declared a static `'modules'` registry mapping 5 distinct domain categories:
       - `system`: System Security & Administration (Rose semantic token, `fa-shield-alt`)
       - `metrology`: Metrology & Equipments (Indigo semantic token, `fa-microscope`)
       - `operations`: Operations & Projects (Amber semantic token, `fa-briefcase`)
       - `analytics`: Internal and Analytical Management (Blue semantic token, `fa-chart-line`)
       - `master_data`: Master Data / Reference Data (Emerald semantic token, `fa-database`)
     - Assigned an explicit `'module' => '...'` key to all 24 entity definitions in `config/permissions.php`.
  2. **Categorized Service Aggregation (`PermissionDiscoveryService`):**
     - Extended `getGroupedPermissionMatrix()` to group entities under their parent module in `$matrix['modules']`, compiling per-module entity lists, CRUD action maps, and a consolidated `all_permissions` list per module.
     - Preserved existing keys (`'entities'`, `'custom'`, `'actions'`, `'all_names'`) to ensure 100% backward compatibility for all existing consumers.
  3. **Visual Isolation & Category Header Architecture (`resources/views/system/roles.blade.php`):**
     - Replaced flat entity iteration in both Create and Edit role modals with domain category grouping.
     - Created distinct category header rows featuring domain icons, color-coded badge tokens, entity counters, and a single-click "Toggle Category" button invoking Alpine.js selection (`toggleCreateEntityAll` / `toggleEditEntityAll`).
     - Sub-indented individual entity rows under their respective module headers with clear visual hierarchy.
     - Extended modal matrix scroll container height to `max-h-80` for better accessibility.
  4. **Strict Trilingual Localization Parity:**
     - Registered `System Security & Administration`, `Toggle Category`, and `entities` across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` maintaining 100% key parity at 691 keys each.
  5. **Automated Testing:**
     - Updated `PermissionDiscoveryServiceTest` asserting presence and integrity of structured module grouping.
- **Consequences:** Provides clear domain separation during role creation and configuration, eliminates administrative confusion when managing granular privileges, and provides convenient category-level bulk permission toggling.

---

## [ADR-014] Root Module-Level Access Permissions Integration in Category Headers
- **Date:** 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** Business domain modules (`Metrology`, `Operations`, `Analytics`, `Master Data`) have root-level view permissions (`view metrology`, `view operations`, `view analytics`, `view master data`) controlling access to the whole module's tabs and navigation. Previously, these were either treated as standalone pseudo-entities in `config/permissions.php` appearing as random rows, or disconnected from the category header, creating clutter and confusion.
- **Decision:**
  1. **Direct Module Configuration Integration (`config/permissions.php`):**
     - Embedded `view_permission` and `perms` array directly into each module definition inside `'modules'` (`'view_permission' => 'view metrology'`, etc.).
     - Removed redundant standalone `*_group` entries from `groups`.
  2. **Service Discovery & Sync Enhancements (`PermissionDiscoveryService`):**
     - Updated `getDiscoveredTables()` and `generateCrudPermissionsForTables()` to discover and register module root permissions so they are created and never pruned by `permissions:sync-tables`.
     - Updated `getGroupedPermissionMatrix()` to expose `view_permission` on every module item and prepend it to `all_permissions`, ensuring "Toggle Category" automatically includes or excludes the root module permission along with child entity permissions.
  3. **Dedicated Category Header Permission Row (`resources/views/system/roles.blade.php`):**
     - Added a stylized permission row directly underneath each domain category header in both Create Role and Edit Role modals.
     - Formatted with an icon, semantic badge color matching the module, an active checkbox bound to Alpine.js (`createRolePermissions` / `editRolePermissions`), and an explanatory subtitle label (`Module access permission`).
  4. **Trilingual Localization Parity:**
     - Registered `Module access permission`, `view metrology`, `view operations`, `view analytics`, and `view master data` across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (696 keys each, 100% 1-to-1 parity).
  5. **Verification & Testing:**
     - Verified all 279 automated tests (1205 assertions) pass with 100% success rate.
- **Consequences:** Clear, intuitive UI hierarchy in role creation modals where module-level access sits prominently at the top of each category, directly controlling the root module permission without cluttering the entity list.

---

## [ADR-015] Granular RBAC Gating on All Business Dashboard Metric & Action Cards
- **Date:** 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** While module dashboard routes (`/operations`, `/master-data`, `/metrology`, `/analytics`) are guarded by module-level permissions (`view operations`, `view master data`, `view metrology`, `view analytics`), child metric cards and action buttons directly link to specific explorer routes which require granular permissions (e.g., `view clients`, `view measuring instruments`, `view expenses`, `view missions`). Ungated cards displayed to users without corresponding permissions lead to 403 authorization rejections upon clicking.
- **Decision:**
  1. **Strict View-Level Permission Gating (`@can` / `@canany`):**
     - Wrapped every metric card across all 4 module dashboards (`operations/index.blade.php`, `master-data/index.blade.php`, `metrology/index.blade.php`, `analytics/index.blade.php`) in its respective `@can('view ...')` directive.
     - Wrapped composite overview cards in `@canany([...])` and gated individual action buttons.
  2. **Polite Zero-Permission Fallback Empty State:**
     - Provided a clean fallback state (`No Accessible Explorers`) explaining that domain entities are not yet authorized when a user only holds module-level access without child explorer permissions.
  3. **Trilingual Localization Parity:**
     - Synchronized translation keys for `No Accessible Explorers` and module-specific explanations across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (701 keys each, 100% 1-to-1 parity).
  4. **Automated Feature Testing:**
     - Implemented `test_operations_dashboard_cards_adhere_to_permissions`, `test_metrology_dashboard_cards_adhere_to_permissions`, `test_master_data_dashboard_cards_adhere_to_permissions`, and `test_analytics_dashboard_cards_adhere_to_permissions` in `tests/Feature/BusinessModulesTest.php` covering Super-Admin access, partial permission filtering, and zero-permission fallback states (283 tests, 1264 assertions passing 100%).
- **Consequences:** Zero dead links or 403 surprises from dashboard exploration across all business modules, maintaining clean security boundaries and consistent UX across all privilege levels.

---

## [ADR-016..020] Business Modules UI Harmonization, Terminology & Metric Grid Standardization
- **Date:** 2026-09-12
- **Status:** Accepted / Implemented
- **Summary:**
  - Standardized entity terminology (migrated "Article Types List" to "Classification of Articles" across views, permissions, and trilingual dictionaries).
  - Streamlined module dashboard navigation links by removing verbose prefixes in top navigation bars across desktop and mobile.
  - Standardized the primary dashboard metrics grid layout across all business modules to `grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4` for visual balance and responsive consistency.
  - Maintained 100% trilingual dictionary parity across Arabic, English, and French.

---

## [ADR-021] Modular Rich Tool & Page Icons Architecture
- **Date:** 2026-09-12
- **Status:** Accepted / Implemented
- **Context:** The application previously relied on simple monochrome linear stroke SVGs across metric cards and workspace portals. These lacked semantic differentiation, visual hierarchy, and functional expression for complex engineering tools and explorers.
- **Decision:**
  - Implemented a componentized micro-illustration system adhering to `docs/tool_icons_guide.md`.
  - Built 21 standalone, dedicated SVG Blade components located strictly in `resources/views/components/icons/tools/` for:
    - Operations: `missions`, `contracts`, `attachments`, `warranties`, `article-types`.
    - Metrology: `instruments`, `equipment`, `calibrator-movements`, `calibration-certificates`, `units`.
    - Analytics: `expenses`, `forecasts`, `statistics`, `reports`.
    - Master Data: `clients`, `employees`, `sites`.
    - Workspace Portals: `module-metrology`, `module-operations`, `module-analytics`, `module-master-data`.
  - Created polymorphic proxy component `<x-tool-icon name="..." />` (`resources/views/components/tool-icon.blade.php`) with dynamic component resolution and a resilient fallback SVG.
  - Enforced strict ID prefixing (`ms-`, `ct-`, `ins-`, `mod-met-`, etc.) on all SVG `<linearGradient>`, `<radialGradient>`, and `<filter>` tags to eliminate DOM collisions across pages.
  - Preserved strict separation of concerns: zero inline CSS (`style="..."`), purely utilizing Tailwind classes (`w-12 h-12 shrink-0 group-hover:scale-105 transition-transform duration-200`).
  - Added synchronized trilingual key `3 Explorers` (`3 مستكشفات` / `3 Explorateurs`) to maintain exact 1-to-1 dictionary parity (738 keys each).
- **Consequences:** Provides a visually rich, professional engineering SaaS aesthetic that intuitively communicates the function of each page/tool while maintaining modularity, fast rendering, and zero CSS debt.

---

## [ADR-022..030] Industrial Green Design System Harmonization for Tool Icons & Navigation
- **Date:** 2026-09-12 / 2026-09-13
- **Status:** Accepted / Implemented
- **Summary:**
  - Extended the rich micro-illustrations system to sidebar navigation tabs (`<x-metrology-tabs>`, `<x-operations-tabs>`, `<x-analytics-tabs>`, `<x-master-data-tabs>`) and explorer page headers/toolbars across all 17 business views.
  - Re-engineered domain icons for authentic engineering recognition: modeled measuring instruments as an explosion-proof industrial process transmitter, and calibrator movements as a Fluke 700G precision digital pressure calibrator with dynamic logistics motion trajectory.
  - Comprehensive palette standardization: unified tool icons and module emblems around the official Industrial Green / Emerald palette (`#065F46`, `#022C22`, `#047857`, `#059669`, `#10B981`, `#34D399`, `#A7F3D0`) while preserving isolated XML IDs and zero inline styles. (Detailed per-icon hex-stop iterations archived in `docs/archive/legacy_ARCHITECTURE_LOG.md`).

---

## [ADR-031] Employees Master Data Architecture, CAS Avatar Sync & Legacy Migration
- **Date:** 2026-09-13
- **Status:** Accepted / Implemented
- **Context:** The company needed a unified personnel registry under Master Data (`/master-data/employees`), linking employees with user accounts, maintaining financial confidentiality, preserving legacy records from `gmtm_app.employees`, and synchronizing employee profile photos with user avatars.
- **Decision:**
  1. **Clean Architecture:** Implemented `EmployeeRepositoryInterface`, `EmployeeRepository` with `with('user')` eager loading, and `EmployeeService` managing atomic transactions.
  2. **CAS Media Engine Integration:** Processed all avatar uploads into optimized `.webp` artifacts with SHA-256 content deduplication and reference-aware safe deletion via `MediaOptimizationService`.
  3. **Bidirectional Photo Synchronization:** Linked employee photos to user avatars dynamically (`$employee->profile_photo_url` falls back to user avatar; updating or creating an employee synchronizes the linked user's avatar path and hash).
  4. **Financial Quarantine:** Implemented `view employee compensation` policy restriction, masking salaries and daily rates (`•••••••• DZD`) for unauthorized personnel.
  5. **Legacy Data Migration:** Migrated 7 historical company records preserving original primary keys (1, 2, 3, 4, 5, 25, 26) via `LegacyEmployeeSeeder` and `employees:import-legacy`.
- **Consequences:** Ensures strict separation of concerns, robust security for sensitive compensation data, zero disk leaks, and zero regression across the enterprise application.

---

## [ADR-032] GMTM Corporate Logo Alignment & Aspect-Ratio Responsive Architecture
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:** The application previously retained legacy template logo assets, which caused missing 404 images under dark mode (`logo-dark.png` missing) and inconsistent visual branding for ERP-GMTM. Furthermore, GMTM's official logo features a wide banner aspect ratio (~2.35:1), which when placed inside fixed square containers (`w-14 h-14` or `w-24 h-24`) collapsed the visible height to less than 24px, degrading legibility.
- **Decision:**
  1. **Dual-Theme High-Fidelity Asset Pipeline:** Standardized `<x-application-logo>` to render official GMTM assets (`images/Logo-black.png` for Light Mode and `images/Logo-white.png` for Dark Mode), with `ERP-GMTM` alt fallback.
  2. **Zero-Breakage Legacy Mirroring:** Synchronized `Logo-black.png` to `logo.png` and `Logo-white.png` to `logo-dark.png` in `public/images/` to preserve backwards compatibility across existing caches or legacy components.
  3. **Aspect-Ratio Conscious Layout Sizing:** Refactored logo containers across `workspace.blade.php` (`h-14 w-auto shrink-0 max-w-[160px]`) and guest layouts (`guest-ltr.blade.php`, `guest-rtl.blade.php`: `h-20 w-auto max-w-[220px]`) to respect the 2.35:1 aspect ratio while maintaining responsiveness and seamless alignment with navigation bars (`navigation-ltr.blade.php`, `navigation-rtl.blade.php`: `h-12 w-auto sm:h-14`).
- **Consequences:** Provides crisp, legible corporate GMTM branding across all key application touchpoints in both light and dark themes with zero visual distortion or disk leaks.

---

---

## [ADR-034] Employee Position & Status Badge Architecture & Unified Single Source of Truth
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:** The employees table view (`master-data/employees.blade.php`) previously rendered position badges using a raw HTML `<span>` styled via `EmployeePosition::badgeClass()`, which injected hardcoded Tailwind classes (`bg-amber-50 ... dark:bg-amber-950/60`, `bg-cyan-50`, `bg-teal-50`) into the view. This violated three architectural rules: (1) mixing backend PHP enums with frontend CSS markup, (2) bypassing the centralized `<x-badge>` component ("Single Source of Truth"), and (3) lacking semantic classification for light and dark modes, causing high-contrast color clashes with status indicators.
- **Decision:**
  1. **Single Source of Truth Componentization (`<x-badge>`):** Mandated that all position and status labels in `employees.blade.php` must render through the unified `<x-badge>` component (`<x-badge :variant="...">`).
  2. **Hierarchical Semantic Classification (`badgeVariant()`):** Extended `EmployeePosition` with a `badgeVariant(): string` method classifying corporate positions into three clear functional categories:
     - **Management / Executive (`isManagement()`):** `primary` (official GMTM brand green token: `bg-brand-600/10 text-brand-800 dark:text-brand-300 border-brand-500/20`).
     - **Engineering Specialists (`isEngineer()`):** `info` (technical indigo token: `bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20`).
     - **Field Operations & Technicians (`isTechnician()`):** `neutral` (operational gray token: `bg-gray-500/10 text-gray-700 dark:text-gray-300 border-gray-500/20`).
  3. **Status Decoupling:** Added `badgeVariant()` to `EmployeeStatus` (`Active => 'success'`, `Inactive => 'danger'`, `OnLeave => 'warning'`) and rendered with `:dot="true"`, ensuring immediate, distinct visual contrast between job titles and operational status.
  4. **Form & Filter Design Token Harmonization:** Replaced ad-hoc `emerald-*` focus rings and avatar styles across employee filters, create modals, and table cells with unified brand tokens (`focus:border-brand-600 focus:ring-brand-600`, `bg-brand-600/10 text-brand-700`).
- **Consequences:** Restores full Separation of Concerns, eliminates CSS leaks in PHP enums, standardizes dark and light mode aesthetics through curated alpha tokens, and ensures 100% compliance with the enterprise UI design system.

---

## [ADR-035] Project-Wide Color Unification, Semantic Functional Classification & Token Harmonization
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:** Following the initial standardization of employee position badges (ADR-034), a full codebase audit was conducted across all domains to ensure project-wide conformity with the unified color source system (`resources/css/tokens.css`, `tailwind.config.js`, `<x-badge>`), 3-tier functional classification, decoupling of status indicators from category chips, and harmonization of dark mode presentation without opaque dark overrides (`dark:bg-*-950/60`).
- **Decision:**
  1. **PHP Enums Contract Uniformity:** Extended `App\Enums\AccountStatus` with `badgeVariant(): string` (`Active => 'success'`, `Suspended => 'danger'`) and modernized `badgeClass()` to emit alpha-transparency tokens (`bg-emerald-500/10`, `bg-rose-500/10`), establishing full parity across all 3 project Enums.
  2. **Elimination of Arbitrary "Rainbow UI" in Workspace:** Refactored module portal cards in `workspace.blade.php` to use `variant="neutral"` for all explorer counter chips (`5 Explorers`, `4 Explorers`, `3 Explorers`), removing inappropriate `danger`, `warning`, and `success` variants that created visual confusion with operational alarm states. Harmonized Super-Admin role badge to `primary` brand variant.
  3. **Domain Overview Metric Card Harmonization:** Replaced hardcoded `text-emerald-600 dark:text-emerald-400` metric sub-labels across Metrology, Operations, Master Data, and Analytics with unified corporate identity tokens (`text-brand-700 dark:text-brand-400 font-medium`).
  4. **Component Design Token Modernization:** Upgraded `.badge-*` utility classes in `resources/css/components.css` and table action themes in `<x-table.action>` to use alpha-transparency tokens (`bg-*/10`, `border-*/20`, `dark:bg-*/20`, `dark:border-*/30`) eliminating muddy, high-contrast dark overrides.
  5. **Quarantined System Module Alignment (Developer Approved):** Under explicit developer approval conforming to the mandatory security quarantine check, harmonized registration state pills and table chips across `system/users.blade.php`, `system/settings.blade.php`, and `system/roles.blade.php` to unified alpha tokens.
  6. **Automated Contract Testing:** Added `tests/Unit/EnumsBadgeContractTest.php` to permanently enforce type-safe `badgeVariant()` contracts and 3-tier functional role classification against regressions.
- **Consequences:** Eliminates ad-hoc color pollution across the codebase, provides a stunning, harmonious visual balance in both light and dark modes, and guarantees strict compliance with the project's living design system.

---

## [ADR-036] Mandatory Unified Search, Filter & State Persistence Architecture
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  When searching or filtering records on tabular pages (such as `/master-data/employees`), users previously lost their active search criteria and filter selections under standard lifecycle events:
  1. Switching interface language risked stripping active query parameters (`search`, `position`, `status`, etc.).
  2. Clicking on table pagination links (page 2, 3, etc.) stripped GET parameters because repository paginators were not chaining `->withQueryString()`.
  3. Creating a new record, updating a record, or deleting a record reset the view back to page 1 with all filters wiped out.
  4. Role/position dropdowns lacked functional grouping, displaying flat lists without semantic hierarchy.
  The developer mandated obligating the AI assistant to strictly adhere to a unified search, filter, and state persistence architecture across all lifecycle operations.
- **Decision:**
  1. **Strict AI Mandate Codification:** Enshrined the `Mandatory Unified Search, Filter & State Persistence Architecture` as a permanent non-negotiable rule into `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, and `.ai/rules/views.md`.
  2. **Single Source of Truth for Search & Filters (`<x-global-filter>`):** Mandated that `<x-global-filter>` is the authoritative standard for search bars, filters, and date pickers across all tabular pages. Ad-hoc search inputs or custom filter bars are strictly prohibited.
  3. **Functional Role Grouping (`<optgroup>`):** In all position filter dropdowns and create/edit modal selects, job positions must be grouped into 3 standardized functional tiers via `<optgroup>`:
     - `Management & Executive Leadership` (`isManagement()`)
     - `Engineering & Specialist Roles` (`isEngineer()`)
     - `Field Operations & Technicians` (`isTechnician()`)
  4. **Paginator Query State Persistence (`->withQueryString()`):** Chained `->withQueryString()` across repository paginator calls in `BaseRepository` (`paginate()`, `paginateWithFilter()`), `EmployeeRepository` (`paginateWithFilter()`), and `UserRepository` (`getVerifiedUsers()`).
  5. **CRUD Redirect State Persistence:** Updated modal form action URLs to pass `request()->query()`, and updated controller store, update, and destroy actions (`MasterDataController`) to redirect back preserving `$request->query()`.
  6. **Trilingual Dictionary Parity:** Added the 3 functional tier labels to `lang/en.json`, `lang/ar.json`, and `lang/fr.json` maintaining 100% 1-to-1 key parity (919 keys each, 0 missing).
  7. **Automated Feature Testing:** Created `tests/Feature/MasterData/EmployeeFilterPersistenceTest.php` testing pagination query retention, store redirect retention, update redirect retention, and delete redirect retention (4/4 passed).
- **Consequences:**
  Guarantees flawless UX across all tabular views where search queries, active filters, and pagination state are never unintentionally wiped out by language changes, page navigation, or record mutations.

---

## [ADR-037] Absolute 100% English Translation Master Keys Standard & Non-English Key Purge
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  The developer mandated obligating the AI assistant to write 100% of all translation keys strictly in the English language. A thorough audit of `lang/en.json`, `lang/ar.json`, and `lang/fr.json` revealed two legacy reverse-translation bridge keys (`"حذف مستخدم"` and `"مستخدم جديد"`) where Arabic was inappropriately used as the dictionary key (property name), creating a structural violation of the English master key standard.
- **Decision:**
  1. **Permanent AI Mandate Codification:** Enshrined the *Absolute Mandate for 100% English Translation Master Keys* across `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, and `.ai/rules/views.md`.
  2. **Zero-Tolerance Non-English Key Prohibition:** Strictly prohibited writing keys in Arabic, French, or any non-English language as dictionary keys or inside `__('...')` in code. Arabic and French text may ONLY exist as the translated values in `lang/ar.json` and `lang/fr.json`.
  3. **Dictionary Purge & Normalization:** Removed legacy reverse-translation entries (`"حذف مستخدم"` and `"مستخدم جديد"`) from `lang/en.json`, `lang/ar.json`, and `lang/fr.json`. Canonical English master keys `"New User"` and `"User Deleted"` now exclusively govern these translations.
  4. **Automated Continuous Regression Prevention:** Added `LocalizationTest::test_all_translation_keys_are_strictly_in_english` to the test suite, asserting that zero dictionary keys contain Arabic or non-English characters across all three language files.
- **Consequences:**
  Eliminates key pollution, ensures pristine 100% English master key consistency across the entire codebase, and permanently guards against the reintroduction of non-English translation keys.

---

## [ADR-038] Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  The developer mandated obligating the AI assistant to strictly adhere to a unified architecture for user messages, in-page alerts, and background notifications, with functional classification and trilingual translations across 3 languages (Arabic, English, French). Inconsistent alert containers, arbitrary flash keys, and unorganized notification scopes create UI fragmentation and accessibility issues.
- **Decision:**
  1. **Permanent AI Mandate Codification:** Enshrined the *Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity* architecture across `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/views.md`, and dedicated `.ai/rules/notifications.md`.
  2. **Single Source of Truth for In-Page Alerts (`<x-alert>`):** Standardized all in-page alerts, operational notices, and flash feedback on `<x-alert :variant="...">` using semantic tokens: `success` (Emerald Green), `danger` (Rose Red), `warning` (Amber Yellow), `info` (Indigo Blue), `primary` (Brand Green). Prohibited ad-hoc alert `<div>` containers.
  3. **Standardized Session Flash Keys:** Controllers must pass standardized flash keys (`with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, `with('info', __('...'))`), mapped directly to corresponding `<x-alert>` variants in views.
  4. **Unified Notification Architecture (`SystemActivityAlert`):** Enforced a structured notification payload schema (`title`, `message`, `type`, `causer`, `extra`) where `type` maps to semantic badge variants (`created` -> `success`, `updated` -> `warning`, `deleted` -> `danger`, `info` -> `info`).
  5. **Mandatory Functional Role Classification (التصنيف حسب الوظيفة):**
     - Scoped notification recipients by functional tiers: Management & Executive Leadership (`isManagement()`) for administrative/security notices to `Super-Admin`/`Admin`; Engineering & Specialist Roles (`isEngineer()`) for technical and calibration alerts; Field Operations & Technicians (`isTechnician()`) for logistics/field tasks.
     - Mandated that actor roles and positions inside notification details must use `<x-badge>` categorized into the 3 functional tiers (`primary`, `info`, `neutral`), while operational states (Read/Unread) use `:dot="true"`.
  6. **Mandatory 100% English Master Keys & Trilingual Parity:** Every alert message, notification title, and body must be authored in English as the master key in `__('...')`, and must be registered simultaneously across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with 1-to-1 parity.
  7. **Automated Feature Testing:** Added `test_system_notification_titles_have_exact_trilingual_parity` to `tests/Feature/DatabaseNotificationTest.php` ensuring all notification titles and actions exist with valid non-empty translations across all 3 languages.
- **Consequences:**
  Ensures a cohesive, professional alert and notification experience across light and dark themes, eliminates fragmented flash messaging, enforces strict role-based scoping, and preserves 100% trilingual dictionary parity.

---

## [ADR-039] Forensic Audit Trail Causer Identity & Search Integration
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  In the high-security audit trail explorer (`resources/views/system/activity-log.blade.php`), the `Causer` column previously displayed only plain unformatted text or email addresses (`$act->causer->name ?? $act->causer->email`), making actor identification Spartan and providing no immediate contact/identity context. Furthermore, the global table filter only searched event descriptions, log names, and subject types, preventing administrators from searching audit logs directly by actor name or email.
- **Decision:**
  1. **Developer Quarantine Verification (Rule 13):** Executed modification under explicit developer pre-approval conforming to the Mandatory Agent Check Question protocol.
  2. **Rich Causer Identity Presentation:** Upgraded the Causer table cell to display:
     - Avatar circle: User's profile photo or brand initials fallback avatar (`bg-brand-600/10 dark:bg-brand-600/20 text-brand-700 dark:text-brand-400`).
     - Primary line: Bold user name (`font-semibold text-xs text-gray-900 dark:text-white`).
     - Secondary line: User email address in subtle monospace typography (`font-mono text-[11px] text-gray-500 dark:text-gray-400`).
     - Fallbacks: Retained `ID: {causer_id}` for purged records and italicized `System` for automated / system events.
  3. **Polymorphic Actor Search (`whereHasMorph`):** Enhanced `SystemTableService::getActivityLogs()` to query `whereHasMorph('causer', [User::class], ...)` against user name and email.
  4. **Trilingual Dictionary Parity:** Added `"Search description, subject, user..."` across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (915 keys each with 1-to-1 parity).
  5. **Automated Feature Testing:** Added `test_activity_logs_explorer_displays_causer_name_and_email_and_supports_causer_search` to `tests/Feature/SystemTableTest.php` asserting that actor name, email, and causer filtering succeed reliably.
- **Consequences:**
  Provides instant, forensic visual clarity regarding which administrator or user executed each audit log mutation, accompanied by fast polymorphic actor filtering across the entire application history.

---

## [ADR-040] Forensic Audit Trail Trilingual Parity & Activity Description Localization Engine
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  A comprehensive audit of translation keys in `resources/views/system/activity-log.blade.php` and `SystemTableService::translateActivityDescription()` revealed unlocalized interface elements: raw string options in the event filter dropdown (`'Created'`, `'Updated'`, `'Deleted'`), an untranslated table header (`ID`), untranslated event badges (`{{ $act->event }}`), and missing translation keys for several automated activity log event descriptions (`Employee has been created`, `Employee has been updated`, `Employee has been deleted`, `Initialized system and created the initial Super Admin account`, `Changed registration status to enabled/disabled`, `Changed user status to ':status' for ':name'`, and `Updated system setting ':key'`).
- **Decision:**
  1. **Strict UI Localization:** Wrapped all filter dropdown labels in `__('...')` (`Created`, `Updated`, `Deleted`), wrapped the column header `<x-table.th>{{ __('ID') }}</x-table.th>`, dynamic event badges in `{{ __($act->event ?? 'event') }}`, and fallback causer IDs in `{{ __('ID') }}: {{ $act->causer_id }}`.
  2. **Activity Description Regex Expansion:** Added regex matchers to `SystemTableService::translateActivityDescription()` to capture user status changes (`Changed user status to ':status' for ':name'`), setting modifications (`Updated system setting ':key'`), and registration status transitions.
  3. **100% English Master Keys & Trilingual Synchronization:** Registered 16 new keys across `lang/en.json`, `lang/ar.json`, and `lang/fr.json`, maintaining exact 1-to-1 key parity (931 keys in each file, 0 missing, 0 non-English keys).
  4. **Automated Feature Testing:** Expanded `test_system_table_service_translates_activity_descriptions_accurately_across_locales` in `tests/Feature/SystemTableTest.php` to 25 assertions covering Employee events, User status updates, Settings changes, and Registration switches in Arabic, French, and English.
- **Consequences:**
  Eliminates unlocalized fragments across the forensic audit trail, guaranteeing full linguistic consistency and seamless readability across Arabic, French, and English interfaces.

---

## [ADR-041] Mandatory Comprehensive Ecosystem & Dependency Synchronization Upon Adding New Services or Pages
- **Date:** 2026-09-20
- **Status:** Accepted / Implemented
- **Context:**
  When adding new services (`app/Services/`) or new pages (`resources/views/`), AI agents frequently perform isolated edits without auditing directly connected dependencies. This resulted in missing translation dictionary keys, unlocalized flash notices, missing `<x-alert>` feedback, unhandled activity log patterns in audit trails, forgotten navigation links, and broken state persistence.
- **Decision:**
  1. **Permanent AI Mandate Codification:** Enshrined the *Mandatory Comprehensive Ecosystem & Dependency Synchronization Protocol* into `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/services.md`, `.ai/rules/views.md`, and `.ai/rules/index.md`.
  2. **Strict Incompletion Standard:** Creating or modifying any service or page is structurally prohibited from being marked complete until the agent systematically reviews and synchronizes all connected files:
     - **Trilingual Localization:** 100% English master keys in `__('...')` synchronized across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with exact 1-to-1 parity and 0 non-English keys.
     - **Alerts & Messages:** Standardized controller flash keys (`with('success')`, `with('error')`, `with('warning')`, `with('info')`) wired directly to `<x-alert>` components in views.
     - **Audit Trail Forensics:** Model and domain mutations recorded in `activity_log` (`causedBy`, `performedOn`, `properties`) with new description patterns registered in `SystemTableService::translateActivityDescription()`.
     - **Notifications:** Structured `SystemActivityAlert` payloads scoped by the 3 functional tiers (Management, Engineering, Technicians).
     - **Table & State Persistence:** Standardized `<x-global-filter>`, 3 functional `<optgroup>` tiers, `->withQueryString()`, and CRUD redirect persistence (`$request->query()`).
     - **Unified Design Tokens:** Standardized `<x-table>`, polymorphic action buttons, `<x-badge>` functional variants (`primary`, `info`, `neutral`), and zero inline styles.
     - **RBAC & Navigation:** Entity permissions declared in `config/permissions.php`, `@can` view guards, and sidebar/navigation tab integration.
     - **Automated Verification:** Feature tests, Laravel Pint formatting (`vendor/bin/pint --dirty --format agent`), and Living Memory updates.
- **Consequences:**
  Eliminates fragmented or half-baked feature implementations, ensuring that every new page or service added to the application is fully localized, alerted, audited, secured, filter-persistent, and thoroughly tested from day one.

---

## [ADR-042] Legacy Code Modernization & Migration Protocol (SARL GMTM Core Kernel)
- **Date:** 2026-09-21
- **Status:** Accepted / Implemented
- **Context:**
  The organization possesses a historical/legacy codebase characterized by spaghetti code, monolithic scripts, inline queries, non-standard database table naming (mixed plural/singular, non-snake_case, irregular foreign keys), and ad-hoc presentation styles. When porting features into SARL GMTM Core Kernel, there is a severe risk that agents copy or adapt legacy structural flaws, compromising the Clean Architecture, naming standards, UI design tokens, and database integrity of the new kernel.
- **Decision:**
  1. **Strict Enterprise Architect Persona & Business Logic Mining:**
     Agents are strictly forbidden from copying legacy architectural structures or styling. Their sole permissible task is extracting underlying business logic and mathematical rules.
  2. **Mandatory Laravel Eloquent Naming Standards:**
     - Models: Singular PascalCase (`User`, `Invoice`, `Mission`, `SparePart`).
     - Tables: Plural snake_case (`users`, `invoices`, `missions`, `spare_parts`).
     - Pivot Tables: Singular for both entities, sorted alphabetically, in snake_case (`mission_user`).
     - Foreign Keys: Singular entity identifier followed by `_id` in snake_case (`employee_id`).
  3. **Mandatory Gatekeeper Step — Naming Convention Fixes Table:**
     Before writing any executable code, the agent MUST generate a comparative table detailing legacy vs. standard names with rule-based justifications and await developer review.
  4. **Clean Architecture & Component Isolation:**
     Enforce ultra-skinny controllers, dedicated domain services, dedicated FormRequests, backed PHP Enums with `badgeVariant(): string`, standardized `<x-table>` and `<x-badge>` suites, zero inline styles, and 100% English master translation keys synchronized across AR, EN, and FR.
  5. **Data Migration & Foreign Key Integrity:**
     Enforce rigorous foreign key mapping and verification scripts to prevent orphan records or corrupted relations during legacy data transfer.
  6. **Sequential Execution Order:**
     Mandate a 9-step execution sequence from naming verification to translations.
- **Consequences:**
  Guarantees that all legacy feature ports into SARL GMTM Core Kernel are systematically sanitized, correctly named, decoupled into clean layers, and visually aligned with the enterprise design system.
