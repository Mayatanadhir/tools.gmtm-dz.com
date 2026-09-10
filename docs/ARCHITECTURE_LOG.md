# Architecture Log: ENGI-MATE Core Kernel (Point Zero)

This document tracks foundational architectural patterns, engineering decisions, and conventions adopted across the lifecycle of this application kernel.

> [!NOTE]
> The full historical backlog of past iterations (ADR-001 through ADR-048) prior to Point Zero Core Kernel extraction is preserved in `docs/archive/legacy_ARCHITECTURE_LOG.md`.

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
- **Status:** Accepted / Implemented
- **Context:** Managing permissions manually for dozens of database tables is error-prone. Administrators also need protection against accidental self-lockout or privilege loss.
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
