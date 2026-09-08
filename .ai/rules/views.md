---
paths:
  - 'resources/views/**'
---

# Views

## Mandatory Unified Button Components and Semantic Color System
Enforce unified button components and semantic color tokens across all Blade views. The AI assistant must strictly use:
1. <x-primary-button> (Orange) for main calls to action (Submit, Save, Create, Login, Filter).
2. <x-secondary-button> (Gray Border) for Cancel, Dismiss, Close, and neutral actions.
3. <x-danger-button> (Rose Red) for Delete, Terminate, and destructive operations.
4. <x-success-button> (Emerald Green) for Approve, Resolve, Mark as Read.
5. <x-warning-button> (Amber) for Retry, Pause, Cautionary actions.
6. <x-info-button> (Indigo) for Inspect, View Changes, Payload, and Technical details.
Never use unstyled or ad-hoc <button> elements with arbitrary colors. Zero inline style attributes.

## Mandatory Trilingual Localization Across All Views (AR, EN, FR)
Every text string across all Blade templates, components, and controllers must strictly use __('...') or @lang('...'). Zero hardcoded text. Every new translation key MUST immediately be registered and translated into all 3 languages (lang/ar.json, lang/en.json, lang/fr.json) maintaining 100% key parity with zero missing keys or English fallbacks in Arabic/French.
