@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Master Module: Metrology & Equipments --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="mod-met-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="mod-met-ring" x1="16" y1="16" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <filter id="mod-met-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#mod-met-shadow)">
        {{-- Rounded Outer Hexagon / Shield Base --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#mod-met-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- Precision Dial Ring --}}
        <circle cx="32" cy="32" r="17" stroke="url(#mod-met-ring)" stroke-width="2.5" stroke-dasharray="4 2" />
        <circle cx="32" cy="32" r="12" fill="#022C22" stroke="#10B981" stroke-width="1" />

        {{-- Calibration Compass / Dial Needle --}}
        <path d="M32 23 L35 32 L32 35 L29 32 Z" fill="#34D399" />
        <path d="M32 41 L29 32 L32 35 L35 32 Z" fill="#F43F5E" />
        <circle cx="32" cy="32" r="2" fill="#FFFFFF" />

        {{-- Micro Caliper Measurement Jaws at Corners --}}
        <path d="M16 16 H22 V20" stroke="#A7F3D0" stroke-width="1.8" stroke-linecap="round" />
        <path d="M48 16 H42 V20" stroke="#A7F3D0" stroke-width="1.8" stroke-linecap="round" />
        <path d="M16 48 H22 V44" stroke="#A7F3D0" stroke-width="1.8" stroke-linecap="round" />
        <path d="M48 48 H42 V44" stroke="#A7F3D0" stroke-width="1.8" stroke-linecap="round" />
    </g>
</svg>
