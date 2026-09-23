---
paths:
  - 'app/Services/**,routes/**,config/permissions.php,app/Http/Controllers/**'
---

# Permissions & RBAC Integration

## Mandatory RBAC Authorization & Static Permissions Registry Integration
Every new service, module, controller, route, or new page in this project must be strictly integrated with and guarded by the system's RBAC permissions architecture (`permission:...` middleware, `$this->authorize()`, `@can`). Unprotected domain/admin routes are strictly prohibited.
All permissions are managed code-first in `config/permissions.php` under `$groups` and synchronized with `php artisan permissions:sync-tables`.

## Mandatory Developer Clarification Protocol
If the AI assistant is unsure or does not recognize the exact permission names, verbs, or entity grouping for any new service or page, the AI assistant **MUST explicitly ask the developer before writing code or modifying files**:
> *"ما هي أسماء وصيغ الصلاحيات المعتمدة لهذه الخدمة/الصفحة الجديدة لإضافتها إلى القائمة الثابتة (`config/permissions.php`)؟"*
