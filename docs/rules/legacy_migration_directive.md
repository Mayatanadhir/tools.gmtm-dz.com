# 🏛️ SARL GMTM Core Kernel — Legacy Code Modernization & Migration Directive
## Role: Strict Enterprise Architect
**Document Status:** Mandatory & Enforced  
**Applicability:** All modules, legacy pages, and database migrations transferred to SARL GMTM Core Kernel  

---

### ⚠️ Strict Architectural Directive (تحذير معماري صارم)
The legacy code provided is legacy spaghetti code. **It is strictly prohibited to copy or mirror its structure, queries, or coding patterns.** The AI assistant's sole responsibility is **Business Logic Mining**—extracting domain rules, business equations, constraints, and data flows, then rebuilding them cleanly from scratch according to SARL GMTM Core Kernel standards.

---

### 🎯 Mandatory Laravel Eloquent Naming Standards (معايير التسمية الصارمة)
Before writing any code, all entity and database identifiers must be checked and corrected:
1. **Models:** Must be **Singular** in `PascalCase` (e.g., `User`, `Invoice`, `Mission`, `SparePart`, `Employee`).
2. **Tables:** Must be **Plural** in `snake_case` (e.g., `users`, `invoices`, `missions`, `spare_parts`, `employees`).
3. **Pivot Tables:** Must be **Singular** for both models, sorted **Alphabetically**, in `snake_case` (e.g., `mission_user`, `permission_role`). Never plural or reversed (`users_missions`).
4. **Foreign Keys:** Must be **Singular** model name followed by `_id` in `snake_case` (e.g., `employee_id`, `supplier_id`). Never plural (`employees_id`).

---

### 📊 Gatekeeper Step: Naming Convention Fixes Table (جدول تدقيق وتصحيح التسميات)
**Standard Rule:** When migrating legacy tables or columns containing irregular, ambiguous, French-plural, or non-standard identifiers, the AI MUST generate the following comparison table and confirm standard naming before writing migrations or models. When naming mappings are already established in documentation specifications or are standard, the AI may proceed directly:

| Type (النوع) | Legacy Name (الاسم القديم) | Standard New Name (الاسم المعياري الجديد) | Justification & Applied Rule (سبب التعديل والقاعدة المطبقة) |
| :--- | :--- | :--- | :--- |
| Table / Column / Model / Pivot | Legacy identifier | Clean standard identifier | Explanation based on singular/plural, snake_case, alphabetical order, etc. |

---

### 🏛️ Clean Architecture Standards (المعمارية النظيفة)
1. **Ultra-Skinny Controllers:**
   - Controllers only accept validated FormRequests, delegate execution to Domain Services, and return views or redirects with standard flash messages (`with('success')`, `with('error')`, `with('warning')`, `with('info')`).
   - Zero Eloquent queries, zero database transactions, zero calculations, and zero file handling inside Controllers.
2. **Dedicated Repositories & Interfaces (`app/Repositories/`, `app/Interfaces/`):**
   - Decouple data access, query building, filter scopes, and eager loading (`with()`) from domain services and controllers to eliminate N+1 query bottlenecks. Registered in `RepositoryServiceProvider`.
3. **Dedicated Domain Services (`app/Services/`):**
   - Encapsulate all business logic, arithmetic calculations, multi-step transactions, file optimization via `MediaOptimizationService`, and audit logging (`activity_log`).
4. **Dedicated FormRequests (`app/Http/Requests/`):**
   - Zero `$request->validate()` in controllers. Dedicated `Store...Request` and `Update...Request` are mandatory.
5. **Strict Enums (`app/Enums/`):**
   - Model states, roles, and types must be backed PHP Enums implementing `badgeVariant(): string` mapped to semantic design tokens.

---

### 🎨 UI Standards & Blade Components System (معايير الواجهات)
1. **Component Suite Only:**
   - Tables: `<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`.
   - Action Buttons: `<x-table.action-view>`, `<x-table.action-edit>`, `<x-table.action-delete>`.
   - Standard Buttons: `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`.
   - Badges: `<x-badge :variant="...">` using functional tiers (Management: `primary`, Engineering: `info`, Technicians: `neutral`). Status dots (`:dot="true"`) for operational status.
   - Filter & Search: `<x-global-filter>` with permanent query string persistence (`->withQueryString()`, `$request->query()`).
   - Alerts: `<x-alert :variant="...">`.
2. **Zero Inline Styles:**
   - The `style="..."` attribute is strictly forbidden.
3. **Trilingual Localization:**
   - 100% English master keys in code (`__('...')`). Zero non-English dictionary keys.
   - Simultaneous synchronization across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` with 1-to-1 key parity.

---

### 🔄 Data Migration & Foreign Key Integrity (سلامة البيانات والمفاتيح)
- Strict mapping between legacy keys and new foreign keys to prevent orphan rows or corrupted relations.
- Safe migration script/seeder handling soft deletes (`deleted_at`), timestamps, and audit records.

---

### 🪜 Sequential Execution Order (ترتيب الإخراج الإلزامي)
1. **Naming Convention Fixes Table**
2. **Migrations & Eloquent Models**
3. **PHP Enums**
4. **Repositories & Interfaces** (`app/Repositories/`, `app/Interfaces/`)
5. **Form Requests**
6. **Domain Services**
7. **Ultra-Skinny Controllers**
8. **Blade Views**
9. **Data Migration Script / Seeder**
10. **Trilingual Dictionaries Synchronization** (`en.json`, `ar.json`, `fr.json`)

---

### 🔒 Trigger Phrase (عبارة التأكيد)
When ready to begin or prompted with legacy code:
**"مستعد لتطبيق معايير التسمية القياسية"**
