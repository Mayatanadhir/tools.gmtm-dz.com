@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Master Module: Operations & Projects --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="mod-ops-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="mod-ops-gold" x1="16" y1="14" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#10B981" />
        </linearGradient>
        <filter id="mod-ops-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#mod-ops-shadow)">
        {{-- Rounded Outer Container --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#mod-ops-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- Operations Clipboard Document Emblem --}}
        <rect x="18" y="16" width="28" height="34" rx="4" fill="#FFFFFF" />
        {{-- Clipboard Clip --}}
        <rect x="25" y="13" width="14" height="6" rx="2" fill="url(#mod-ops-gold)" />

        {{-- Checkmark Items --}}
        <rect x="22" y="24" width="4" height="4" rx="1" fill="#10B981" />
        <path d="M23 26 L24 27 L25.5 25" stroke="#FFFFFF" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" />
        <rect x="28" y="25" width="14" height="2" rx="1" fill="#94A3B8" />

        <rect x="22" y="31" width="4" height="4" rx="1" fill="#10B981" />
        <path d="M23 33 L24 34 L25.5 32" stroke="#FFFFFF" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" />
        <rect x="28" y="32" width="11" height="2" rx="1" fill="#94A3B8" />

        <rect x="22" y="38" width="4" height="4" rx="1" fill="#059669" />
        <rect x="28" y="39" width="8" height="2" rx="1" fill="#CBD5E1" />

        {{-- Engineering Compass / Mission Star Badge --}}
        <circle cx="44" cy="44" r="9" fill="#059669" stroke="#FFFFFF" stroke-width="1.8" />
        {{-- 4-point star --}}
        <path d="M44 38 L45.5 42.5 L50 44 L45.5 45.5 L44 50 L42.5 45.5 L38 44 L42.5 42.5 Z" fill="#FFFFFF" />
    </g>
</svg>
