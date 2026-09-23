@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Enterprise Clients: Corporate Partner Profile with Skyscraper Headquarters & Partnership Handshake Shield --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="cl-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="cl-bldg-front" x1="20" y1="22" x2="34" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <linearGradient id="cl-bldg-back" x1="32" y1="16" x2="44" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#047857" />
            <stop offset="1" stop-color="#064E3B" />
        </linearGradient>
        <filter id="cl-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.28" />
        </filter>
    </defs>

    <g filter="url(#cl-shadow)">
        {{-- Corporate Account Frame --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#cl-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Corporate Headquarters Architecture Silhouette --}}
        {{-- High-Rise Tower (Back) --}}
        <rect x="31" y="16" width="13" height="32" rx="2" fill="url(#cl-bldg-back)" />
        {{-- Back Tower Windows --}}
        <rect x="34" y="20" width="2" height="2" fill="#D1FAE5" />
        <rect x="39" y="20" width="2" height="2" fill="#D1FAE5" />
        <rect x="34" y="25" width="2" height="2" fill="#D1FAE5" />
        <rect x="39" y="25" width="2" height="2" fill="#D1FAE5" />
        <rect x="34" y="30" width="2" height="2" fill="#D1FAE5" />
        <rect x="39" y="30" width="2" height="2" fill="#D1FAE5" />

        {{-- Main Headquarters Building (Front) --}}
        <rect x="18" y="22" width="16" height="26" rx="2" fill="url(#cl-bldg-front)" stroke="#D1FAE5" stroke-width="0.8" />
        {{-- Front Windows Grid --}}
        <rect x="21" y="26" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="26" y="26" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="29.5" y="26" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="21" y="31" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="26" y="31" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="29.5" y="31" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="21" y="36" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="26" y="36" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        <rect x="29.5" y="36" width="2.5" height="2.5" rx="0.5" fill="#FFFFFF" />
        {{-- Building Entrance --}}
        <path d="M24 48 V43 H28 V48 Z" fill="#D1FAE5" />

        {{-- Verified Client Partnership Handshake Badge --}}
        <circle cx="45" cy="42" r="9.5" fill="#059669" stroke="#FFFFFF" stroke-width="1.8" />
        {{-- Handshake stylized icon --}}
        <path d="M40 42 L43 45 L47 41 L50 43" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <circle cx="45" cy="42" r="7" stroke="#A7F3D0" stroke-width="0.8" stroke-dasharray="2 2" />
    </g>
</svg>
