---
paths:
  - app/Http/Controllers/SystemTableController.php
---

# Controllers

## ADR-033: Role & Permission Deletion Pattern
destroyRole and destroyPermission follow the same activity-log-before-delete pattern: capture the name before deletion, log with `roles_permissions` channel, then call `->delete()`. No soft deletes — Spatie models are hard-deleted. Both return a redirect to system-tables.roles with a `status` flash. Routes: DELETE /roles/{role} → roles.destroy, DELETE /permissions/{permission} → permissions.destroy.
