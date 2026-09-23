@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Reports Management: Executive Dossier Binder with Multi-Color Donut Chart & Export Seal --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="rp-binder-bg" x1="10" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="rp-page-bg" x1="16" y1="12" x2="50" y2="52" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#ECFDF5" />
        </linearGradient>
        <linearGradient id="rp-donut-emerald" x1="32" y1="20" x2="48" y2="36" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <filter id="rp-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#rp-shadow)">
        {{-- Executive Dossier Binder Backing --}}
        <rect x="10" y="8" width="44" height="50" rx="7" fill="url(#rp-binder-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Binder Left Margin Spine with Holes --}}
        <rect x="10" y="8" width="7" height="50" rx="3" fill="#047857" />
        <circle cx="13.5" cy="18" r="1.5" fill="#A7F3D0" />
        <circle cx="13.5" cy="33" r="1.5" fill="#A7F3D0" />
        <circle cx="13.5" cy="48" r="1.5" fill="#A7F3D0" />

        {{-- Front Insert Sheet --}}
        <rect x="17" y="12" width="33" height="42" rx="4" fill="url(#rp-page-bg)" />

        {{-- Mini Donut Chart Simulation (Visual Analytics) --}}
        <circle cx="33.5" cy="24" r="8" stroke="#D1FAE5" stroke-width="3" fill="none" />
        {{-- Emerald Segment --}}
        <path d="M33.5 16 A8 8 0 0 1 41.5 24" stroke="url(#rp-donut-emerald)" stroke-width="3" stroke-linecap="round" fill="none" />
        {{-- Amber Segment (Contrast) --}}
        <path d="M41.5 24 A8 8 0 0 1 33.5 32" stroke="#F59E0B" stroke-width="3" stroke-linecap="round" fill="none" />
        {{-- Rose Segment (Contrast) --}}
        <path d="M33.5 32 A8 8 0 0 1 25.5 24" stroke="#F43F5E" stroke-width="3" stroke-linecap="round" fill="none" />

        {{-- Structured Executive Report Rows --}}
        <rect x="22" y="36" width="23" height="2" rx="1" fill="#059669" />
        <rect x="22" y="40" width="18" height="2" rx="1" fill="#94A3B8" />
        <rect x="22" y="44" width="20" height="2" rx="1" fill="#CBD5E1" />
        <rect x="22" y="48" width="14" height="2" rx="1" fill="#CBD5E1" />

        {{-- Verified Document Export Seal Badge --}}
        <circle cx="45" cy="45" r="8" fill="#059669" stroke="#FFFFFF" stroke-width="1.5" />
        <path d="M42 45 L44.5 47.5 L48 43" stroke="#FFFFFF" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
    </g>
</svg>
