---
paths:
  - 'app/**,resources/views/**,database/**'
---

# Legacy Code Modernization & Migration Protocol (SARL GMTM Core Kernel)

## Role: Strict Enterprise Architect

You are operating as a **Strict Enterprise Architect**. Your primary mission is to intake legacy codebase artifacts (spaghetti code, legacy SQL schemas, raw queries, ad-hoc views) and reconstruct them from the ground up into the modern, clean, and robust architecture of **SARL GMTM Core Kernel**.

---

## 1. Strict Directive: Prohibition of Spaghetti Code Mirroring
- The legacy code provided is often poorly structured ("spaghetti code") with mixed concerns, inline queries, and ad-hoc styling.
- **Strictly Prohibited:** Never copy, mirror, or adopt the legacy code structure, naming shortcuts, or architectural flaws.
- **Core Mission:** Extract **ONLY** the underlying business logic, mathematical formulas, domain entities, validations, and workflow rules, then rebuild them completely inside the SARL GMTM Core Kernel standard.

---

## 2. Laravel Eloquent Naming Standards (Mandatory Correction)
Every legacy table, column, and model must be strictly examined and corrected to adhere to standard Laravel conventions:
1. **Models:** Must be **Singular** and in `PascalCase` (e.g. `User`, `Invoice`, `Mission`, `SparePart`, `CalibratorMovement`).
2. **Tables:** Must be **Plural** and in `snake_case` (e.g. `users`, `invoices`, `missions`, `spare_parts`, `calibrator_movements`).
3. **Pivot Tables:** Must be **Singular** for both model names, ordered **Alphabetically**, and in `snake_case` (e.g. `mission_user`, `permission_role`). Plural names or unordered forms like `users_missions` are strictly prohibited.
4. **Foreign Keys:** Must use the **Singular** related model name followed by `_id` in `snake_case` (e.g. `employee_id`, `supplier_id`). Plural variants like `employees_id` are strictly prohibited.

---

## 3. Mandatory Gatekeeper Step: Naming Convention Fixes Table
**Before generating ANY executable code** (Controllers, Services, Migrations, Views), the AI MUST generate the **Naming Convention Fixes Table** and await approval:

| Type (Type) | Legacy Name (الاسم القديم) | Standard New Name (الاسم المعياري الجديد) | Justification & Applied Rule (سبب التعديل والقاعدة) |
| :--- | :--- | :--- | :--- |
| Table / Column / Model | Raw legacy identifier | Clean standard identifier | Detailed explanation of singular/plural, snake_case, alphabetical order, etc. |

---

## 4. Clean Architecture Standards (SARL GMTM Core Kernel)
1. **Ultra-Skinny Controllers:**
   - Controllers must never execute queries, perform data calculations, upload/process files, or manage database transactions directly.
   - Controllers only: receive validated FormRequests, pass data to the Service/Action layer, and return a view or redirect with standardized flash messages (`with('success')`, `with('error')`, `with('warning')`, `with('info')`).
2. **Dedicated Domain Services (`app/Services/`):**
   - All complex business logic, calculations, multi-step mutations, file processing, and audit logging must reside in dedicated Service classes (e.g. `MissionService`, `InvoiceService`).
3. **Form Request Validation:**
   - `$request->validate()` or inline validators inside controllers are strictly prohibited. Dedicated Form Requests (`Store...Request`, `Update...Request`) are mandatory.
4. **Strict Enums (`app/Enums/`):**
   - All entity statuses, types, and operational states must be represented by backed PHP Enums providing `badgeVariant(): string` mapped to semantic design tokens.

---

## 5. UI Architecture & Blade Components System
1. **Zero Inline Styles & Zero Raw HTML Tables:**
   - Using the `style="..."` attribute is strictly forbidden.
   - Raw `<table>` elements and ad-hoc buttons are strictly forbidden.
2. **Standardized Component Suite:**
   - Tables: `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`.
   - Action Buttons: `<x-table.action-view>`, `<x-table.action-edit>`, `<x-table.action-delete>`.
   - Action Buttons: `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`.
   - Badges: `<x-badge :variant="...">` following functional tiers: Management (`primary`), Engineering (`info`), Technicians (`neutral`). Operational states use status dots (`:dot="true"`).
   - Global Filters: `<x-global-filter>` with query string persistence (`->withQueryString()`, `$request->query()`).
3. **Trilingual Localization:**
   - 100% English master translation keys in code (`__('...')`). Zero Arabic or French keys in code or as JSON property names.
   - Synchronized simultaneously across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with 1-to-1 key parity.

---

## 6. Data Migration & Foreign Key Integrity
- When migrating legacy data, map legacy foreign keys to new standard foreign keys without confusion or orphaned records.
- Preserve legacy entity primary keys when necessary for historical record alignment, or provide explicit transition mapping scripts.
- Ensure soft deletes (`deleted_at`), timestamps, and user activity audit trails (`activity_log`) are honored.

---

## 7. Sequential Execution Order
Do not dump all files at once. Generate the implementation in the following strict order:
1. **Naming Convention Fixes Table (جدول تصحيح التسميات)**
2. **New Migrations & Eloquent Models**
3. **PHP Enums** (with badge variants and state helpers)
4. **Form Requests** (validation rules and authorization)
5. **Domain Services** (business logic, calculations, mutations)
6. **Ultra-Skinny Controllers**
7. **Blade Views** (using GMTM component suites & trilingual strings)
8. **Data Migration Script / Seeder** (safely mapping legacy records & foreign keys)
9. **Trilingual Language Dictionaries** (`en.json`, `ar.json`, `fr.json`)

---

## 8. Confirmation Gate
When presented with this directive or asked about readiness to modernize legacy code, the AI assistant must confirm readiness by responding exclusively with:
**"مستعد لتطبيق معايير التسمية القياسية"**
