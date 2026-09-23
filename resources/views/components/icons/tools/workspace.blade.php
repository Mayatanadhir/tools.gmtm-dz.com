@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Workspace Page Header Icon: Office Desk + Monitor + Chair + Cup — 64x64 Professional --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- GMTM Brand Background Gradient --}}
        <linearGradient id="ws2-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>

        {{-- Monitor Screen Gradient --}}
        <linearGradient id="ws2-screen" x1="22" y1="14" x2="44" y2="30" gradientUnits="userSpaceOnUse">
            <stop stop-color="#047857" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>

        {{-- Desk Surface Gradient --}}
        <linearGradient id="ws2-desk" x1="10" y1="34" x2="54" y2="38" gradientUnits="userSpaceOnUse">
            <stop stop-color="#059669" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>

        {{-- Chair Gradient --}}
        <linearGradient id="ws2-chair" x1="26" y1="38" x2="38" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>

        {{-- Drop Shadow --}}
        <filter id="ws2-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#022C22" flood-opacity="0.35" />
        </filter>
    </defs>

    <g filter="url(#ws2-shadow)">
        {{-- Rounded Outer Container Base --}}
        <rect x="8" y="8" width="48" height="48" rx="14" fill="url(#ws2-bg)" stroke="#059669" stroke-width="1.5" />

        {{-- === Monitor === --}}
        {{-- Screen Body --}}
        <rect x="21" y="13" width="22" height="16" rx="2" fill="url(#ws2-screen)" stroke="#10B981" stroke-width="1.2" />
        {{-- Screen Glare / UI Lines --}}
        <line x1="24" y1="17" x2="36" y2="17" stroke="#34D399" stroke-width="0.9" stroke-linecap="round" />
        <line x1="24" y1="20" x2="33" y2="20" stroke="#065F46" stroke-width="0.9" stroke-linecap="round" />
        <line x1="24" y1="23" x2="38" y2="23" stroke="#065F46" stroke-width="0.9" stroke-linecap="round" />
        {{-- Screen Status Dot --}}
        <circle cx="39" cy="17" r="1.2" fill="#34D399" />
        {{-- Monitor Neck --}}
        <rect x="30" y="29" width="4" height="4" rx="0.5" fill="#047857" stroke="#10B981" stroke-width="0.8" />
        {{-- Monitor Stand Base --}}
        <rect x="26" y="33" width="12" height="2" rx="1" fill="#059669" stroke="#10B981" stroke-width="0.8" />

        {{-- === Desk Surface === --}}
        <rect x="11" y="35" width="42" height="3" rx="1.5" fill="url(#ws2-desk)" stroke="#10B981" stroke-width="1" />

        {{-- Desk Left Leg --}}
        <path d="M14 38 L12 53" stroke="#047857" stroke-width="2" stroke-linecap="round" />
        {{-- Desk Right Leg --}}
        <path d="M50 38 L52 53" stroke="#047857" stroke-width="2" stroke-linecap="round" />

        {{-- === Documents Stack (Left on Desk) === --}}
        <rect x="13" y="30" width="7" height="5" rx="1" fill="#022C22" stroke="#34D399" stroke-width="0.9" />
        <line x1="14.5" y1="32" x2="19" y2="32" stroke="#A7F3D0" stroke-width="0.7" stroke-linecap="round" />
        <line x1="14.5" y1="33.5" x2="18" y2="33.5" stroke="#A7F3D0" stroke-width="0.7" stroke-linecap="round" />

        {{-- === Coffee Cup (Right on Desk) === --}}
        {{-- Cup body --}}
        <path d="M46 28 L47.5 35 H42.5 L44 28 Z" fill="#047857" stroke="#10B981" stroke-width="0.9" stroke-linejoin="round" />
        {{-- Cup handle --}}
        <path d="M47.5 30 Q50 30 50 32 Q50 34 47.5 34" stroke="#10B981" stroke-width="1" fill="none" stroke-linecap="round" />
        {{-- Saucer --}}
        <ellipse cx="45" cy="35" rx="3.5" ry="0.8" fill="#059669" stroke="#A7F3D0" stroke-width="0.7" />
        {{-- Steam --}}
        <path d="M44 27 Q43.5 25.5 44 24" stroke="#A7F3D0" stroke-width="0.7" stroke-linecap="round" fill="none" opacity="0.6" />
        <path d="M46 27 Q46.5 25 46 23.5" stroke="#A7F3D0" stroke-width="0.7" stroke-linecap="round" fill="none" opacity="0.6" />

        {{-- === Office Chair === --}}
        {{-- Seat --}}
        <rect x="27" y="41" width="10" height="4" rx="3" fill="url(#ws2-chair)" />
        {{-- Chair back --}}
        <rect x="28" y="37" width="8" height="5" rx="2" fill="#34D399" stroke="#059669" stroke-width="0.8" />
        {{-- Chair stem --}}
        <line x1="32" y1="45" x2="32" y2="50" stroke="#047857" stroke-width="1.5" stroke-linecap="round" />
        {{-- Chair base / 3-spoke --}}
        <line x1="32" y1="50" x2="27" y2="54" stroke="#059669" stroke-width="1.5" stroke-linecap="round" />
        <line x1="32" y1="50" x2="32" y2="55" stroke="#059669" stroke-width="1.5" stroke-linecap="round" />
        <line x1="32" y1="50" x2="37" y2="54" stroke="#059669" stroke-width="1.5" stroke-linecap="round" />
        {{-- Chair wheel dots --}}
        <circle cx="27" cy="54" r="1.2" fill="#A7F3D0" />
        <circle cx="32" cy="55" r="1.2" fill="#A7F3D0" />
        <circle cx="37" cy="54" r="1.2" fill="#A7F3D0" />
    </g>
</svg>
