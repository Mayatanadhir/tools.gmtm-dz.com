@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Master Module: Internal & Analytical Management --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="mod-ana-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#064E3B" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="mod-ana-trend" x1="16" y1="42" x2="48" y2="18" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#38BDF8" />
        </linearGradient>
        <filter id="mod-ana-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#mod-ana-shadow)">
        {{-- Rounded Outer Container --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#mod-ana-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- Analytical Grid Reference Lines --}}
        <line x1="16" y1="26" x2="48" y2="26" stroke="#065F46" stroke-width="1" stroke-dasharray="3 2" />
        <line x1="16" y1="36" x2="48" y2="36" stroke="#065F46" stroke-width="1" stroke-dasharray="3 2" />
        <line x1="16" y1="46" x2="48" y2="46" stroke="#047857" stroke-width="1.2" />

        {{-- Multi-Color Ascending Analytics Columns --}}
        <rect x="18" y="34" width="5" height="12" rx="1.5" fill="#34D399" />
        <rect x="25" y="27" width="5" height="19" rx="1.5" fill="#10B981" />
        <rect x="32" y="22" width="5" height="24" rx="1.5" fill="#38BDF8" />
        <rect x="39" y="16" width="5" height="30" rx="1.5" fill="#818CF8" />

        {{-- Dynamic Predictive Trajectory Curve --}}
        <path d="M18 36 Q28 30 34 22 T46 14" stroke="url(#mod-ana-trend)" stroke-width="2.5" stroke-linecap="round" fill="none" />
        {{-- High-Growth Target Arrowhead --}}
        <path d="M42 14 H46 V18" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <circle cx="34" cy="22" r="2.5" fill="#FDE68A" stroke="#F59E0B" stroke-width="1" />
    </g>
</svg>
