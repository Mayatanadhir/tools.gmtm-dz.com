@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Master Module: System Control, Database & Infrastructure --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- Unified GMTM Industrial Green Background Gradient --}}
        <linearGradient id="mod-sys-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>

        {{-- Database Cylinder Top Cap Gradient --}}
        <linearGradient id="mod-sys-db-top" x1="16" y1="18" x2="38" y2="24" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>

        {{-- Database Cylinder Body Platter Gradient --}}
        <linearGradient id="mod-sys-db-body" x1="16" y1="20" x2="38" y2="44" gradientUnits="userSpaceOnUse">
            <stop stop-color="#047857" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>

        {{-- System Master Cog / Gear Gradient --}}
        <linearGradient id="mod-sys-gear" x1="34" y1="26" x2="50" y2="42" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>

        {{-- Smooth Shadow Filter --}}
        <filter id="mod-sys-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#mod-sys-shadow)">
        {{-- Rounded Outer Container Base --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#mod-sys-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- Background Infrastructure Circuit Bus Lines --}}
        <path d="M14 18 H20 L24 22" stroke="#047857" stroke-width="1.2" stroke-linecap="round" />
        <path d="M50 18 H44 L40 22" stroke="#047857" stroke-width="1.2" stroke-linecap="round" />
        <path d="M14 46 H22 L26 42" stroke="#047857" stroke-width="1.2" stroke-linecap="round" />
        <circle cx="14" cy="18" r="1.5" fill="#A7F3D0" />
        <circle cx="50" cy="18" r="1.5" fill="#A7F3D0" />
        <circle cx="14" cy="46" r="1.5" fill="#A7F3D0" />

        {{-- Database Cylinders Stack (Left Side) --}}
        {{-- Tier 3 Bottom Disk --}}
        <path d="M16 38 C16 41 21.4 43 28 43 C34.6 43 40 41 40 38 V41 C40 44 34.6 46 28 46 C21.4 46 16 44 16 41 Z" fill="url(#mod-sys-db-body)" stroke="#10B981" stroke-width="1" />
        <circle cx="19" cy="42" r="1" fill="#34D399" />
        <circle cx="22" cy="42" r="1" fill="#A7F3D0" />

        {{-- Tier 2 Middle Disk --}}
        <path d="M16 31 C16 34 21.4 36 28 36 C34.6 36 40 34 40 31 V34 C40 37 34.6 39 28 39 C21.4 39 16 37 16 34 Z" fill="url(#mod-sys-db-body)" stroke="#10B981" stroke-width="1" />
        <circle cx="19" cy="35" r="1" fill="#34D399" />
        <circle cx="22" cy="35" r="1" fill="#A7F3D0" />

        {{-- Tier 1 Top Disk --}}
        <path d="M16 24 C16 27 21.4 29 28 29 C34.6 29 40 27 40 24 V27 C40 30 34.6 32 28 32 C21.4 32 16 30 16 27 Z" fill="url(#mod-sys-db-body)" stroke="#10B981" stroke-width="1" />
        <ellipse cx="28" cy="24" rx="12" ry="4.5" fill="url(#mod-sys-db-top)" stroke="#D1FAE5" stroke-width="1.2" />
        <circle cx="19" cy="28" r="1" fill="#FFFFFF" />
        <circle cx="22" cy="28" r="1" fill="#34D399" />

        {{-- System Infrastructure / Security Gear & Core (Right Overlay) --}}
        <g transform="translate(42, 34)">
            {{-- Gear Cogs (8 Teeth) --}}
            <rect x="-2" y="-12" width="4" height="24" rx="1" fill="url(#mod-sys-gear)" />
            <rect x="-12" y="-2" width="24" height="4" rx="1" fill="url(#mod-sys-gear)" />
            <rect x="-2" y="-12" width="4" height="24" rx="1" transform="rotate(45)" fill="url(#mod-sys-gear)" />
            <rect x="-2" y="-12" width="4" height="24" rx="1" transform="rotate(-45)" fill="url(#mod-sys-gear)" />

            {{-- Outer Gear Ring --}}
            <circle cx="0" cy="0" r="10" fill="#047857" stroke="#A7F3D0" stroke-width="1" />
            {{-- Inner Shield / Control Hub --}}
            <circle cx="0" cy="0" r="6.5" fill="#022C22" stroke="#10B981" stroke-width="1.2" />

            {{-- Security / Admin Core Icon --}}
            <path d="M0 -3.5 L2.5 -1 L1.5 3.5 L-1.5 3.5 L-2.5 -1 Z" fill="#D1FAE5" />
            <circle cx="0" cy="0" r="1.5" fill="#022C22" />
        </g>

        {{-- Live Server Activity Pulse --}}
        <circle cx="48" cy="18" r="2" fill="#34D399" />
        <circle cx="48" cy="18" r="3.5" stroke="#A7F3D0" stroke-width="0.8" stroke-dasharray="2 1.5" />
    </g>
</svg>
