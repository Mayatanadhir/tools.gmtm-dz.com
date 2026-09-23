@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Physical Sites: Operational Industrial Facility with 3D Geolocation Pin & Architectural Plant --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="si-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#064E3B" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="si-pin-grad" x1="36" y1="10" x2="52" y2="34" gradientUnits="userSpaceOnUse">
            <stop stop-color="#F59E0B" />
            <stop offset="1" stop-color="#D97706" />
        </linearGradient>
        <linearGradient id="si-plant-facade" x1="14" y1="28" x2="38" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <filter id="si-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#si-shadow)">
        {{-- Card Enclosure Frame --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#si-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Industrial Warehouse / Facility Building Profile --}}
        {{-- Sawtooth Roof / Factory Profile --}}
        <path d="M14 48 V34 L20 30 V34 L26 30 V34 L32 30 V48 H14 Z" fill="url(#si-plant-facade)" stroke="#A7F3D0" stroke-width="0.8" />
        
        {{-- Facility Entrance & High Bay Doors --}}
        <rect x="17" y="40" width="6" height="8" rx="1" fill="#022C22" />
        <rect x="25" y="41" width="5" height="4" rx="0.5" fill="#34D399" />

        {{-- Facility Storage Silo / Chimney Stack --}}
        <rect x="34" y="32" width="6" height="16" rx="1.5" fill="#047857" stroke="#34D399" stroke-width="0.8" />
        <line x1="34" y1="36" x2="40" y2="36" stroke="#A7F3D0" stroke-width="0.8" />
        <line x1="34" y1="42" x2="40" y2="42" stroke="#A7F3D0" stroke-width="0.8" />

        {{-- Ground Foundation / Pavement Line --}}
        <line x1="12" y1="48" x2="52" y2="48" stroke="#059669" stroke-width="1.5" stroke-linecap="round" />

        {{-- 3D Geolocation Map Marker Pin (Highlight) --}}
        <g filter="drop-shadow(0 2px 3px rgba(0,0,0,0.3))">
            <path d="M44 12 C39.5 12 36 15.5 36 20 C36 26 44 34 44 34 C44 34 52 26 52 20 C52 15.5 48.5 12 44 12 Z" fill="url(#si-pin-grad)" stroke="#FFFFFF" stroke-width="1.5" />
            <circle cx="44" cy="20" r="3.5" fill="#FFFFFF" />
            <circle cx="44" cy="20" r="1.8" fill="#D97706" />
        </g>
    </g>
</svg>
