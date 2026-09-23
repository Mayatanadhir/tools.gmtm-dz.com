---
paths:
  - 'app/Notifications/**'
  - 'resources/views/system/notifications.blade.php'
---

# Notifications & Alerts

## Mandatory Unified Alerts, Notifications, Functional Classification & Trilingual Parity
1. **Single Source of Truth for Alerts (`<x-alert>`):**
   - All in-page alerts and flash notifications must strictly use `<x-alert :variant="...">` (`success`, `danger`, `warning`, `info`, `primary`).
   - Standardized session flash keys: `with('success', __('...'))`, `with('error', __('...'))`, `with('warning', __('...'))`, `with('info', __('...'))`.
2. **Unified Notification Architecture (`SystemActivityAlert`):**
   - System notifications must follow the unified structured payload schema: `title`, `message`, `type`, `causer`, `extra`.
   - Action `type` must map to semantic tokens: `created` / `success` (`success`), `updated` / `warning` (`warning`), `deleted` / `danger` (`danger`), `info` (`info`).
3. **Mandatory Functional Role Classification (التصنيف حسب الوظيفة):**
   - Notification targeting must be scoped by the 3 functional tiers:
     - `Management & Executive Leadership` (`isManagement()`): Administrative events, user management, and security alerts.
     - `Engineering & Specialist Roles` (`isEngineer()`): Technical and calibration alerts.
     - `Field Operations & Technicians` (`isTechnician()`): Operational logistics and field task alerts.
   - Actor and recipient position chips in notifications must use `<x-badge>` mapped to functional tier variants (`primary`, `info`, `neutral`). State dots (`:dot="true"`) must be reserved for operational status (Read/Unread).
4. **Mandatory 100% English Master Keys & Trilingual Parity:**
   - All notification titles and message bodies must be authored in English as the master key in `__('...')`.
   - All notification keys must be registered across `lang/en.json`, `lang/ar.json`, and `lang/fr.json` maintaining 100% 1-to-1 key parity with zero non-English keys as dictionary keys.
