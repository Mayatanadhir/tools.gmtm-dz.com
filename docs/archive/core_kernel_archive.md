# Historical Changelog: Core Kernel Baseline (v1.0.0 — v1.0.35)

This archive document preserves the detailed historical release logs for the ENGI-GMTM Core Kernel from the initial Point Zero baseline (`v1.0.0`) through `v1.0.35`.

For active releases (`v1.0.36` onward), refer to the primary [Changelog](file:///c:/Project%20HARD/erp.gmtm-dz.com/docs/changelog.md).
For historical logs prior to the Point Zero core extraction, refer to the [Legacy Changelog](file:///c:/Project%20HARD/erp.gmtm-dz.com/docs/archive/legacy_changelog.md).

---

## [1.0.35] — 2026-09-20 — Employee Position & Status Badge Harmonization & Design System Alignment

### Changed
- **Employee Position & Status Badges (Single Source of Truth)**:
  - `app/Enums/EmployeePosition.php`: Added `badgeVariant(): string` method classifying positions into three unified functional tiers conforming to the centralized design token system (`Management => 'primary'`, `Engineering => 'info'`, `Technicians => 'neutral'`). Refactored `color()` to return semantic color tokens (`brand`, `indigo`, `gray`) and sanitized `badgeClass()`.
  - `app/Enums/EmployeeStatus.php`: Added `badgeVariant(): string` method mapping operational states to semantic tokens (`Active => 'success'`, `Inactive => 'danger'`, `OnLeave => 'warning'`).
  - `resources/views/master-data/employees.blade.php`: Replaced raw `<span>` and hardcoded CSS classes under `<x-table.th>{{ __('Position') }}</x-table.th>` and `<x-table.th>{{ __('Status') }}</x-table.th>` with the unified `<x-badge>` component (`<x-badge :variant="$employee->position->badgeVariant()">` and `<x-badge :variant="$employee->status->badgeVariant()" :dot="true">`), eliminating ad-hoc color classes and guaranteeing harmonious light/dark mode presentation.
  - Aligned search filter select dropdowns and form inputs to use unified brand focus ring tokens (`focus:border-brand-600 focus:ring-brand-600`).
  - Standardized employee avatar fallback and border styles to use unified brand tokens (`bg-brand-600/10 text-brand-700 dark:text-brand-400 border border-brand-500/20`).

### Added
- **Standing AI Rule Enshrinement**:
  - Formally codified and enshrined the strict rule: **Mandatory Single Source of Truth for Colors, Badge System & Semantic Functional Classification** across `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, `.ai/rules/views.md`, and `.ai/rules/enums.md`.
  - Enforced pure PHP isolation for Enums (`badgeVariant(): string` contract), banned raw `<span>` badges with arbitrary ad-hoc Tailwind colors, mandated 3-tier functional classification (Management: `primary`, Engineering: `info`, Technicians: `neutral`), and decoupled operational status from job titles.
- **Test Suite Verification**:
  - `tests/Feature/MasterData/EmployeeTest.php`: Added `test_employees_page_renders_unified_position_and_status_badges` verifying correct variant resolution and view rendering for all employee tiers.

---

## [1.0.34] — 2026-09-20 — System Module Tool Icon & Executive Dashboard Header Harmonization

### Added
- **System Control & Infrastructure Tool Icon**:
  - `resources/views/components/icons/tools/module-system.blade.php`: Implemented a standalone rich SVG micro-illustration depicting enterprise database stacks, activity LEDs, master system control cogs, and security shield circuits. Standardized its palette to the official GMTM corporate industrial green spectrum (`#065F46` / `#022C22` / `#059669` / `#10B981` / `#34D399` / `#A7F3D0`) matching the company logo and sibling workspace modules, with isolated unique prefix `mod-sys-` conforming to `docs/tool_icons_guide.md`.

### Changed
- **Executive Dashboard Headers & Layout Standardization**:
  - `resources/views/system/index.blade.php`: Cleaned header structure, properly closed tags, and bound `<x-tool-icon name="module-system" />` with responsive flex alignment (`flex items-center gap-4`).
  - `resources/views/metrology/index.blade.php`: Standardized header to clean inline icon layout with `module-metrology`.
  - `resources/views/operations/index.blade.php`: Standardized header to clean inline icon layout with `module-operations`.
  - `resources/views/analytics/index.blade.php`: Standardized header to clean inline icon layout with `module-analytics`.
  - `resources/views/master-data/index.blade.php`: Standardized header to clean inline icon layout with `module-master-data`.
  - `resources/views/workspace.blade.php`: Harmonized the Super-Admin System Control & Infrastructure banner to render `<x-tool-icon name="module-system" />`.

---

## [1.0.33] — 2026-09-20 — GMTM Corporate Logo Alignment Across Application Components

### Changed
- **Corporate Visual Identity & Dual-Theme Logo Synchronization**:
  - `resources/views/components/application-logo.blade.php`: Aligned the central application logo component to use GMTM official assets: `images/Logo-black.png` for Light Mode (`block dark:hidden`) and `images/Logo-white.png` for Dark Mode (`hidden dark:block`), updated alt fallback to `ERP-GMTM`.
  - `public/images/`: Synchronized `Logo-black.png` to `logo.png` and `Logo-white.png` to `logo-dark.png` to maintain full backwards compatibility with legacy routes/caches and eliminate 404 image errors.
  - `resources/views/workspace.blade.php`: Adjusted executive welcome banner logo container from fixed square (`w-14 h-14`) to proportional aspect ratio (`h-14 w-auto shrink-0 max-w-[160px]`), preventing distorted or undersized rendering of the wide GMTM logo banner.
  - `resources/views/layouts/guest-ltr.blade.php` & `resources/views/layouts/guest-rtl.blade.php`: Adjusted guest authentication logo sizing to `h-20 w-auto max-w-[220px]` preserving natural proportions across login and registration views.

---

## [1.0.32] — 2026-09-13 — Full Implementation of Employees Module, CAS Avatar Sync & Legacy Migration

### Added
- **Employees Module Core Infrastructure**:
  - `database/migrations/2026_09_13_093000_create_employees_table.php`: Migrated `employees` table with `user_id` foreign key, unique `registration_number`, indexed `full_name` and `status`, decimal `salary` and `daily_rate`, `profile_photo_path`, `photo_hash`, timestamps, and `softDeletes`.
  - `app/Enums/EmployeePosition.php`: PHP 8.4 Backed String Enum (`GeneralManager`, `SeniorMeteringEngineer`, `MeteringEngineer`, `SeniorInstrumentationEngineer`, `MeteringTechnician`, `InstrumentationTechnician`) with semantic color tokens, badge styling, and category helpers.
  - `app/Enums/EmployeeStatus.php`: PHP 8.4 Backed String Enum (`Active`, `Inactive`, `OnLeave`) with badge classes and translation helpers.
  - `app/Exceptions/CannotDeleteAssignedEmployeeException.php`: Domain exception protecting employees assigned to active or historical missions.
  - `app/Models/Employee.php`: Reference staff model incorporating `FilterableTrait`, Spatie `LogsActivity`, `HasFactory`, `SoftDeletes`, and `#[ObservedBy([EmployeeObserver::class])]`.
  - `app/Observers/EmployeeObserver.php`: Content-Addressable Storage (CAS) observer calculating `photo_hash` and ensuring safe unlinking via `MediaOptimizationService::safeDelete`.
  - `app/Policies/EmployeePolicy.php`: Granular authorization policy managing standard CRUD and financial quarantine inspection (`view employee compensation`).
  - `app/Interfaces/EmployeeRepositoryInterface.php` & `app/Repositories/EmployeeRepository.php`: Clean repository pattern with `with('user')` eager loading eliminating N+1 queries.
  - `app/Services/EmployeeService.php`: Domain service orchestrating atomic transactions, WebP compression, mission assignment protection, and bidirectional photo synchronization between employees and linked user accounts.
  - `app/Http/Requests/MasterData/StoreEmployeeRequest.php` & `app/Http/Requests/MasterData/UpdateEmployeeRequest.php`: Robust validation enforcing unique `registration_number` with soft delete exclusion and image validation.
  - `database/seeders/LegacyEmployeeSeeder.php` & `app/Console/Commands/ImportLegacyEmployeesCommand.php`: Synchronized historical data for all 7 legacy employees with exact primary key preservation (1, 2, 3, 4, 5, 25, 26).
  - `tests/Feature/MasterData/EmployeeTest.php`: Comprehensive test suite verifying 12 distinct feature workflows (43 assertions, 100% passing).

### Changed
- **Bidirectional Photo Synchronization**:
  - `app/Models/User.php`: Added `employee(): HasOne` relationship.
  - `app/Models/Employee.php`: Enabled `getProfilePhotoUrlAttribute()` fallback to linked user's avatar.
  - `app/Services/MediaOptimizationService.php`: Updated `isAssetInUse()` to check both User and Employee entities during CAS safe delete.
- **Master Data Routing & Interface**:
  - `routes/web.php`: Added `store`, `update`, and `destroy` endpoints for `/master-data/employees`.
  - `app/Http/Controllers/MasterDataController.php`: Injected `EmployeeService` and `EmployeeRepositoryInterface`, implemented full CRUD workflows and error trapping.
  - `resources/views/master-data/employees.blade.php`: Complete redesign with full-width responsive `<x-table>`, live Alpine.js photo previews, user selection dropdowns, financial compensation quarantine masking (`•••••••• DZD`), and unified Industrial Green accents.
- **Permissions & Localization**:
  - `config/permissions.php`: Added `view employee compensation` to `master_data.employees` (85 active permissions).
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Synchronized 36 new keys reaching 912 identical keys across all 3 languages (0 missing).

---

## [1.0.31] — 2026-09-13 — Comprehensive Industrial Green Palette Transformation Across Active Tool Icons & Workspace Modules

### Changed
- **Unified Industrial Green Color Standardization Across Tool Icons & Module Badges**:
  - `resources/views/components/icons/tools/equipment.blade.php`: Transformed primary body and casing gradients from blue (`#60A5FA` / `#2563EB` / `#1D4ED8` / `#1E3A8A`) to Industrial Emerald Green (`#10B981` / `#059669` / `#047857` / `#022C22`). Corrected glare highlight markup, adjusted side-grip accents (`#10B981` and `#047857`), and matched enter key to `#059669` / `#10B981`.
  - `resources/views/components/icons/tools/forecasts.blade.php`: Converted analytics trend card and coordinate grid from deep indigo (`#1E1B4B` / `#312E81` / `#6366F1`) to deep industrial green (`#065F46` / `#022C22` / `#059669` / `#34D399` / `#10B981`).
  - `resources/views/components/icons/tools/missions.blade.php`: Refactored mission clipboard from slate/amber (`#334155` / `#1E293B` / `#F59E0B`) to industrial green (`#065F46` / `#022C22` / `#059669` / `#34D399` / `#10B981`), updating clip bar and in-progress indicator.
  - `resources/views/components/icons/tools/module-master-data.blade.php`: Converted constellation master data module emblem from crimson rose (`#4C0519` / `#881337` / `#F43F5E`) to industrial green (`#065F46` / `#022C22` / `#059669` / `#10B981` / `#A7F3D0`).
  - `resources/views/components/icons/tools/module-metrology.blade.php`: Transformed metrology module emblem from indigo to industrial emerald green (`#065F46` / `#022C22` / `#34D399` / `#059669` / `#A7F3D0`).
  - `resources/views/components/icons/tools/module-operations.blade.php`: Transformed operations module emblem from brown/amber (`#78350F` / `#451A03` / `#D97706`) to industrial emerald green (`#065F46` / `#022C22` / `#059669` / `#10B981` / `#34D399`).
  - `resources/views/components/icons/tools/units.blade.php`: Transformed Quantities & Units precision balance scale and metric cube from violet/purple (`#4C1D95` / `#2E1065` / `#6D28D9` / `#C084FC`) to industrial emerald green (`#065F46` / `#022C22` / `#059669` / `#34D399` / `#10B981` / `#D1FAE5`).
  - `resources/views/components/icons/tools/reports.blade.php`: Transformed Reports Management executive dossier binder, left margin spine, and export verification seal from deep blue (`#1E3A8A` / `#172554` / `#1D4ED8` / `#2563EB`) to industrial emerald green (`#065F46` / `#022C22` / `#059669` / `#047857` / `#10B981` / `#D1FAE5`).
  - `resources/views/components/icons/tools/expenses.blade.php`: Transformed Expenses & Charges ledger card, receipt slip accents, and cost trend badge from rose/crimson (`#4C0519` / `#881337` / `#BE123C` / `#E11D48`) to industrial emerald green (`#065F46` / `#022C22` / `#059669` / `#ECFDF5`), preserving the stacked gold coins standard.
  - `resources/views/components/icons/tools/employees.blade.php`: Transformed Personnel & Employees ID security badge, department header bar, staff avatar box, and credential lines from indigo (`#4F46E5` / `#3730A3` / `#6366F1`) to industrial emerald green (`#059669` / `#064E3B` / `#10B981` / `#047857` / `#ECFDF5`), preserving the gold biometric security chip.
  - `resources/views/metrology/index.blade.php`: Synchronized Equipment and Quantities & Units metric card text tokens to `text-emerald-600 dark:text-emerald-400`.
  - `resources/views/analytics/index.blade.php`: Synchronized Expenses & Charges, Annual Forecasts, and Reports Management metric card text tokens to `text-emerald-600 dark:text-emerald-400`.
  - `resources/views/operations/index.blade.php`: Synchronized Mission Management metric card text token to `text-emerald-600 dark:text-emerald-400`.
  - `resources/views/master-data/index.blade.php`: Synchronized Employees metric card text token to `text-emerald-600 dark:text-emerald-400`.

---

## [1.0.30] — 2026-09-13 — Contracts Management Industrial Green Palette Transformation

### Changed
- **Harmonized Contracts Management Visual Identity to Industrial Green**:
  - `resources/views/components/icons/tools/contracts.blade.php`: Transformed primary accent color palette from indigo (`#4F46E5` / `#3730A3` / `#A5B4FC`) to industrial green (`#047857` / `#064E3B` / `#059669` / `#A7F3D0`). Updated legal header decorative band (`#047857` to `#064E3B`), inner header accent line (`#A7F3D0`), legal clause title line (`#047857`), signature stylized script line (`#059669`), and drop shadow (`#064E3B`), while preserving the authentic certified golden wax seal (`#F59E0B` to `#B45309`).
  - `resources/views/operations/index.blade.php`: Updated the Active Agreements metric label text token from `text-indigo-600 dark:text-indigo-400` to `text-emerald-600 dark:text-emerald-400` to maintain cross-system color coherence.

---

## [1.0.29] — 2026-09-13 — Enterprise Clients Industrial Green Palette Transformation

### Changed
- **Harmonized Enterprise Clients Visual Identity to Industrial Green**:
  - `resources/views/components/icons/tools/clients.blade.php`: Transformed primary color palette from rose/crimson (`#4C0519` / `#881337` / `#F43F5E` / `#BE123C`) to deep industrial machine green (`#065F46` / `#022C22` / `#10B981` / `#059669` / `#047857`). Updated card base container (`#065F46` to `#022C22`), corporate high-rise towers (`#10B981` to `#047857`), illuminated architectural windows and entrance (`#D1FAE5`), and verified client partnership handshake badge (`#059669` with `#A7F3D0` dotted ring and crisp white handshake).
  - `resources/views/master-data/index.blade.php`: Updated the Accounts metric label text token from `text-rose-600 dark:text-rose-400` to `text-emerald-600 dark:text-emerald-400` to maintain cross-system color coherence.

---

## [1.0.28] — 2026-09-13 — Calibrator Movements Fluke 700G Digital Gauge & Prominent Motion Arrow Redesign

### Changed
- **Redesigned Calibrator Movements Icon (`resources/views/components/icons/tools/calibrator-movements.blade.php`)**:
  - Replaced the generic transit suitcase with an authentic digital pressure calibrator gauge inspired by the Fluke 700G Series Precision Reference Gauge, harmonized in **Industrial Green**.
  - Features circular molded protective rubber boot (`#10B981` / `#059669` / `#047857` / `#064E3B`) with side grip ridges, dark dial face plate (`#1E293B` to `#0F172A`), top power button with glowing icon, high-contrast backlit LCD screen (`#93C5FD` to `#3B82F6`) displaying large digital readout (`15.00 PSI`), segment bargraph scale (0% to 100%), 4 navigation push buttons (`ZERO`, `MIN`, `CONFIG`, `ENTER`), bottom embossed calibrator rim (`CAL`), and stainless steel bottom hex fitting (`#CBD5E1` / `#94A3B8`) with technical specification tag (`1 BAR`) and threaded process nipple.
  - Implemented a bold, prominent logistics transfer motion trajectory (`#34D399` to `#059669`, `stroke-width="3.5"`) with an origin pulsing beacon (`cx="8" cy="22"`), motion speed trail dashes, and a sharp prominent arrowhead (`#34D399`) delivering unmistakable clarity for calibrator transit workflows across all viewport scales.
  - `resources/views/metrology/index.blade.php`: Updated the Tracking metric label text token from `text-blue-600 dark:text-blue-400` to `text-emerald-600 dark:text-emerald-400`.

---

## [1.0.27] — 2026-09-13 — Attachments Dossier Industrial Green Palette Transformation

### Changed
- **Harmonized Attachments List Visual Identity to Industrial Green**:
  - `resources/views/components/icons/tools/attachments.blade.php`: Transformed primary accent color palette from sky blue (`#38BDF8` / `#0284C7` / `#0369A1`) to industrial green (`#34D399` / `#059669` / `#047857` / `#064E3B`). Updated the metallic corner paperclip gradient (`at-clip-grad`), document title line (`#047857`), mini-chart sparkline (`#059669`), PDF file tag badge (`at-badge-bg`), and drop shadow (`at-shadow`).
  - `resources/views/operations/index.blade.php`: Updated the Project Files metric label text token from `text-blue-600 dark:text-blue-400` to `text-emerald-600 dark:text-emerald-400` to maintain cross-system color coherence.

---

## [1.0.26] — 2026-09-13 — Classification of Articles Industrial Green Palette Transformation

### Changed
- **Harmonized Classification of Articles Visual Identity to Industrial Green**:
  - `resources/views/components/icons/tools/article-types.blade.php`: Transformed primary color palette from purple (`#3B0764` / `#581C87` / `#7E22CE`) to deep industrial machine green (`#065F46` / `#022C22` / `#059669`). Updated header ribbon (`#047857`), status/rivet dots (`#A7F3D0` / `#34D399` / `#10B981`), barcode reference indicators (`#A7F3D0`), and floating classification index badge (`#059669`).
  - `resources/views/operations/index.blade.php`: Updated the classification count label text token from `text-purple-600 dark:text-purple-400` to `text-emerald-600 dark:text-emerald-400` to maintain cross-system color coherence.

---

## [1.0.25] — 2026-09-12 — Explorer Views Header & Table Toolbar Icons Integration (Complete)

### Changed
- **All 17 Business Explorer Views — Header & Toolbar Icon Integration:**
  - Replaced every plain `<div>` title block in `<x-slot name="header">` with the standardized `<x-tool-icon>` + title pattern across all explorer pages.
  - Replaced all legacy raw SVG icons in `<x-slot:toolbar>` with the unified `<x-tool-icon name="..." class="w-6 h-6 shrink-0" />` component.
  - **Metrology (5):** `instruments`, `equipment`, `calibrator-movements`, `calibration-certificates`, `units`.
  - **Operations (5):** `missions`, `contracts`, `attachments`, `warranties`, `article-types`.
  - **Analytics (4):** `expenses`, `forecasts`, `statistics`, `reports`.
  - **Master Data (3):** `clients`, `employees`, `sites`.
- **Pattern Standardized:**
  - Header: `<x-tool-icon name="[tool]" class="w-11 h-11 sm:w-12 sm:h-12 shrink-0" />` beside `<h2>` title.
  - Toolbar: `<x-tool-icon name="[tool]" class="w-6 h-6 shrink-0" />` inside `<h3>` with `gap-2.5`.

---

## [1.0.24] — 2026-09-12 — Measuring Instruments Industrial Transmitter Icon Redesign

### Changed
- **Redesigned Measuring Instruments Icon (`resources/views/components/icons/tools/instruments.blade.php`)**:
  - Re-crafted the Measuring Instruments visual micro-illustration to authentically match the enterprise industrial process transmitter / pressure & temperature measuring field instrument provided in engineering specifications.
  - Features signature industrial-blue explosion-proof cylindrical housing (`#0284C7` / `#0369A1` / `#0C4A6E`), circular notched grip bezel, dark recessed viewing window, illuminated pale green digital LCD display (`#A7F3D0` / `#6EE7B7`) with readout (`101.3 bar`) and bargraph, top stainless steel tag with rivets, side conduit entry port, and bottom process sensor hub.
  - Maintains strict ID prefix isolation (`ins-`), zero CSS styling leakage, and sharp scalability across dashboard cards (`w-12 h-12`) and sidebar tabs (`w-6 h-6`).

---

## [1.0.23] — 2026-09-12 — Sidebar Navigation Tool Icons Generalization

### Changed
- **Generalization of Rich Tool Icons Across Sidebar Navigation Tabs**:
  - Replaced legacy monochrome linear SVGs in all 4 business sidebar tab components with `<x-tool-icon :name="$tab['tool']" class="w-6 h-6 shrink-0 transition-transform duration-200 group-hover:scale-110" />`:
    - `resources/views/components/operations-tabs.blade.php`: `module-operations`, `missions`, `contracts`, `attachments`, `warranties`, `article-types`.
    - `resources/views/components/metrology-tabs.blade.php`: `module-metrology`, `instruments`, `equipment`, `calibrator-movements`, `calibration-certificates`, `units`.
    - `resources/views/components/analytics-tabs.blade.php`: `module-analytics`, `expenses`, `forecasts`, `statistics`, `reports`.
    - `resources/views/components/master-data-tabs.blade.php`: `module-master-data`, `clients`, `employees`, `sites`.
  - Cleaned up tab PHP arrays by removing bulky inline raw SVG strings, elevating separation of concerns and maintainability.

---

## [1.0.22] — 2026-09-12 — Modular Rich Tool & Page Icons Architecture

### Added
- **Componentized Micro-Illustration Architecture (`resources/views/components/icons/tools/`)**:
  - Implemented 21 standalone, dedicated SVG Blade components in `resources/views/components/icons/tools/` following the visual guidelines in `docs/tool_icons_guide.md`:
    - Operations (5): `missions.blade.php`, `contracts.blade.php`, `attachments.blade.php`, `warranties.blade.php`, `article-types.blade.php`.
    - Metrology (5): `instruments.blade.php`, `equipment.blade.php`, `calibrator-movements.blade.php`, `calibration-certificates.blade.php`, `units.blade.php`.
    - Analytics (4): `expenses.blade.php`, `forecasts.blade.php`, `statistics.blade.php`, `reports.blade.php`.
    - Master Data (3): `clients.blade.php`, `employees.blade.php`, `sites.blade.php`.
    - Workspace Master Modules (4): `module-metrology.blade.php`, `module-operations.blade.php`, `module-analytics.blade.php`, `module-master-data.blade.php`.
  - Built polymorphic proxy icon component `<x-tool-icon name="..." />` with fail-safe fallback rendering and zero ID collisions via strictly scoped gradient and filter ID prefixes (`ms-`, `ct-`, `ins-`, `mod-met-`, etc.).
- **Trilingual Dictionary Key Synchronization**:
  - Registered `3 Explorers` across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (`3 مستكشفات` / `3 Explorateurs`), ensuring 100% parity across all 738 translation keys.

### Changed
- **Unified Visual Identity Across Business Dashboards**:
  - Replaced legacy monochrome linear SVGs across `operations/index.blade.php`, `metrology/index.blade.php`, `analytics/index.blade.php`, `master-data/index.blade.php`, and `workspace.blade.php` with dynamic `<x-tool-icon>` micro-illustrations.
  - Added subtle hover micro-animations (`group-hover:scale-105 transition-transform duration-200`) without any inline CSS or styling leakage.
  - Aligned explorer badge counts in `workspace.blade.php` to accurately display 5 for Metrology, 5 for Operations, 4 for Analytics, and 3 for Master Data.

---

## [1.0.21] — 2026-09-12 — Business Modules Metrics Grid Layout Unification

### Changed
- **Harmonized Executive Metrics Grid Across Dashboards**:
  - Unified `resources/views/operations/index.blade.php` and `resources/views/metrology/index.blade.php` to use the identical 4-column responsive grid layout (`grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4`) matching `resources/views/analytics/index.blade.php` and `resources/views/system/index.blade.php`.
  - Optimized individual card dimensions and widths, preventing horizontal squishing and matching the exact visual proportion of Analytics.
  - Aligned explorer count badge for Operations in `resources/views/workspace.blade.php` to `5 Explorers` reflecting all five active operational entities (`Missions`, `Contracts`, `Attachments`, `Bank Guarantees`, `Article Types`).

---

## [1.0.20] — 2026-09-12 — Navigation Bar Module Links Streamlining

### Changed
- **Streamlined Navigation Bar Labels (`resources/views/layouts/navigation-ltr.blade.php` & `navigation-rtl.blade.php`)**:
  - Removed `Dashboard` prefix (`Tableau de bord` / `لوحة التحكم`) from desktop and responsive navigation bar links.
  - Aligned all navigation links to use concise, direct module naming:
    - `Metrology` (`Métrologie` / `المترولوجيا`)
    - `Operations` (`Opérations` / `العمليات`)
    - `Analytics` (`Analytique` / `التحليلات`)
    - `Master Data` (`Données de référence` / `البيانات المرجعية`)
  - Guaranteed 100% parity across LTR (English & French) and RTL (Arabic) navigation bars in both desktop and mobile views.

---

## [1.0.19] — 2026-09-12 — System & Database Title Simplification

### Changed
- **System Module Header Title Update (`resources/views/system/index.blade.php`)**:
  - Simplified page header title from `System & Database Explorer` to `System & Database`.
  - Updated test assertion in `tests/Feature/SystemTableTest.php`.
  - Added synchronized trilingual key `System & Database` across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (`بيانات وجداول النظام` / `Données et tables système`), maintaining exact 1-to-1 parity at 707 keys each.

---

## [1.0.18] — 2026-09-12 — Module Dashboard Title Translations

### Added
- **Trilingual Dictionary Keys for Module Dashboards**:
  - Registered translations across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` for all executive module dashboard headers and navigation items:
    - `Dashboard Metrology` (`لوحة تحكم المترولوجيا` / `Tableau de bord Métrologie`)
    - `Dashboard Operations` (`لوحة تحكم العمليات` / `Tableau de bord Opérations`)
    - `Dashboard Master Data` (`لوحة تحكم البيانات المرجعية` / `Tableau de bord Données de référence`)
    - `Dashboard Analytics` (`لوحة تحكم التحليلات` / `Tableau de bord Analytique`)
  - Preserved exact 1-to-1 key parity across all three languages (706 keys each, 0 missing).

---

## [1.0.17] — 2026-09-12 — Terminology Update: Classification of Articles

### Changed
- **Renamed `Article Types List` to `Classification of Articles` (تصنيف المواد)**:
  - `resources/views/operations/article-types.blade.php`: Updated header title and table toolbar title to `Classification of Articles`.
  - `resources/views/components/operations-tabs.blade.php`: Updated tab navigation label to `Classification of Articles`.
  - `resources/views/operations/index.blade.php`: Updated metric card title and quick access button in overview card to `Classification of Articles`.
  - `config/permissions.php`: Updated display label (`lang`) for `article_types` entity in the static permissions registry and role matrix to `Classification of Articles`.
  - `app/Http/Controllers/OperationsController.php`: Updated method docblock to `Classification of Articles`.
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Added `Classification of Articles` (`تصنيف المواد` / `Classification des articles`) maintaining exact 1-to-1 key parity (702 keys each).

---

## [1.0.16] — 2026-09-12 — Granular Permission Gating on Master Data, Metrology & Analytics Dashboard Cards

### Added & Changed
- **Granular RBAC Protection across All Business Dashboards**:
  - **Master Data Dashboard (`resources/views/master-data/index.blade.php`)**:
    - Gated metric cards behind granular permissions: Clients (`@can('view clients')`), Employees (`@can('view employees')`), Sites (`@can('view sites')`).
    - Gated overview cards and quick access buttons with `@canany` / `@can`.
    - Implemented zero-permission fallback empty state (`No Accessible Explorers`) when holding only `view master data`.
  - **Metrology Dashboard (`resources/views/metrology/index.blade.php`)**:
    - Gated metric cards: Measuring Instruments (`@can('view measuring instruments')`), Equipment (`@can('view equipment')`), Calibrator Movements (`@can('view calibrator movements')`), Calibration Certificates (`@can('view calibration certificates')`), Quantities & Units (`@can('view quantities units')`).
    - Gated overview cards and quick access buttons with `@canany` / `@can`.
    - Implemented zero-permission fallback empty state (`No Accessible Explorers`) when holding only `view metrology`.
  - **Analytics Dashboard (`resources/views/analytics/index.blade.php`)**:
    - Gated metric cards: Expenses & Charges (`@can('view expenses')`), Annual Forecasts (`@can('view annual forecasts')`), Company Statistics (`@can('view company statistics')`), Executive Reports (`@can('view reports')`).
    - Gated overview cards and quick access buttons with `@canany` / `@can`.
    - Implemented zero-permission fallback empty state (`No Accessible Explorers`) when holding only `view analytics`.
  - **Trilingual Localization Synchronization**:
    - Added fallback description translations for Master Data, Metrology, and Analytics across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (701 keys each, 100% 1-to-1 parity).
  - **Automated Feature Testing (`tests/Feature/BusinessModulesTest.php`)**:
    - Added comprehensive tests for all four business module dashboards: `test_operations_dashboard_cards_adhere_to_permissions`, `test_metrology_dashboard_cards_adhere_to_permissions`, `test_master_data_dashboard_cards_adhere_to_permissions`, and `test_analytics_dashboard_cards_adhere_to_permissions`.

---

## [1.0.15] — 2026-09-12 — Granular Permission Gating on Operations Dashboard Cards

### Added & Changed
- **Granular RBAC Protection for Operations Dashboard (`resources/views/operations/index.blade.php`)**:
  - Gated each of the 5 Metric Cards behind granular view permissions:
    - Mission Management Card: `@can('view missions')`
    - Contracts Card: `@can('view contracts')`
    - Attachments List Card: `@can('view attachments')`
    - Bank Guarantees Card: `@can('view warranties')`
    - Article Types List Card: `@can('view article types')`
  - Gated the Overview Cards and their quick-access buttons:
    - Missions & Field Activities Card: `@canany(['view missions', 'view attachments'])`
    - Legal, Bank Guarantees & Classifications Card: `@canany(['view contracts', 'view warranties', 'view article types'])`
    - Each button within overview cards individually protected by its respective `@can(...)` gate.
  - Implemented polite zero-permission fallback empty state (`No Accessible Explorers`) when a user has access to the module root (`view operations`) but no granular child explorer permissions.
  - Added 2 synchronized trilingual keys across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` (`No Accessible Explorers`, `You do not currently have permissions to view any operational entities in this module. Contact your system administrator to grant the required permissions.`), maintaining 100% parity with 698 keys each.
  - Added comprehensive automated feature test `test_operations_dashboard_cards_adhere_to_permissions` in `tests/Feature/BusinessModulesTest.php`.

---

## [1.0.14] — 2026-09-12 — Module Access Root Permissions in Category Headers

### Added & Changed
- **Module Access Permissions Integration in Role Modals**:
  - `config/permissions.php`: Declared `view_permission` and `perms` directly inside `modules` array for `metrology` (`view metrology`), `operations` (`view operations`), `analytics` (`view analytics`), and `master_data` (`view master data`). Removed obsolete standalone `*_group` entries from `groups`.
  - `app/Services/PermissionDiscoveryService.php`: Updated `getDiscoveredTables()` and `generateCrudPermissionsForTables()` to register module-level permissions and prevent them from being pruned during sync. Updated `getGroupedPermissionMatrix()` to expose `view_permission` and prepend it to each module's `all_permissions` list.
  - `resources/views/system/roles.blade.php`: Added a dedicated, stylized Module Access row immediately beneath each domain category header in both Create Role and Edit Role modals, displaying an inline checkbox with icon, semantic badge coloring, and fully localized permission display (`{{ __($module['view_permission']) }}`).
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Added `Module access permission`, `view metrology`, `view operations`, `view analytics`, and `view master data` across all three language dictionaries (696 keys each, 100% 1-to-1 parity).
  - Synchronized permissions to database via `php artisan permissions:sync-tables`.

---

## [1.0.13] — 2026-09-12 — Categorized Permission Matrix by Module (Roles & Permissions)

### Added
- **Module-Categorized Permission Matrix in System Role Modals**:
  - `config/permissions.php`: Registered `'modules'` metadata defining 5 business domains (`system`, `metrology`, `operations`, `analytics`, `master_data`) with distinct semantic color tokens (`rose`, `indigo`, `amber`, `blue`, `emerald`), icons, and localized labels. Assigned each of the 24 entity groups to its designated `'module'`.
  - `app/Services/PermissionDiscoveryService.php`: Extended `getGroupedPermissionMatrix()` to structure and return `'modules'`, assembling permissions, actions, and entities grouped under their domain category while retaining backward compatibility with `'entities'`.
  - `resources/views/system/roles.blade.php`: Transformed Create Role and Edit Role modals from a flat 24-entity list into visually isolated domain categories:
    - Domain category header rows featuring module icon, category badge, entity counter, and single-click "Toggle Category" bulk-selection button (`toggleCreateEntityAll` / `toggleEditEntityAll`).
    - Sub-indented entity rows with clear visual hierarchy, soft zebra-striping, and standardized action checkmarks.
    - Expanded modal scroll area (`max-h-80`) for improved ergonomics.
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Added 3 synchronized trilingual translation keys: `System Security & Administration`, `Toggle Category`, and `entities` (691 total keys each, 100% parity).
  - `tests/Unit/PermissionDiscoveryServiceTest.php`: Added comprehensive unit test assertions verifying structured module grouping and metadata completeness.

---

## [1.0.12] — 2026-09-12 — Terminology Refinement: Bank Guarantees (الضمان البنكي)

### Changed
- **Refined Warranties terminology to Bank Guarantees (`الضمان البنكي`)**:
  - `resources/views/operations/warranties.blade.php`: Header title updated to `Bank Guarantees`, toolbar title updated, and descriptive subtitle updated to focus on bank guarantee obligations and financial commitments.
  - `resources/views/components/operations-tabs.blade.php`: Tab label updated to `Bank Guarantees`.
  - `resources/views/operations/index.blade.php`: Metric card, quick access button, overview description, and header subtitle updated to reference `Bank Guarantees`.
  - `resources/views/workspace.blade.php`: Operations module card description updated to reference `bank guarantees`.
  - `config/permissions.php`: Updated display label for warranties entity to `Bank Guarantees`.
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Added 6 synchronized trilingual translation keys and updated Arabic mapping to `الضمان البنكي` (688 total keys each, 100% parity).

---

## [1.0.11] — 2026-09-12 — Module Restructuring: Article Types → Operations

### Changed
- **Moved `Article Types` from Master Data → Operations module** (architectural realignment):
  - Route renamed: `master-data.article-types` → `operations.article-types` (`GET /operations/article-types`).
  - Controller method moved: `MasterDataController::articleTypes()` → `OperationsController::articleTypes()`.
  - View relocated: `resources/views/master-data/article-types.blade.php` → `resources/views/operations/article-types.blade.php` (sidebar updated to `x-operations-tabs`).
  - `resources/views/components/operations-tabs.blade.php`: Added `article-types` tab entry.
  - `resources/views/components/master-data-tabs.blade.php`: Removed `article-types` tab entry.
  - `config/permissions.php`: Moved `article_types` group from Master Data section to Operations section.
  - `resources/views/operations/index.blade.php`: Added Article Types metric card (grid expanded to `xl:grid-cols-5`), added quick access button, updated subtitle.
  - `resources/views/master-data/index.blade.php`: Removed Article Types card (grid adjusted to `xl:grid-cols-3` for Clients, Employees, Sites), updated subtitle, and restructured overview cards.
  - `lang/en.json`, `lang/ar.json`, `lang/fr.json`: Added 11 synchronized trilingual translation keys for modified dashboard layouts.
  - `tests/Feature/BusinessModulesTest.php`: Moved `operations.article-types` into operations test, removed from master-data test.
- **Permissions synced** via `php artisan permissions:sync-tables`.

---

## [1.0.10] — 2026-09-12 — Module Restructuring: Quantities & Units → Metrology

### Changed
- **Moved `Quantities & Units` from Master Data → Metrology module** (architectural correction):
  - Route renamed: `master-data.units` → `metrology.units` (`GET /metrology/units`).
  - Controller method moved: `MasterDataController::units()` → `MetrologyController::units()`.
  - View relocated: `resources/views/master-data/units.blade.php` → `resources/views/metrology/units.blade.php` (sidebar updated to `x-metrology-tabs`).
  - `resources/views/components/metrology-tabs.blade.php`: Added `units` tab entry.
  - `resources/views/components/master-data-tabs.blade.php`: Removed `units` tab entry.
  - `config/permissions.php`: Moved `quantities_units` group from Master Data section to Metrology section.
  - `resources/views/metrology/index.blade.php`: Added Quantities & Units metric card (grid expanded to `xl:grid-cols-5`).
  - `resources/views/master-data/index.blade.php`: Removed Quantities & Units card (grid reduced to `xl:grid-cols-4`), updated subtitle and Quick Access section.
  - `tests/Feature/BusinessModulesTest.php`: Moved `metrology.units` into metrology test, removed from master-data test.
- **Permissions synced** via `php artisan permissions:sync-tables`.
- **All 279 tests pass** (8 BusinessModulesTest assertions: 55).

---

## [1.0.9-patch] — 2026-09-12 — Hotfix: Master Data Missing Views

### Fixed
- Created missing `resources/views/master-data/units.blade.php` view (was mistakenly generated in `metrology/` directory).
- Created missing `resources/views/master-data/article-types.blade.php` view (was mistakenly generated in `operations/` directory).
- Removed the misplaced `metrology/units.blade.php` and `operations/article-types.blade.php` that belonged to the master-data module.
- All 279 tests now pass (BusinessModulesTest: 8 tests, 55 assertions — 100%).

---

## [1.0.9] — 2026-09-12 — Business Module Dashboards Architecture & Executive Workspace Portal

### Added — Executive Dashboards & Navigation Architecture
- **Dedicated Business Module Dashboards**:
  - Implemented `Metrology & Equipments Dashboard` (`resources/views/metrology/index.blade.php`) with metric cards for Measuring Instruments, Equipment, Calibrator Movements, and Calibration Certificates.
  - Implemented `Operations & Projects Dashboard` (`resources/views/operations/index.blade.php`) with metric cards for Mission Management, Contracts, Attachments List, and Warranties.
  - Implemented `Internal & Analytical Management Dashboard` (`resources/views/analytics/index.blade.php`) with metric cards for Expenses & Charges, Annual Forecasts, Company Statistics, and Reports Management.
  - Implemented `Master Data Dashboard` (`resources/views/master-data/index.blade.php`) with metric cards for Clients, Employees, Sites, Quantities & Units, and Article Types List.
  - Modernized `Workspace Portal` (`resources/views/workspace.blade.php`) into a central executive hub with role-based module cards, quick access triggers, and system infrastructure links.
- **Controller Action Extensions**:
  - Added `index(): View` to `MetrologyController` guarded by `Gate::authorize('view metrology')`.
  - Added `index(): View` to `OperationsController` guarded by `Gate::authorize('view operations')`.
  - Added `index(): View` to `AnalyticsController` guarded by `Gate::authorize('view analytics')`.
  - Added `index(): View` to `MasterDataController` guarded by `Gate::authorize('view master data')`.
- **Route Definitions & Dual Aliases (`routes/web.php`)**:
  - Registered index endpoints and dedicated dashboard aliases: `dashboard_metrology`, `metrology.index`, `dashboard_operations`, `operations.index`, `dashboard_analytics`, `analytics.index`, `dashboard_master_data`, `master-data.index`.
- **Sidebar Tab Components Upgrade**:
  - Added `Overview` / `Dashboard` tab as the first item with `active="index"` across `<x-metrology-tabs>`, `<x-operations-tabs>`, `<x-analytics-tabs>`, and `<x-master-data-tabs>`.
- **Navigation Synchronization (LTR & RTL & Mobile)**:
  - Aligned top-level direct buttons and active matching (`request()->routeIs('dashboard_*') || request()->routeIs('*.*')`) in `navigation-ltr.blade.php` and `navigation-rtl.blade.php`.
  - Added complete responsive drawers in mobile navigation with `@can` permission gates.
- **Trilingual Localization Parity**:
  - Added 63 new localized keys across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` maintaining 100% 1-to-1 key parity (671 total keys).
- **Automated Feature Tests**:
  - Added full test coverage in `tests/Feature/BusinessModulesTest.php` verifying Super-Admin access to all module dashboards and strict 403 authorization gating for unauthorized users (279 passing tests, 1201 assertions).

---

## [1.0.8] — 2026-09-11 — Business Module Tabs Architecture, Pages & Granular RBAC Permissions

### Added — Business Modules & Tab Components Architecture
- **Sidebar Tab Components Suite**:
  - Implemented `<x-metrology-tabs>` (`resources/views/components/metrology-tabs.blade.php`) matching the System Control sidebar tab architecture for Measuring Instruments, Equipment, Calibrator Movements, and Calibration Certificates.
  - Implemented `<x-operations-tabs>` (`resources/views/components/operations-tabs.blade.php`) for Mission Management, Contracts, Attachments List, and Warranties.
  - Implemented `<x-analytics-tabs>` (`resources/views/components/analytics-tabs.blade.php`) for Expenses & Charges, Annual Forecasts, Company Statistics, and Reports Management.
  - Implemented `<x-master-data-tabs>` (`resources/views/components/master-data-tabs.blade.php`) for Clients, Employees, Sites, Quantities & Units, and Article Types List.
- **Dedicated Thin Business Controllers**:
  - Created `App\Http\Controllers\MetrologyController` with `Gate::authorize()` on all 4 actions.
  - Created `App\Http\Controllers\OperationsController` with `Gate::authorize()` on all 4 actions.
  - Created `App\Http\Controllers\AnalyticsController` with `Gate::authorize()` on all 4 actions.
  - Created `App\Http\Controllers\MasterDataController` with `Gate::authorize()` on all 5 actions.
- **17 Standardized Module Blade Views**:
  - Created full responsive page templates for all 17 sub-module pages across `resources/views/metrology/`, `resources/views/operations/`, `resources/views/analytics/`, and `resources/views/master-data/`.
  - Enforced unified `<x-app-layout>`, sticky sidebar tabs, standardized `<x-table>` component suite with `<x-table.empty>`, and `<x-primary-button>` action controls.
- **Static Permissions Catalog & Automated DB Synchronization (`config/permissions.php`)**:
  - Registered 24 static business modules with granular CRUD permissions (view, create, edit, delete) and top-level view permissions (`view metrology`, `view operations`, `view analytics`, `view master data`).
  - Synchronized 72 new permissions into the database and assigned full privileges to `Super-Admin` via `permissions:sync-tables`.
- **Top Navigation Dropdown Integration**:
  - Connected all navigation links in both `navigation-ltr.blade.php` and `navigation-rtl.blade.php` to named routes.
  - Added `@canany` and `@can` permission gates around module dropdowns and items.
  - Added active route indicators (`:active="request()->routeIs('...')"`).
- **Trilingual Localization Parity (Arabic, English, French)**:
  - Added 17 descriptive page subtitles across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` maintaining 100% key parity.
- **Automated Feature Test Suite (`tests/Feature/BusinessModulesTest.php`)**:
  - Added 6 automated feature tests verifying Super-Admin access, standard user authorization blocks (403), and granular permission gating. 100% passing (277 tests, 1186 assertions).

---

## [1.0.7] — 2026-09-11 — Media Optimization, CAS Deduplication & Safe Reference-Aware Deletion (ADR-009)

### Added & Changed — Media Processing & Storage Architecture
- **Content-Addressable Storage (CAS) & Deduplication (`config/media.php`, `MediaOptimizationService.php`)**:
  - Implemented cryptographic SHA-256 content hashing for optimized images and documents.
  - Automatically detects and reuses existing stored files when identical images are uploaded by different or same users, preventing redundant physical files on disk.
- **Reference-Aware Safe Deletion Protocol (`MediaOptimizationService.php`)**:
  - Implemented `isAssetInUse($path, $excludeUserId)` and `safeDelete($path, $disk, $excludeUserId)` methods.
  - Verifies whether any other user or active record is currently using a media asset before deleting it from physical storage.
  - Preserves shared deduplicated files if other records still point to them; permanently deletes the physical file from disk only when the reference count reaches zero.
- **Lifecycle Observers & Controller Integration (`UserObserver.php`, `ProfileController.php`)**:
  - Hooked `UserObserver::saving()` to invoke `safeDelete()` on the old profile photo path when a user changes their photo.
  - Hooked `UserObserver::deleted()` to invoke `safeDelete()` when a user account is deleted.
  - Updated `ProfileController` to use `safeDelete()` instead of unconditional file deletion.
- **Centralized Media Configuration (`config/media.php`)**:
  - Implemented settings for image processing (`imagick`/`gd` driver selection, 1920px max width, 80% WebP quality, EXIF stripping).
  - Configured deduplication flags (`deduplication.enabled` and `deduplication.hash_filenames`).
  - Configured Ghostscript PDF compression (`/ebook` 150dpi, compatibility 1.4, timeout 60s, resilient fallback).
  - Configured scratch temporary media directory (`storage/app/temp-media`).
- **Universal AI Standing Rules Codification**:
  - Embedded mandatory media processing, CAS deduplication, and reference-aware safe deletion rules across all rule catalogs: `AGENTS.md`, `CLAUDE.md`, `.antigravityrules`, and `.ai/rules/media.md` (registered in `.ai/rules/index.md`).
  - Strict prohibition of raw `$file->store()`, raw `Storage::putFile()`, and raw `Storage::delete()` across the entire codebase.
- **FileUploadService Harmonization (`app/Services/FileUploadService.php`)**:
  - Upgraded `deleteFile()` to delegate to `MediaOptimizationService::safeDelete()`, ensuring zero accidental unlinking of shared deduplicated assets across the application.
  - Added `optimizeMedia()` method providing unified access to media optimization for generic upload workflows.
- **Feature Test Suite (`tests/Feature/MediaOptimizationTest.php`)**:
  - Added 9 comprehensive automated feature tests verifying downscaling, format conversion, temporary file destruction, PDF routing, avatar uploads, CAS deduplication, shared-file preservation, and final safe purge. 100% test suite passing (270 tests).

---

## [1.0.6] — 2026-09-11 — Permission Matrix Static Catalog Binding & Module/Entity Modernization

### Fixed & Changed — RBAC Permission Matrix & System Roles View
- **Role Permission Matrix Resolution Fix (`PermissionDiscoveryService.php`)**:
  - Identified and removed legacy `SYSTEM_BLACKLIST` filtering in `getGroupedPermissionMatrix()` that was hiding `roles` and `permissions` from the matrix.
  - Aligned matrix entity population with the static registry `$this->getDiscoveredTables()`, ensuring all configured modules (`users`, `roles`, `permissions`) render with their 4 CRUD privileges.
- **UI Modernization in Role Modals (`resources/views/system/roles.blade.php`)**:
  - Replaced legacy column header `{{ __('Table / Entity') }}` with `{{ __('Module / Entity') }}` in both the Create System Role and Edit System Role modal tables.
- **Internationalization (`lang/*.json`)**:
  - Added `"Module / Entity"` key to all three language dictionaries (`"الوحدة / الكيان"` in Arabic, `"Module / Entity"` in English, `"Module / Entité"` in French) maintaining strict 1-to-1 parity at 570 keys each.
- **System Activity Alert Normalization (`SystemActivityAlert.php`)**:
  - Standardized default `$causer` value to canonical English master key `'System'`, aligning with strict trilingual rules and passing all feature notification tests.
- **Unit Test Suite Alignment (`PermissionDiscoveryServiceTest.php`)**:
  - Updated test assertions to verify that statically registered entities (`roles`, `permissions`, `users`) are correctly discovered and populated in the permission matrix. All 261 tests pass.

---

## [1.0.5] — 2026-09-11 — Static System Roles Registry & Configured Roles UI Modernization

### Added & Changed — RBAC & Static Catalog Architecture
- **Static System Roles Catalog (`config/permissions.php`)**:
  - Integrated `'roles'` registry declaring baseline system roles (`Super-Admin`, `Admin`, `User`) alongside their functional titles, scopes, and system flags (`is_super`, `is_default`).
  - Decoupled role metadata from hardcoded class properties, establishing `config/permissions.php` as the single source of truth for both permissions and roles.
- **Service Layer Modernization (`PermissionDiscoveryService.php`)**:
  - Refactored `getSuperRoles()`, `getDefaultRole()`, `getRoleFunctionalTitle()`, and `getRoleFunctionalScope()` to dynamically resolve from `config('permissions.roles')` with resilient fallbacks.
  - Linked Super-Admin permission sync loops directly to configured super roles.
- **Configured Roles UI Alignment (`resources/views/system/roles.blade.php`)**:
  - Added `<x-badge variant="success">{{ __('Static Registry') }}</x-badge>` to the Configured Roles header.
  - Modernized the section subtitle from referencing raw database tables (`RBAC Tables: roles, role_has_permissions, model_has_roles`) to the static catalog: `({{ __('Static Registry') }}: config/permissions.php)`.
  - Updated granted privileges phrasing to refer to module/system privileges rather than raw tables.
- **Trilingual Dictionary Parity (`lang/*.json`)**:
  - Added new localized strings (`Role definitions, functional scopes, and granted privileges`, `Custom user role with assigned granular system privileges.`) across `en`, `ar`, and `fr` with 100% 1-to-1 parity (569 keys each).

---

## [1.0.4] — 2026-09-11 — Comprehensive Codebase Translation Audit & 100% English Master Key Normalization

### Changed & Fixed — Internationalization Architecture
- **Full Codebase Translation Audit**:
  - Scanned all 432 distinct translation keys in Blade views and 59 distinct keys in `app/`.
  - Confirmed 0 unwrapped/hardcoded Arabic strings across all Blade views.
  - Verified 100% of Blade and backend keys are strictly registered in dictionaries.
- **Normalized Observer Notification Payloads to English Master Keys**:
  - Refactored `UserObserver.php` to dispatch notification titles and messages in canonical English (`New User`, `User Deleted`, `System`) instead of Arabic database payloads.
  - Updated `tests/Feature/DatabaseNotificationTest.php` to assert English payload keys.
- **Abolished Remaining Arabic Dictionary Keys**:
  - Removed legacy reverse-translation bridge keys (`"مستخدم جديد"` and `"حذف مستخدم"`) from `lang/en.json`, `lang/ar.json`, and `lang/fr.json`.
  - Added missing keys (`"Assigned Users"`, `"This action cannot be undone."`, `"Add a new user account."`).
  - Achieved **flawless 1-to-1 key parity** across all 3 language files with exactly 567 English master keys each and 0 Arabic characters in dictionary keys.

---

## [1.0.3] — 2026-09-11 — AI Assistant Governance: Unified English Master Keys & Mandatory RBAC Integration

### Added — AI Developer Guidelines & Security Protocols
- **Unified English Master Keys Rule**:
  - Enforced canonical English authoring for all translation keys passed to `__('...')`, `@lang('...')`, validation attributes, and backend notification messages.
  - Strictly prohibited Arabic or French keys in code dictionaries.
  - Mandated 1-to-1 parity across all three language files (`lang/en.json`, `lang/ar.json`, `lang/fr.json`).
- **Mandatory RBAC Integration & Static Catalog Registration Rule**:
  - Enforced strict permission protection (`permission:...` middleware, `$this->authorize()`, `@can(...)`) on every new service, page, controller, or route.
  - Mandated registering new permissions in the static catalog (`config/permissions.php`) and syncing with `php artisan permissions:sync-tables`.
  - Established the **Mandatory Developer Clarification Protocol**: if the AI assistant is unsure of permission naming or grouping, it must halt and explicitly consult the developer before modifying code.
- **Rules Documentation Sync**:
  - Synchronized rules across `AGENTS.md`, `.antigravityrules`, `CLAUDE.md`, and `.ai/rules/permissions.md`.

---

## [1.0.2] — 2026-09-11 — Static Permissions Catalog Registry (Abolished Schema Introspection)

### Changed — Security & Permissions Architecture
- **Abolished Dynamic Schema Introspection**:
  - Completely eliminated dynamic database schema queries (`Schema::getTables()`, `Schema::getTableListing()`).
  - Implemented the static code-first registry in `config/permissions.php` scoped strictly to core sovereign modules: `users_management`, `roles_management`, and `permissions_management`.
- **Refactored `PermissionDiscoveryService`**:
  - Service now ingests `config('permissions.groups')` directly, ensuring 100% predictable, version-controlled permissions.
  - Automatic auto-pruning removes stale or unwanted permissions (such as phantom `media_assets` and `media_variants`) while synchronizing standard CRUD actions (`view`, `create`, `edit`, `delete`) for declared entities.
  - Automatically provisions all 12 active permissions to the `Super-Admin` role.
- **Modernized UI & Localization (`system/roles.blade.php`)**:
  - Updated the Permissions Catalog header to reflect the **Static Registry** badge and descriptive copy.
  - Translated module entities and matrix descriptions with synchronized trilingual dictionary support across Arabic, English, and French.
- **Console Synchronization Command (`permissions:sync-tables`)**:
  - Updated command description and output to report static module synchronization without schema introspection.

---

## [1.0.1] — 2026-09-11 — Profile Photo Storage Link & Reactive Preview Fix

### Fixed — Profile Photo Storage & Preview Pipeline
- **Storage Directory Junction (`public/storage`)**:
  - Replaced isolated/disconnected `public/storage` folder with an NTFS directory junction pointing to `storage/app/public` via `php artisan storage:link`.
  - Resolved 404/403 missing image errors on newly uploaded user photos (`storage/app/public/photos/`).
- **Domain Configuration Synchronization (`.env`)**:
  - Updated `APP_URL` from `http://tools.gmtm-dz.com.test` to `http://erp.gmtm-dz.com.test` to match Laravel Herd virtual host mappings.
  - Aligned `Storage::disk('public')->url()` and `asset('storage/...')` outputs with the current application domain.
- **Client-Side Reactive Preview (`update-profile-information-form.blade.php`)**:
  - Replaced asynchronous `FileReader` and base64 string allocations with instantaneous, zero-overhead `URL.createObjectURL(file)`.
  - Replaced unstable sibling `<template x-if>` avatar containers with reliable `x-show` toggles (`x-show="photoPreview"` and `x-show="!photoPreview"`).
  - Added `x-on:error="photoPreview = null"` to smoothly fallback to letter avatar if an image URL fails to load.
  - Standardized "Remove Photo" button to use `<x-danger-button>`.
- **Navigation & Profile Views Unification**:
  - Unified avatar source references across `profile/edit.blade.php`, `navigation-rtl.blade.php`, and `navigation-ltr.blade.php` to use `$user->profile_photo_url` / `Auth::user()->profile_photo_url`.

---

## [1.0.0] — 2026-09-10 — Point Zero Master Core Kernel Baseline

Initial release of the production-ready Enterprise Application Boilerplate and SaaS Master Kernel.

### Added — Self-Healing & Infrastructure Engine
- **Dual-Layer Database Auto-Creation & Provisioning (`EnsureDatabaseIsMigrated`)**:
  - Automatically intercepts uninitialized or missing database connections (`1049 Unknown database` or PostgreSQL/SQLite equivalents).
  - Connects directly to server host via raw PDO and silently executes `CREATE DATABASE IF NOT EXISTS \`db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`.
  - Reconnects on the fly and triggers silent migration, role seeding, and table CRUD permission discovery.
- **Graceful Fallback Database Error View (`resources/views/errors/database.blade.php`)**:
  - Renders a localized, Dark/Light mode diagnostics view with HTTP status `503 Service Unavailable` if the database is unreachable, with connection parameters, step-by-step troubleshooting suggestions, and retry CTA.
- **Zero-State Super Admin Onboarding Gate (`EnsureSuperAdminExists`)**:
  - Automatically detects empty user database (`User::count() === 0`) and redirects all incoming guest traffic to `/system-tables/setup` to provision the primary Super Admin.
  - Permanently seals the setup route (`404 Not Found` on GET, `403 Forbidden` on mutation) once initialized.

### Added — Authentication, Security & RBAC
- **Laravel Breeze Authentication**: Integrated Blade + Alpine.js session authentication with `MustVerifyEmail` and dynamic registration shields.
- **Enterprise Spatie Roles & Permissions**: Configured roles (`Super-Admin`, `Admin`, `User`) with immutability guarantees.
- **Automated Permission Discovery (`PermissionDiscoveryService`)**: Inspects database tables dynamically and generates standard CRUD permissions on the fly.
- **Anti-Self-Action Security Policy**: Hardened guards preventing administrators from self-demotion, self-suspension, or self-deletion.
- **Private Forensic System Tables Module (`system-tables.*`)**: Quarantined dashboard for user sessions, activity log audit trail, database notifications, backups, and automated data pruning.

### Added — Trilingual Localization Engine
- **Native Trilingual Synchronization**: 100% key parity across Arabic (RTL, default), English (LTR), and French (LTR) dictionaries (`lang/ar.json`, `lang/en.json`, `lang/fr.json`) with 559 keys each.
- **Asset Isolation**: Separate RTL and LTR stylesheet and script compilation via Vite to guarantee zero visual flash (Zero-FOUC).

### Added — Unified Design System
- **Zero Inline Styles**: 100% compliance with strict styling isolation using Tailwind CSS utility tokens.
- **Standardized Button Suite**: Semantic palette (`<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`).
- **Standardized Table Suite**: Modular `<x-table>` components with sorting, empty states, and standardized row action buttons (`<x-table.action-view>`, `<x-table.action-edit>`, `<x-table.action-delete>`).
- **Zero-FOUC Theme Switcher**: Class-based Light, Dark, and System theme synchronizer backed by Alpine.js store and localStorage.

### Added — Dynamic System Settings
- **Persistent Key-Value Store (`system_settings`)**: Dynamic runtime parameter registry with high-performance persistent caching (`86400` TTL) and Super Admin control dashboard.
- **Real-Time Registration Shield**: Instant toggle switch with bilingual knob positioning and route middleware protection.

### Added — Testing & Code Quality
- **Automated Test Suite**: 261 test cases, 1109 assertions with 100% passing rate.
- **Code Standards**: 100% compliant with Laravel Pint PSR-12/PER formatting standards.
