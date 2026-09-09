---
paths:
  - 'resources/views/system/**'
---

# System

## x-crud-modal component usage (ADR-035)
Use `<x-crud-modal.delete>` and `<x-crud-modal.form>` for all create/edit/delete modals in the system explorer.

KEY RULES:
1. `<x-crud-modal.delete>`: `show`, `action-url`, `item-name` are Alpine variable NAMES — pass WITHOUT the `:` Blade prefix (e.g. `action-url="deleteRoleActionUrl"` not `:action-url="..."`).
2. `<x-crud-modal.form>` CREATE forms: use `:action-url="route('...')"` (static PHP URL with `:`).
3. `<x-crud-modal.form>` EDIT forms: use `alpine-action="editActionUrl"` (Alpine variable name as plain string, NO `:`).
4. Extra hidden inputs go in the named `$hidden` slot: `<x-slot:hidden>...</x-slot:hidden>`.
5. Icon color options: `orange` (create), `amber` (edit), `indigo` (info), `emerald` (success), `rose` (danger).
6. NEVER write raw inline modal dialogs in system views — always use these components.
