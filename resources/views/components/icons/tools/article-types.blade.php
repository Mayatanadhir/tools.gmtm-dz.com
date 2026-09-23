@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Classification of Articles: Categorized Product Taxonomy Cards with Barcode & Classification Tags --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="art-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="art-tag-amber" x1="16" y1="20" x2="32" y2="28" gradientUnits="userSpaceOnUse">
            <stop stop-color="#F59E0B" />
            <stop offset="1" stop-color="#D97706" />
        </linearGradient>
        <linearGradient id="art-tag-sky" x1="16" y1="28" x2="36" y2="36" gradientUnits="userSpaceOnUse">
            <stop stop-color="#38BDF8" />
            <stop offset="1" stop-color="#0284C7" />
        </linearGradient>
        <filter id="art-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#art-shadow)">
        {{-- Base Taxonomy Card / Container --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#art-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Top Classification Header Ribbon --}}
        <path d="M10 18 C10 13.6 13.6 10 18 10 H46 C50.4 10 54 13.6 54 18 V20 H10 Z" fill="#047857" />
        <circle cx="15" cy="15" r="1.5" fill="#A7F3D0" />
        <circle cx="20" cy="15" r="1.5" fill="#34D399" />
        <circle cx="25" cy="15" r="1.5" fill="#10B981" />

        {{-- Layered Classification Category Pills / Tags --}}
        {{-- Tag 1 (Product Line) --}}
        <rect x="16" y="24" width="22" height="6.5" rx="3.25" fill="url(#art-tag-amber)" />
        <circle cx="20" cy="27.25" r="1.2" fill="#FFFFFF" />
        <rect x="23" y="26" width="12" height="2.5" rx="1" fill="#FEF3C7" />

        {{-- Tag 2 (Component Spec) --}}
        <rect x="16" y="33" width="26" height="6.5" rx="3.25" fill="url(#art-tag-sky)" />
        <circle cx="20" cy="36.25" r="1.2" fill="#FFFFFF" />
        <rect x="23" y="35" width="16" height="2.5" rx="1" fill="#E0F2FE" />

        {{-- Barcode / Article Reference Lines at Bottom --}}
        <rect x="16" y="44" width="2" height="8" fill="#A7F3D0" />
        <rect x="20" y="44" width="3" height="8" fill="#A7F3D0" />
        <rect x="25" y="44" width="1.5" height="8" fill="#A7F3D0" />
        <rect x="28" y="44" width="3.5" height="8" fill="#A7F3D0" />
        <rect x="33" y="44" width="2" height="8" fill="#A7F3D0" />
        <rect x="37" y="44" width="1" height="8" fill="#A7F3D0" />
        <rect x="40" y="44" width="3" height="8" fill="#A7F3D0" />

        {{-- Category Index Floating Badge --}}
        <circle cx="47" cy="32" r="8" fill="#059669" stroke="#FFFFFF" stroke-width="1.8" />
        <path d="M44 32 H50 M47 29 V35" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" />
    </g>
</svg>
