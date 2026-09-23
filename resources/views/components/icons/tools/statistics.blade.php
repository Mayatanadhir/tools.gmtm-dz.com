@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Company Statistics: Comparative KPI Analytics Board with Multi-Metric Bars & Growth Indicator --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="st-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#064E3B" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="st-bar-1" x1="16" y1="36" x2="22" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <linearGradient id="st-bar-2" x1="25" y1="26" x2="31" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <linearGradient id="st-bar-3" x1="34" y1="20" x2="40" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#6EE7B7" />
            <stop offset="1" stop-color="#10B981" />
        </linearGradient>
        <linearGradient id="st-bar-4" x1="43" y1="30" x2="49" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#A7F3D0" />
            <stop offset="1" stop-color="#34D399" />
        </linearGradient>
        <filter id="st-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#st-shadow)">
        {{-- Card Container Frame --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#st-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Top KPI Header Bar --}}
        <rect x="14" y="14" width="36" height="5" rx="1.5" fill="#065F46" />
        <circle cx="17.5" cy="16.5" r="1.2" fill="#34D399" />
        <rect x="21" y="15.5" width="12" height="2" rx="1" fill="#A7F3D0" />

        {{-- Horizontal Chart Guidelines --}}
        <line x1="14" y1="25" x2="50" y2="25" stroke="#065F46" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="14" y1="33" x2="50" y2="33" stroke="#065F46" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="14" y1="41" x2="50" y2="41" stroke="#065F46" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="14" y1="48" x2="50" y2="48" stroke="#059669" stroke-width="1" />

        {{-- Comparative 3D Analytics Bars --}}
        {{-- Bar 1 --}}
        <rect x="16" y="34" width="6" height="14" rx="2" fill="url(#st-bar-1)" />
        {{-- Bar 2 --}}
        <rect x="25" y="24" width="6" height="24" rx="2" fill="url(#st-bar-2)" />
        {{-- Bar 3 (Peak) --}}
        <rect x="34" y="18" width="6" height="30" rx="2" fill="url(#st-bar-3)" />
        {{-- Bar 4 --}}
        <rect x="43" y="28" width="6" height="20" rx="2" fill="url(#st-bar-4)" />

        {{-- Dynamic Ascending Trendline Overlay --}}
        <path d="M19 32 L28 22 L37 16 L46 26" stroke="#FDE68A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
        <circle cx="37" cy="16" r="2.2" fill="#F59E0B" stroke="#FFFFFF" stroke-width="0.8" />

        {{-- Floating Growth Badge (+24%) --}}
        <rect x="37" y="38" width="14" height="6.5" rx="3.25" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
        <path d="M40 42 L42 40 L44 42" stroke="#FFFFFF" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
        <text x="47" y="43" font-size="4.5" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" text-anchor="middle">%</text>
    </g>
</svg>
