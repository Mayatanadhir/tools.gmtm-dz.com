@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Annual Forecasts: Forward Projection Chart with Upward Predictive Trendline & Calendar Target --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="fc-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="fc-trend-grad" x1="16" y1="46" x2="48" y2="18" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#10B981" />
        </linearGradient>
        <linearGradient id="fc-area-fill" x1="16" y1="26" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" stop-opacity="0.35" />
            <stop offset="1" stop-color="#022C22" stop-opacity="0.05" />
        </linearGradient>
        <filter id="fc-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#fc-shadow)">
        {{-- Card Container Frame --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#fc-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Top Calendar Header --}}
        <rect x="14" y="14" width="36" height="8" rx="2" fill="#047857" />
        {{-- Calendar Rings --}}
        <rect x="20" y="12" width="2" height="4" rx="1" fill="#A7F3D0" />
        <rect x="31" y="12" width="2" height="4" rx="1" fill="#A7F3D0" />
        <rect x="42" y="12" width="2" height="4" rx="1" fill="#A7F3D0" />

        {{-- Chart Grid Lines --}}
        <line x1="15" y1="28" x2="49" y2="28" stroke="#047857" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="15" y1="36" x2="49" y2="36" stroke="#047857" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="15" y1="44" x2="49" y2="44" stroke="#047857" stroke-width="0.8" stroke-dasharray="2 2" />
        <line x1="15" y1="48" x2="49" y2="48" stroke="#059669" stroke-width="1" />

        {{-- Projection Area Under Curve --}}
        <path d="M16 46 Q24 44 28 36 T40 28 T48 20 V48 H16 Z" fill="url(#fc-area-fill)" />

        {{-- Upward Projection Curve --}}
        <path d="M16 46 Q24 44 28 36 T40 28 T48 20" stroke="url(#fc-trend-grad)" stroke-width="2.5" stroke-linecap="round" fill="none" />

        {{-- Prediction Future Dotted Extension & Milestone Arrow --}}
        <path d="M48 20 L52 16 M52 16 V21 M52 16 H47" stroke="#34D399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

        {{-- Milestone Data Nodes --}}
        <circle cx="16" cy="46" r="2" fill="#34D399" />
        <circle cx="28" cy="36" r="2.5" fill="#10B981" />
        <circle cx="40" cy="28" r="2.5" fill="#A7F3D0" />
        <circle cx="48" cy="20" r="3" fill="#F59E0B" stroke="#FFFFFF" stroke-width="1" />
    </g>
</svg>
