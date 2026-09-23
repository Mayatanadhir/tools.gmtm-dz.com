@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Quantities & Units: Precision Measurement Balance Scale with Calibration Mass & 3D Metric Cube --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="un-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="un-beam-gold" x1="16" y1="18" x2="48" y2="24" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#10B981" />
        </linearGradient>
        <linearGradient id="un-cube-top" x1="38" y1="36" x2="52" y2="44" gradientUnits="userSpaceOnUse">
            <stop stop-color="#6EE7B7" />
            <stop offset="1" stop-color="#34D399" />
        </linearGradient>
        <filter id="un-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#un-shadow)">
        {{-- Enclosing Frame Card --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#un-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Scale Central Fulcrum Pillar --}}
        <path d="M30 46 H34 V22 H30 Z" fill="#D1FAE5" />
        <path d="M26 46 H38 V49 H26 Z" rx="1" fill="#A7F3D0" />
        <circle cx="32" cy="20" r="3" fill="url(#un-beam-gold)" />

        {{-- Horizontal Balance Beam --}}
        <path d="M16 21 L32 19 L48 21" stroke="url(#un-beam-gold)" stroke-width="2" stroke-linecap="round" fill="none" />

        {{-- Left Suspension Strings & Pan --}}
        <line x1="18" y1="21" x2="14" y2="34" stroke="#A7F3D0" stroke-width="0.8" />
        <line x1="18" y1="21" x2="22" y2="34" stroke="#A7F3D0" stroke-width="0.8" />
        <path d="M13 34 C13 37 23 37 23 34 Z" fill="#D1FAE5" stroke="#6EE7B7" stroke-width="0.8" />
        {{-- Standard Calibration Weight on Left Pan (Preserved Gold) --}}
        <path d="M16 33 H20 L19 28 H17 Z" fill="#F59E0B" />
        <circle cx="18" cy="27" r="1" fill="#FDE68A" />

        {{-- Right Suspension Strings & Pan --}}
        <line x1="46" y1="21" x2="42" y2="34" stroke="#A7F3D0" stroke-width="0.8" />
        <line x1="46" y1="21" x2="50" y2="34" stroke="#A7F3D0" stroke-width="0.8" />
        <path d="M41 34 C41 37 51 37 51 34 Z" fill="#D1FAE5" stroke="#6EE7B7" stroke-width="0.8" />
        {{-- 3D Isometric Metric Measurement Cube on Right Pan --}}
        {{-- Top Face --}}
        <path d="M46 26 L50 28 L46 30 L42 28 Z" fill="url(#un-cube-top)" />
        {{-- Left Face --}}
        <path d="M42 28 L46 30 V34 L42 32 Z" fill="#047857" />
        {{-- Right Face --}}
        <path d="M46 30 L50 28 V32 L46 34 Z" fill="#059669" />

        {{-- Measurement Units Label (SI / kg) --}}
        <text x="32" y="36" font-size="6" font-family="sans-serif" font-weight="bold" fill="#D1FAE5" text-anchor="middle">SI</text>
    </g>
</svg>
