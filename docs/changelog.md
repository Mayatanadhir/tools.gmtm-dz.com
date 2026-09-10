# Changelog: ENGI-MATE Core Kernel (Point Zero)

All notable changes, features, refactorings, and fixes for this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> [!NOTE]
> Detailed historical changelog prior to Point Zero Core Kernel extraction is preserved in `docs/archive/legacy_changelog.md`.

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
