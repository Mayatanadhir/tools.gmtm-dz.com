# Project State: tools.gmtm-dz.com

**Last Updated:** 2026-09-08
**Status:** Localization, Theme Management & Profile Module Clean Architecture Active

---

## 1. Application Overview
- **Framework:** Laravel 13.x (PHP 8.4+)
- **Application Brand:** ENGI-MATE ("Your Engineering Work Assistant")
- **Frontend Stack:** Blade + Tailwind CSS (Class-based Dark Mode) + Alpine.js + Vite (Isolated RTL/LTR bundles)
- **Locales Supported:** `ar` (Arabic, default, hidden prefix), `en` (English, `/en/`), `fr` (French, `/fr/`)
- **Themes Supported:** `light`, `dark`, `system` (Zero-FOUC prevention script, Alpine.js reactive store, cross-instance sync)
- **Database Engine:** MySQL (`gmtmdz_tools`)
- **Authentication:** Laravel Breeze (Session/Blade based)
- **Test Suite:** 118 tests, 378 assertions (100% passing)

---

## 2. Database Schema Snapshot

### Tables:
- `users`: Standard authentication table (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `timestamps`).
- `password_reset_tokens`: Password reset handling (`email`, `token`, `created_at`).
- `sessions`: Database session driver table.
- `cache` & `cache_locks`: Database cache storage.
- `jobs`, `job_batches`, `failed_jobs`: Database queue driver tables.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: Spatie RBAC tables.
- `activity_log`: Spatie audit trail and event logging table.
- `notifications`: Laravel database notifications table (`id (uuid)`, `type`, `notifiable_type`, `notifiable_id`, `data (json)`, `read_at`, `timestamps`).

---

## 3. Registered Models
- `App\Models\User`: Authenticatable user model (includes `FilterableTrait`, `HasActivity`, `HasFactory`, `HasRoles`, `Notifiable`).

---

## 4. Routes & Endpoints
- **Localized Web Routes** (`routes/web.php` wrapped in `LaravelLocalization::groupRoutes`):
  - Arabic (default, no prefix):
    - `GET /` -> Public welcome page (`welcome.blade.php`).
    - `GET /dashboard` -> Authenticated user dashboard (`dashboard.blade.php`).
    - `GET /profile` -> Edit user profile (`ProfileController@edit`).
    - `PATCH /profile` -> Update profile details (`ProfileController@update`).
    - `DELETE /profile` -> Delete account (`ProfileController@destroy`).
    - Auth routes (`login`, `register`, `forgot-password`, `reset-password`, etc.).
  - English (`/en/...`) and French (`/fr/...`):
    - Prefixed mirror routes (`en.dashboard`, `fr.dashboard`, `en.profile.edit`, `fr.profile.edit`, etc.).
- **API Notification Routes** (`routes/api.php`) — Auth-protected:
  - `GET /api/notifications` → `NotificationController@index` (paginated all notifications)
  - `GET /api/notifications/unread` → `NotificationController@unread` (unread count + list)
  - `PATCH /api/notifications/read-all` → `NotificationController@markAllAsRead`
  - `PATCH /api/notifications/{id}/read` → `NotificationController@markAsRead`
  - `DELETE /api/notifications/{id}` → `NotificationController@destroy`

---

## 5. Architectural Components
- **Traits (`app/Traits`):** `ApiResponseTrait` (unified API responses with success/error envelopes), `FilterableTrait` (declarative dynamic request query filtering and search scope).
- **Interfaces (`app/Interfaces`):** `BaseRepositoryInterface` (includes `filter` & `paginateWithFilter`), `UserRepositoryInterface`.
- **Repositories (`app/Repositories`):** `BaseRepository` (abstract base with dynamic filtering), `UserRepository`.
- **Services (`app/Services`):** `BaseService` (transaction manager & exception handling), `UserService` (domain logic), `ImageOptimizationService` (image compression & scaling), `FileUploadService` (standardized secure file uploads & storage management).
- **Notifications (`app/Notifications`):** `SystemActivityAlert` (database-channel-only notification with unified payload: `title`, `message`, `type`, `causer`, `extra`).
- **Observers (`app/Observers`):** `UserObserver` (captures `created` & `deleted` events on User model; auto-dispatches `SystemActivityAlert` to all `Super-Admin` and `Admin` role users; gracefully handles missing roles).
- **Controllers:**
  - `App\Http\Controllers\ProfileController`: Refactored with `declare(strict_types=1);`, delegates `update` and `destroy` to `UserService` (transaction-wrapped).
  - `App\Http\Controllers\Api\NotificationController`: API (`index`, `unread`, `markAsRead`, `markAllAsRead`, `destroy`; uses `ApiResponseTrait`; enforces per-user notification isolation).
- **Console Commands (`app/Console/Commands`):** `OptimizeImagesCommand` (`php artisan images:optimize`).
- **Providers (`app/Providers`):** `RepositoryServiceProvider` (maps repository interfaces to implementations).
- **Middleware (`app/Http/Middleware`):** `SetLocale` (guarantees runtime locale synchronization).
- **Middleware Aliases (`bootstrap/app.php`):** `role` (RoleMiddleware), `permission` (PermissionMiddleware), `role_or_permission` (RoleOrPermissionMiddleware), `localize` (LaravelLocalizationRoutes), `localizationRedirect` (LaravelLocalizationRedirectFilter), `localeSessionRedirect` (LocaleSessionRedirect), `localeCookieRedirect` (LocaleCookieRedirect), `localeViewPath` (LaravelLocalizationViewPath).
- **Views & Layout Isolation (`resources/views`):**
  - Layouts: `layouts/app-rtl.blade.php`, `layouts/app-ltr.blade.php`, `layouts/guest-rtl.blade.php`, `layouts/guest-ltr.blade.php` (all embedded with Zero-FOUC prevention scripts; legacy unisolated Breeze files `app`, `guest`, `navigation` purged).
  - Navigation: `layouts/navigation-rtl.blade.php`, `layouts/navigation-ltr.blade.php` (with responsive desktop/mobile theme & language switchers, and standalone enlarged brand logo lockup `h-12 w-auto sm:h-14` without redundant text labels).
  - Profile: `profile/edit.blade.php`, `profile/partials/update-profile-information-form.blade.php`, `profile/partials/update-password-form.blade.php`, `profile/partials/delete-user-form.blade.php` (all with complete Dark Mode styling).
  - Components: `AppLayout` (dynamic RTL/LTR resolution), `GuestLayout` (dynamic RTL/LTR resolution), `x-language-switcher`, `x-theme-switcher`, and Dark-Mode enabled form & navigation components (`x-nav-link`, `x-input-label`, `x-text-input`, `x-input-error`, `x-primary-button`, `x-secondary-button`, `x-danger-button`, `x-modal`, `x-dropdown`, `x-dropdown-link`, `x-responsive-nav-link`, `x-auth-session-status`).
- **Vite Bundles (`resources/css`, `resources/js`):**
  - RTL: `app-rtl.css`, `app-rtl.js`
  - LTR: `app-ltr.css`, `app-ltr.js`
- **Tailwind Configuration:** `darkMode: 'class'` in `tailwind.config.js`.
- **Assets & Storage Structure:**
  - Static Web Assets: `public/images/` (dual-theme transparent logos `logo.png` [Light] and `logo-dark.png` [Dark], icons, graphics; accessed via `asset('images/...')`).
  - Dynamic Uploads: `storage/app/public/instruments/` (linked to `public/storage/instruments/` via active junction; accessed via `asset('storage/instruments/...')`).
- **Scheduled Tasks (`routes/console.php`):** `backup:clean` (01:00 daily), `backup:run` (01:30 daily).
- **Actions (`app/Actions`):** None yet.
- **Form Requests (`app/Http/Requests`):** `ProfileUpdateRequest`, `Auth\LoginRequest`.
- **API Resources:** None yet.

---

## 6. Installed Key Dependencies
- `laravel/framework` (13.x)
- `laravel/breeze` (2.4+)
- `laravel/boost` (2.7)
- `laravel/pint` (1.27)
- `mcamara/laravel-localization` (2.4+)
- `spatie/laravel-permission` (8.3)
- `spatie/laravel-activitylog` (5.1)
- `spatie/laravel-backup` (10.3+)
- `intervention/image` (4.3+)
- `phpunit/phpunit` (12.5+)

---

## 7. AI Agent Integrations & MCP
- **Laravel Boost MCP Server:**
  - Globally configured: `~/.gemini/config/mcp_config.json` (pointing to `C:\Users\HP\.config\herd\bin\php84\php.exe` and `c:\Project HARD\tools.gmtm-dz.com\artisan boost:mcp`).
  - Workspace plugin: [.agents/plugins/laravel-boost/](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/plugins/laravel-boost/) (`plugin.json`, `mcp_config.json`).
  - Workspace root config: [.agents/mcp_config.json](file:///c:/Project%20HARD/tools.gmtm-dz.com/.agents/mcp_config.json).
  - Environment: `C:\Users\HP\.config\herd\bin` added to Windows User `PATH`.
  - Exposes tools: `database-query`, `database-schema`, `get-absolute-url`, `browser-logs`, `search-docs`.

---

## 8. Standing Architectural Directives
- **Rule 12 (Strict Separation of Concerns):**
  - Backend isolation: Pure PHP logic in `app/`, `routes/`, `database/`; no HTML, inline styles, or frontend scripts in PHP controllers, actions, services, or models.
  - Frontend isolation: UI markup in `resources/views/` and `resources/js/`; no database queries or Eloquent business logic in views.
  - CSS & Styling isolation: Absolute prohibition of inline `style="..."` attributes in HTML/Blade markup. Utility-first Tailwind classes in templates, or dedicated CSS files in `resources/css/`. Zero raw `<style>` tag injections in PHP or JS components.


