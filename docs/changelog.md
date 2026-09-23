# Changelog: ENGI-GMTM Core Kernel (Point Zero)

All notable changes, features, refactorings, and fixes for this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> [!NOTE]
> Detailed historical changelog prior to Point Zero Core Kernel extraction is preserved in `docs/archive/legacy_changelog.md`.
> Historical Core Kernel releases from `v1.0.0` through `v1.0.35` are archived in `docs/archive/core_kernel_archive.md`.

## [1.0.51] — 2026-09-23 — Calibration Curves Subsystem Total Purge
### Removed
- **`CalibrationChartService` (`app/Services/CalibrationChartService.php`)**: Fully deleted service class.
- **`<x-metrology-curves>` (`resources/views/components/metrology-curves.blade.php`)**: Fully deleted component.
- **Certificate Curves Tab (`resources/views/metrology/certificates/show.blade.php`)**: Removed curves tab button, panel, and set default `activeTab` to `'points'`.
- **`CalibrationCertificateController`**: Removed `CalibrationChartService` import, constructor injection, and `$chartData` view passing.
- **Test File (`tests/Feature/Metrology/CalibrationChartFeatureTest.php`)**: Deleted obsolete test suite.

---

## [1.0.50] — 2026-09-23 — System Prompt & Rule Harmonization for Token Economy

### Changed
- **`AGENTS.md` & `.antigravityrules` & `CLAUDE.md`**:
  - Enshrined the **Strict Token-Economy Response Directive**: prohibited echoing full document contents or large text dumps of `docs/` files in chat messages, mandating concise 3-5 line diff summaries instead.
  - Mandated **Targeted Line Slicing**: required using `grep_search` and slice notation (`StartLine`/`EndLine`) when inspecting documentation rather than bulk-reading entire files.
  - Synchronized `.antigravityrules` and `CLAUDE.md` to remove obsolete blanket 3-file pre-flight read requirements in favor of Smart Pre-Flight and Proportional Post-Flight.
  - Aligned legacy migration gatekeeper rules to prevent pipeline blocking on obvious 1-to-1 standard migrations.
- **`docs/project_state.md`**:
  - Bumped baseline version to `v1.0.50`.

### Added
- **`.ai/rules/documentation.md`**: Dedicated token-efficient documentation rule file covering Smart Pre-Flight, Proportional Post-Flight, and Token-Economy response standards for `docs/**`.
- **`.ai/rules/index.md`**: Registered path mapping for `docs/**` pointing to `.ai/rules/documentation.md`.

---

## [1.0.49] — 2026-09-23 — Documentation Token Optimization & Streamlining Overhaul

### Changed
- **`AGENTS.md` (Living Memory & Workflow Protocol)**:
  - Replaced the token-draining blanket instruction (reading 250 KB across 3 documents on every turn) with **Smart Pre-Flight (Targeted Context Reading)**: read `project_state.md` for active system topology, and consult specific ADRs or recent changelog entries on-demand based on task scope.
  - Instituted **Proportional Post-Flight Protocol**: always update `changelog.md`; only update `project_state.md` when schema/models/routes/commands change; restrict ADR entries to genuine architectural decisions, stopping the proliferation of micro-ADRs for visual/bug fixes.
- **`docs/project_state.md`**:
  - Streamlined Section 8 ("Standing Architectural Directives & Conventions") by replacing 160 lines of duplicated rules with a concise 30-line architectural pointer referencing `AGENTS.md`.
  - Reduced file size from 60 KB to 36 KB (~40% reduction, saving thousands of tokens per read).
  - Bumped active version to `v1.0.49`.
- **`docs/ARCHITECTURE_LOG.md`**:
  - Consolidated visual micro-ADRs into unified thematic records:
    - `[ADR-016..020]`: Business Modules UI Harmonization, Terminology & Metric Grid Standardization.
    - `[ADR-022..030]`: Industrial Green Design System Harmonization for Tool Icons & Navigation.
  - Added `[ADR-053]` documenting the documentation token optimization overhaul.
  - Reduced file size from 82 KB to 63 KB.
- **`docs/rules/legacy_migration_directive.md`**:
  - Made the Gatekeeper naming convention comparison table conditional on ambiguous, irregular, or non-standard legacy identifiers, preventing pipeline blocks on obvious standard migrations.
- **`docs/changelog.md`**:
  - Archived historical Core Kernel releases (`v1.0.0` through `v1.0.35`) into `docs/archive/core_kernel_archive.md`, reducing active changelog size from 90 KB to 29 KB (~68% reduction).

### Added
- **`docs/archive/core_kernel_archive.md`**: Dedicated archival record for historical releases `v1.0.0` through `v1.0.35`.

---

## [1.0.48] — 2026-09-23 — Documentation Harmonization & Architectural Directive Alignment

### Changed
- **`docs/project_state.md`**:
  - Harmonized active Database Engine identifier to `gmtmdz_erp` to match `.env` and migration specs.
  - Standardized trilingual dictionary key counts across the overview and standing rules to active parity baseline (1,237 keys each).
  - Updated Employees module cross-references from legacy numbers (`ADR-021..024`) to current canonical records (`ADR-031`, `ADR-034`).
  - Added 6 missing active console commands (`employees:import-legacy`, `equipment:import-legacy`, `customers:import-legacy`, `sites:import-legacy`, `warranties:import-legacy`, `metrology:check-expiring-certificates`) to the architectural components catalog.
  - Modernized `AccountStatus` enum rule specification to declare the unified `badgeVariant(): string` contract.
- **`docs/employees_migration_spec.md`**:
  - Updated system version to current baseline (`v1.0.48`).
  - Re-mapped historical ADR references (`ADR-021`, `ADR-023`, `ADR-024`) to current canonical architecture records (`ADR-031`, `ADR-034`).
- **`docs/rules/legacy_migration_directive.md`**:
  - Integrated dedicated Repositories & Interfaces (`app/Repositories/`, `app/Interfaces/`) into Clean Architecture Standards and the 10-step Sequential Execution Order.
- **`docs/ARCHITECTURE_LOG.md`**:
  - Updated `[ADR-007]` status to `Accepted (Partially Superseded by ADR-010 for static catalog discovery)` and appended an explicit note clarifying the deprecation of dynamic schema introspection in favor of the static code-first registry.

---

## [1.0.47] — 2026-09-23 — Calibration Curves Feature Removal (Metrology Domain)

### Removed
- **Calibration Curves Tab (`metrology.equipment.show`)**:
  - Removed the "Calibration Curves" tab button from the interactive tab navigation in `resources/views/metrology/equipment/show.blade.php`.
  - Removed the tab content panel that rendered `<x-metrology-curves :equipment="$equipment" :chartData="$curvesData" />`.
- **`CalibrationChartService` Dependency**:
  - Removed `CalibrationChartService` import and constructor injection from `MetrologyController`.
  - Removed `$curvesData` computation call (`$this->calibrationChartService->getEquipmentCurves($equipment)`) from `showEquipment()`.
  - Cleaned up `compact()` call to only pass `equipment`.

---

## [1.0.46] — 2026-09-22 — Quantities & Units Service Modernization (Metrology Domain)


### Added
- **Quantities & Units Clean Architecture (`metrology.units`)**:
  - Implemented `GrandeurRepositoryInterface` and `GrandeurRepository` in `app/Repositories/` providing eager-loaded specifications counts and dynamic filtering.
  - Implemented `GrandeurService` in `app/Services/` providing atomic transactions, KPI counter calculations, and strict data integrity guards preventing deletion of any physical quantity currently linked to equipment or instrument specifications.
  - Registered `GrandeurRepositoryInterface` in `RepositoryServiceProvider`.
- **Form Requests & HTTP Layer**:
  - Created `StoreGrandeurRequest` and `UpdateGrandeurRequest` in `app/Http/Requests/Metrology/` with enum validation (`GrandeurType`) and RBAC authorization (`create quantities units`, `edit quantities units`).
  - Added `storeUnit`, `updateUnit`, and `destroyUnit` to `MetrologyController`.
  - Registered RESTful CRUD routes in `routes/web.php` under `metrology.units.*`.
- **Modernized Blade Explorer (`resources/views/metrology/units.blade.php`)**:
  - 4-Card KPI Counter Grid (Total Quantities, Measurement Sensors / In, Source Generators / Out, Configured Specs).
  - Standardized `<x-global-filter>` with dynamic search and `GrandeurType` filter.
  - Data table `<x-table>` with semantic badges (`neutral` for symbol, `GrandeurType::badgeVariant()`), specification links counter, and unified `<x-table.actions>`, `<x-table.action-edit>`, and `<x-table.action-delete>`.
  - Alpine.js modal suite (Create, Edit, and Delete with integrity alert if linked).
- **System-Wide Action Buttons Standardization**:
  - Replaced legacy and ad-hoc `<button>` elements in table cells with standardized GMTM Blade components (`<x-table.action-edit>`, `<x-table.action-delete>`, `<x-table.action-view>`), guaranteeing visual, behavioral, and accessibility consistency across all application tables.
- **Trilingual Dictionary Synchronization**:
  - Synchronized 24 new translation keys with 100% parity across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (1122 keys each, 0 non-English keys).
- **Automated Feature Tests**:
  - Created `tests/Feature/Metrology/GrandeurFeatureTest.php` covering index view, KPI counters, create, update, delete guard against equipment usage, unlinked deletion, and unauthorized access (6 tests, 22 assertions, 100% passing).

---

## [1.0.45] — 2026-09-22 — Equipment Service Modernization & Legacy Migration (Metrology Domain)

### Added
- **Metrology Equipment Architecture & Domain Schema**:
  - Implemented 3 normalized migrations conforming to strict enterprise standards:
    - `2026_09_22_100000_create_grandeurs_table.php` (`grandeurs`: physical measurement quantities and symbols).
    - `2026_09_22_110000_create_equipment_table.php` (`equipment`: enterprise inventory tracking, internal code, serial number, category, package lot, status, calibration toggle, WebP photo, certificate path, CAS SHA-256 hash, and soft deletes).
    - `2026_09_22_120000_create_equipment_specifications_table.php` (`equipment_specifications`: normalized min/max ranges and precision tolerances linked to physical quantities).
- **Domain Enums (`app/Enums/`)**:
  - `EquipmentCategory`: `measuring_instrument`, `work_tool`, `vehicle`, `other` with `badgeVariant()` and `requiresCalibrationByDefault()`.
  - `EquipmentStatus`: `active`, `maintenance`, `deployed`, `retired`, `inactive` with semantic status badges.
  - `EquipmentPackage`: `lot_01`, `lot_02`, `vehicle_lot`, `none` for logistics grouping.
  - `GrandeurType`: `measurement`, `source` with semantic badges.
  - `AccuracyType`: `%`, `abs`.
- **Domain Service & Repository Pattern**:
  - `EquipmentRepositoryInterface` & `EquipmentRepository` implementing eager loading (`specifications.grandeur`) and `FilterableTrait`.
  - `EquipmentService` handling atomic DB transactions, CAS WebP image optimization and safe deletion, certificate PDF storage, specification range synchronization, and KPI counter statistics.
  - Registered `EquipmentRepositoryInterface` in `RepositoryServiceProvider`.
- **Form Requests & Validation**:
  - `StoreEquipmentRequest` & `UpdateEquipmentRequest` with enum validation, unique serial numbers ignoring soft deletes, file mimes validation (images up to 5MB, PDF up to 10MB), and dynamic specification rules.
- **Observers & CAS Deduplication**:
  - `EquipmentObserver` monitoring image updates to calculate SHA-256 CAS hash and triggering `MediaOptimizationService::safeDelete()` to prevent disk leakage.
- **Trilingual Localization Parity (1098 Keys)**:
  - 87 new English master translation keys introduced across forms, table headers, activity logs, and modals, synchronized with 100% key parity across `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
- **Enterprise Blade Views & Components**:
  - `resources/views/metrology/equipment.blade.php`: Modernized explorer with 4-card KPI counter grid, `<x-global-filter>` category/package/status filters, `<x-table>`, Alpine.js Create/Edit/Delete modals, and dynamic physical specification range inputs.
  - `resources/views/metrology/equipment/show.blade.php`: Tabbed details inspection page (Overview & Specs, Physical Capabilities, Certificate PDF viewer, and Spatie Forensic Audit Trail).
- **Automated Feature Testing**:
  - Created `tests/Feature/Metrology/EquipmentFeatureTest.php` covering index explorer, KPI counters, detail view, store with specs, update with specs sync, and soft delete (5 tests, 21 assertions, 100% passing).
- **Legacy Migration Command & Seeder**:
  - `ImportLegacyEquipmentCommand` (`php artisan equipment:import-legacy`) & `LegacyEquipmentSeeder`:
    - Successfully imported all 29 historical equipment records from `app.gmtm-dz.com` with primary keys (1–29) strictly preserved.
    - Converted and optimized all legacy images to modern `.webp` via `MediaOptimizationService`.
    - Synced 39 physical specifications across calibrated measuring instruments.

---

## [1.0.44] — 2026-09-22 — Brand Identity Modernization (ENGI-GMTM) & Unified Favicon Architecture

### Changed
- **Application Rebranding (`ENGI-MATE` -> `ENGI-GMTM`)**:
  - Renamed core application moniker from `ENGI-MATE` to `ENGI-GMTM` across project documentation, `README.md`, `SystemTableController`, and trilingual dictionaries.
  - Updated initialization status flash message to: `__('System initialized successfully! Welcome to ENGI-GMTM.')`.
  - Synchronized translation key with 100% parity across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with zero non-English keys.

### Added
- **Unified Favicon Architecture (`public/images/LogoP.jpg`)**:
  - Integrated GMTM Metrology & Instrumentation favicon `<link rel="icon" type="image/x-icon" href="{{ asset('images/LogoP.jpg') }}">` across all core layout views:
    - `resources/views/layouts/app-ltr.blade.php`
    - `resources/views/layouts/app-rtl.blade.php`
    - `resources/views/layouts/guest-ltr.blade.php`
    - `resources/views/layouts/guest-rtl.blade.php`
    - `resources/views/welcome.blade.php`
  - Replaced empty 0-byte `public/favicon.ico` with authentic GMTM brand asset to satisfy direct browser icon lookups.
  - Mirrored asset to `public/assets/image/LogoP.jpg` for legacy path backward compatibility.

---

## [1.0.43] — 2026-09-21 — Legacy Code Modernization & Migration Protocol (SARL GMTM Core Kernel)

### Added
- **Permanent Architectural Directive Codification (`AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/legacy-migration.md`, `.ai/rules/index.md`, `docs/rules/legacy_migration_directive.md`)**:
  - Enshrined the **Legacy Code Modernization & Migration Protocol** (Role: *Strict Enterprise Architect*):
    1. **Zero Spaghetti Mirroring:** Prohibits replicating legacy architecture, monolithic styles, or inline queries. Only business logic mining is permitted.
    2. **Laravel Eloquent Standards Enforcement:** Models singular PascalCase, Tables plural snake_case, Pivot tables singular alphabetical snake_case, Foreign keys singular_id.
    3. **Gatekeeper Step (Naming Convention Fixes Table):** Mandatory comparative table explaining all legacy vs. standard names before writing any executable code.
    4. **Clean Architecture Isolation:** Ultra-skinny controllers, dedicated domain services, dedicated FormRequests, and backed PHP Enums with `badgeVariant(): string`.
    5. **UI Component Architecture:** Exclusively GMTM Blade components (`<x-table>`, `<x-badge>`, `<x-*-button>`, `<x-global-filter>`, `<x-alert>`), zero inline styles, and 100% English master translation keys in code synchronized across AR, EN, and FR.
    6. **Foreign Key & Data Integrity:** Strict mapping preventing orphan rows, preserving soft deletes, timestamps, and audit trails.
    7. **Sequential Execution Order:** 9-step structured output sequence.
    8. **Mandatory Confirmation Gate:** Response trigger *"مستعد لتطبيق معايير التسمية القياسية"*.

---

## [1.0.42] — 2026-09-20 — Mandatory Comprehensive Ecosystem & Dependency Synchronization Architecture

### Added
- **Permanent AI Mandate Codification (`AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/services.md`, `.ai/rules/views.md`, `.ai/rules/index.md`)**:
  - Enshrined the **Mandatory Comprehensive Ecosystem & Dependency Synchronization Protocol**:
    Whenever the AI assistant creates, extends, or modifies any new service (`app/Services/`, Actions, Repositories, Jobs) or any new page/view (`resources/views/`, Controllers, Routes), the task is strictly prohibited from completion until all directly connected ecosystem layers are systematically verified and synchronized:
    1. Trilingual Localization Synchronization: 100% of user-facing strings, labels, errors, and activity descriptions extracted into English master keys and synchronized simultaneously across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (1-to-1 parity, 0 non-English keys).
    2. Alerts, Flash Messages & UI Feedback: Standardized controller flash keys (`with('success')`, `with('error')`, `with('warning')`, `with('info')`) paired with corresponding `<x-alert>` components in target views.
    3. Audit Trail Forensics & Activity Regex Engines: Logging mutations in `activity_log` (`causedBy`, `performedOn`, `properties`) and registering new activity description patterns in `SystemTableService::translateActivityDescription()` with regex matchers.
    4. Notifications & System Alerts: Structured payloads (`SystemActivityAlert`) scoped by the 3 functional tiers (Management, Engineering, Technicians).
    5. Table & Query State Persistence: Standardized `<x-global-filter>`, 3 functional `<optgroup>` tiers, and query string retention (`->withQueryString()`, `$request->query()`).
    6. Design System & Badges: Standardized `<x-table>`, polymorphic action buttons, `<x-badge>` functional variants (`primary`, `info`, `neutral`), and zero inline styles.
    7. RBAC & Navigation Hierarchy: Permission registration in `config/permissions.php`, `@can` view guards, and sidebar/navigation tab integration.
    8. Automated Verification & Living Memory: Feature test coverage, Pint code formatting, and post-flight living memory updates.

---

## [1.0.41] — 2026-09-20 — Forensic Audit Trail Trilingual Parity & Activity Description Localization Engine

### Added
- **Audit Trail UI Localization (`resources/views/system/activity-log.blade.php`)**:
  - Localized event filter selector options via `__('Created')`, `__('Updated')`, `__('Deleted')`.
  - Localized table column header `<x-table.th>{{ __('ID') }}</x-table.th>`.
  - Localized event badge chips dynamically via `{{ __($act->event ?? 'event') }}` rendering proper localized badges in Arabic (`إنشاء`, `تعديل`, `حذف`), French (`Créé`, `Modifié`, `Supprimé`), and English (`Created`, `Updated`, `Deleted`).
  - Localized causer fallback label `{{ __('ID') }}: {{ $act->causer_id }}`.
- **Activity Description Localization Engine (`app/Services/SystemTableService.php`)**:
  - Extended regex matchers in `SystemTableService::translateActivityDescription()` to handle:
    - User status changes: `Changed user status to ':status' for ':name'`.
    - System setting mutations: `Updated system setting ':key'`.
    - Registration status transitions: `Changed registration status to enabled` and `Changed registration status to disabled`.
- **Trilingual Dictionary Parity (`lang/en.json`, `lang/ar.json`, `lang/fr.json`)**:
  - Added 16 missing translation keys across all 3 languages (931 keys per language with 100% 1-to-1 parity and 0 non-English keys):
    - Activity descriptions: `Employee has been created`, `Employee has been updated`, `Employee has been deleted`, `Initialized system and created the initial Super Admin account`, `Changed registration status to enabled`, `Changed registration status to disabled`, `Changed user status to ':status' for ':name'`, `Updated system setting ':key'`.
    - Interface and badge keys: `ID`, `Updated`, `Deleted`, `event`, `active`, `suspended`, `enabled`, `disabled`.
- **Automated Verification (`tests/Feature/SystemTableTest.php`, `tests/Feature/LocalizationTest.php`)**:
  - Expanded `test_system_table_service_translates_activity_descriptions_accurately_across_locales` to 25 assertions covering Employee mutations, User status changes, System Settings, and Registration toggles across Arabic, French, and English.
  - Verified 100% test suite passage (306 tests, 1404 assertions).

---

## [1.0.40] — 2026-09-20 — Forensic Audit Trail Causer Identity & Search Integration

### Added
- **Causer Identity Integration (`resources/views/system/activity-log.blade.php`)**:
  - Enhanced the Causer column in the forensic audit trail table (`activity_log`) under developer pre-approval per Rule 13 (Strict Security Quarantine).
  - Displays user avatar (profile photo URL or brand initials chip) alongside both the User Name (in bold) and Email address (underneath in mono styling) for rich, unambiguous actor tracking.
  - Retains graceful fallback states: `ID: {causer_id}` if causer record was soft/hard purged, and italicized `System` badge when actions originate from system tasks, migrations, or unauthenticated processes.
- **Causer Search in Activity Logs (`app/Services/SystemTableService.php`, `resources/views/system/activity-log.blade.php`)**:
  - Extended `SystemTableService::getActivityLogs()` to support searching by causer user name and email via Eloquent's `whereHasMorph('causer', [User::class], ...)` alongside existing searches across `description`, `log_name`, and `subject_type`.
  - Updated filter search input placeholder to `__('Search description, subject, user...')` with synchronized trilingual entries in `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
- **Automated Feature Verification (`tests/Feature/SystemTableTest.php`)**:
  - Added `test_activity_logs_explorer_displays_causer_name_and_email_and_supports_causer_search` verifying that both actor name and email are rendered in the Causer cell and that searching by causer name and email properly isolates the relevant audit trail rows.

---

## [1.0.39] — 2026-09-20 — Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity

### Added
- **Strict Guidelines & Architecture Mandates (`AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/views.md`, `.ai/rules/notifications.md`)**:
  - Enshrined the **Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity Architecture**:
    1. Single Source of Truth for In-Page Alerts: All in-page alerts, operational notices, and flash feedback MUST strictly use `<x-alert :variant="...">` (`success`, `danger`, `warning`, `info`, `primary`). Standardized session flash keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, `with('info', __('...'))`.
    2. Unified Notification Architecture: System activity notifications (`SystemActivityAlert`) must adhere to structured payload schema (`title`, `message`, `type`, `causer`, `extra`), with `type` mapping to semantic badge variants.
    3. Mandatory Functional Role Classification: Notifications and alerts must be scoped by the 3 functional tiers: Management & Executive Leadership (`isManagement()`) for administrative/security alerts, Engineering & Specialist Roles (`isEngineer()`) for technical and calibration alerts, Field Operations & Technicians (`isTechnician()`) for logistics/field tasks. Role chips in notifications must use `<x-badge>` categorized into the 3 functional tiers (`primary`, `info`, `neutral`).
    4. Mandatory Trilingual Localization Across 3 Languages: 100% English master keys in `__('...')` synchronized across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with 1-to-1 parity.
- **Automated Feature Verification (`tests/Feature/DatabaseNotificationTest.php`)**:
  - Added `test_system_notification_titles_have_exact_trilingual_parity` verifying all system activity notification titles and actions exist in Arabic, English, and French dictionaries with non-empty translations.
- **Rule Infrastructure (`.ai/rules/notifications.md`, `.ai/rules/index.md`)**:
  - Created dedicated rule file `.ai/rules/notifications.md` covering `app/Notifications/**` and registered it in `.ai/rules/index.md`.

### Changed
- **Alert Component Token Harmonization (`resources/views/components/alert.blade.php`)**:
  - Corrected `info` variant text and hover classes to use dedicated indigo tokens (`text-indigo-600 dark:text-indigo-400`, `hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300`).

---

## [1.0.38] — 2026-09-20 — 100% English Master Translation Keys Mandate & Non-English Key Purge

### Added
- **Strict Guidelines & Architecture Mandates (`AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/views.md`)**:
  - Enshrined the **Absolute Mandate for 100% English Master Translation Keys**:
    1. Every translation key in code (`__('...')`, `@lang('...')`, validation, notifications, controllers) MUST strictly be authored in English.
    2. Every dictionary key (JSON object property name) across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` MUST strictly and exclusively be in English.
    3. Strict Zero-Tolerance Prohibition against using Arabic, French, or any non-English text as dictionary keys. Arabic and French text may ONLY exist as translated values.
- **Automated Verification Test (`tests/Feature/LocalizationTest.php`)**:
  - Added `test_all_translation_keys_are_strictly_in_english` verifying that 0 translation keys contain Arabic characters across `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.

### Changed
- **Language Dictionaries Cleanup (`lang/`)**:
  - Purged legacy reverse-translation bridge keys (`"حذف مستخدم"` and `"مستخدم جديد"`) from `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
  - Canonical English master keys `"New User"` and `"User Deleted"` now exclusively govern these translations.
  - Achieved 100% pure English dictionary keys (914 keys per file with exact 1-to-1 parity).

---

## [1.0.37] — 2026-09-20 — Mandatory Unified Search, Filter & State Persistence Architecture

### Added
- **Strict Guidelines & Architecture Mandates (`AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/views.md`)**:
  - Formally codified `Mandatory Unified Search, Filter & State Persistence Architecture`:
    1. Single Source of Truth for Search & Filters: `<x-global-filter>` is the authoritative standard for all table search inputs, status/position filters, and date filters across all tabular pages.
    2. Mandatory Functional Role Grouping in Selectors: In all position filters, dropdowns, and create/edit modal selects, job positions MUST be grouped into the 3 standardized functional tiers via `<optgroup>` (`Management & Executive Leadership`, `Engineering & Specialist Roles`, `Field Operations & Technicians`).
    3. Mandatory Filter & Search State Persistence Across All Lifecycle Events: Active search and filter queries (`search`, `position`, `status`, etc.) MUST be preserved across:
       - Language Switching (`LaravelLocalization::getLocalizedURL($localeCode, null, [], true)`).
       - Pagination Navigation (`->withQueryString()`).
       - Record Creation (Modal form action URLs append `request()->query()` and controller store actions redirect back with `$request->query()`).
       - Record Modification/Editing (Modal form action URLs append `request()->query()` and controller update actions redirect back with `$request->query()`).
       - Record Deletion (Delete modal action URLs append `request()->query()` and controller destroy actions redirect back with `$request->query()`).
- **Automated Feature Test**:
  - `tests/Feature/MasterData/EmployeeFilterPersistenceTest.php`: Comprehensive test suite verifying query string persistence across pagination links, employee creation redirect, employee update redirect, and employee deletion redirect (4/4 passed).
- **Trilingual Dictionary Keys (`lang/en.json`, `lang/ar.json`, `lang/fr.json`)**:
  - Synchronized keys with 100% 1-to-1 parity: `"Management & Executive Leadership"`, `"Engineering & Specialist Roles"`, `"Field Operations & Technicians"` (919 keys each, 0 missing).

### Changed
- **Paginator Query State Persistence (`app/Repositories/`)**:
  - `app/Repositories/EmployeeRepository.php`: Chained `->withQueryString()` to `paginateWithFilter()`.
  - `app/Repositories/BaseRepository.php`: Chained `->withQueryString()` to `paginate()` and `paginateWithFilter()`.
  - `app/Repositories/UserRepository.php`: Chained `->withQueryString()` to `getVerifiedUsers()`.
- **Controller Redirection Query Preservation (`app/Http/Controllers/MasterDataController.php`)**:
  - `storeEmployee`: Redirects back with `$request->query()`.
  - `updateEmployee`: Redirects back with `$request->query()`.
  - `destroyEmployee`: Redirects back with `request()->query()`.
- **Views & Modals (`resources/views/master-data/employees.blade.php`)**:
  - Position filter select inside `<x-global-filter>` categorized into 3 functional `<optgroup>` tiers.
  - Create modal and edit modal position selects grouped into 3 functional `<optgroup>` tiers.
  - Action URLs for create, edit, and delete modals dynamically pass `request()->query()` to preserve active filter state.

---

## [1.0.36] — 2026-09-20 — Project-Wide Color Unification, Semantic Classification & Design Token Harmonization

### Changed
- **PHP Enums Layer (`app/Enums/`)**:
  - `app/Enums/AccountStatus.php`: Implemented `badgeVariant(): string` contract returning `'success'` for `Active` and `'danger'` for `Suspended`. Modernized legacy `badgeClass()` to use alpha-transparency design tokens (`bg-emerald-500/10`, `bg-rose-500/10`).
- **CSS Semantic Classes & Design Tokens (`resources/css/components.css`)**:
  - Replaced legacy opaque dark mode classes (`dark:bg-*-950`) in `.badge-primary`, `.badge-danger`, `.badge-warning`, `.badge-success`, and `.badge-neutral` with unified alpha-transparency tokens (`bg-*/10`, `border-*/20`, `text-* dark:text-*`) matching `<x-badge>`.
- **Domain Overview Metric Cards (`resources/views/*/index.blade.php`)**:
  - `resources/views/metrology/index.blade.php`: Harmonized metric card sub-labels (`Operational`, `Tracking`, `Certifications`, `Standards`) to `text-brand-700 dark:text-brand-400`.
  - `resources/views/operations/index.blade.php`: Harmonized all 5 metric card sub-labels (`Field Operations`, `Active Agreements`, `Project Files`, `Guarantees Active`, `Classifications`) to `text-brand-700 dark:text-brand-400`.
  - `resources/views/master-data/index.blade.php`: Harmonized all 3 metric card sub-labels (`Accounts`, `Staff`, `Facilities`) and empty state icon to `text-brand-700 dark:text-brand-400` and `bg-brand-600/10`.
  - `resources/views/analytics/index.blade.php`: Harmonized all 4 metric card sub-labels (`Cost Tracking`, `Projections`, `Key Metrics`, `Documents Ready`) and empty state icon to `text-brand-700 dark:text-brand-400` and `bg-brand-600/10`.
- **Workspace Dashboard (`resources/views/workspace.blade.php`)**:
  - Harmonized executive Super-Admin badge to `primary` brand variant (`<x-badge variant="primary" size="md">`).
  - Unified all 4 business portal explorer count badges (Metrology, Operations, Analytics, Master Data) from arbitrary rainbow variants to clean, neutral status tokens (`<x-badge variant="neutral" size="md">`), preventing UI noise and decoupling counters from operational alarm states.
- **System Module Views (`resources/views/system/**`)**:
  - `resources/views/system/users.blade.php`: Harmonized registration open/closed pills to modern alpha tokens (`bg-emerald-500/10`, `bg-rose-500/10`).
  - `resources/views/system/settings.blade.php`: Modernized registration pills and boolean setting values to alpha tokens.
  - `resources/views/system/roles.blade.php`: Harmonized all system privileges badges and permission count chips to alpha tokens (`bg-emerald-500/10`, `bg-indigo-500/10`), and updated row toggles to `bg-indigo-500/10 dark:hover:bg-indigo-500/20`.
- **Table Action Component (`resources/views/components/table/action.blade.php`)**:
  - Modernized theme classes across all action variants (`edit`, `delete`, `primary`, `success`, `view`) to leverage alpha-transparency tokens (`bg-*/10`, `border-*/20`, `hover:bg-*`, `dark:bg-*/20`) ensuring high-contrast rendering across light and dark themes.

### Added
- **Unit Test Suite Coverage**:
  - `tests/Unit/EnumsBadgeContractTest.php`: Created automated unit test verifying `badgeVariant(): string` contract across all Enums (`AccountStatus`, `EmployeePosition`, `EmployeeStatus`) and asserting strict 3-tier functional classification compliance.

---

> [!NOTE]
> Historical release entries from `v1.0.0` through `v1.0.35` have been archived to [Core Kernel Baseline Archive](file:///c:/Project%20HARD/erp.gmtm-dz.com/docs/archive/core_kernel_archive.md) to maintain token efficiency.
> Pre-Core-Kernel historical logs are preserved in [Legacy Changelog](file:///c:/Project%20HARD/erp.gmtm-dz.com/docs/archive/legacy_changelog.md).

