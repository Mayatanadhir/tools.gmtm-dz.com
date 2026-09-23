---
paths:
  - 'app/Enums/**'
---

# Enums

## Pure PHP Enums and Semantic Badge Variant Contract
Enums representing roles, positions, or statuses must never return raw HTML or CSS class strings (e.g. badgeClass). They must strictly declare badgeVariant(): string returning a standardized semantic token ('primary', 'info', 'neutral', 'success', 'danger', 'warning') to preserve backend/frontend separation of concerns and allow views to bind directly into <x-badge :variant="...">.
