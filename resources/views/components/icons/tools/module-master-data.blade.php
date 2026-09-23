@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Master Module: Master Data / Reference Data --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="mod-md-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="mod-md-core" x1="24" y1="24" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <filter id="mod-md-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#mod-md-shadow)">
        {{-- Rounded Outer Container --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#mod-md-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- Network Constellation Connecting Nodes --}}
        <line x1="32" y1="20" x2="20" y2="36" stroke="#A7F3D0" stroke-width="1.5" stroke-dasharray="2 2" />
        <line x1="32" y1="20" x2="44" y2="36" stroke="#A7F3D0" stroke-width="1.5" stroke-dasharray="2 2" />
        <line x1="20" y1="36" x2="44" y2="36" stroke="#A7F3D0" stroke-width="1.5" stroke-dasharray="2 2" />

        {{-- Node 1: Corporate Partner (Top) --}}
        <circle cx="32" cy="20" r="7" fill="#047857" stroke="#FFFFFF" stroke-width="1.8" />
        <path d="M29 22 V18 H35 V22" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" fill="none" />

        {{-- Node 2: Personnel Staff (Bottom Left) --}}
        <circle cx="20" cy="38" r="7" fill="#059669" stroke="#FFFFFF" stroke-width="1.8" />
        <circle cx="20" cy="36" r="2.2" fill="#FFFFFF" />
        <path d="M16 42 C16 40 18 39 20 39 C22 39 24 40 24 42" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" fill="none" />

        {{-- Node 3: Physical Site Facility (Bottom Right) --}}
        <circle cx="44" cy="38" r="7" fill="#10B981" stroke="#FFFFFF" stroke-width="1.8" />
        <path d="M44 33 L48 37 H40 Z" fill="#FFFFFF" />
        <rect x="42" y="37" width="4" height="4" fill="#FFFFFF" />

        {{-- Central Database Core Hub --}}
        <circle cx="32" cy="34" r="4.5" fill="url(#mod-md-core)" stroke="#D1FAE5" stroke-width="1.2" />
        <circle cx="32" cy="34" r="2" fill="#FFFFFF" />
    </g>
</svg>
