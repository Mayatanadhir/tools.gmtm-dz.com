@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Mission Management: Field Operations Clipboard with GPS Beacon & Task Checklist --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="ms-board-bg" x1="12" y1="8" x2="52" y2="58" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="ms-paper-bg" x1="16" y1="16" x2="48" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#F8FAFC" />
        </linearGradient>
        <linearGradient id="ms-clip-bg" x1="24" y1="6" x2="40" y2="14" gradientUnits="userSpaceOnUse">
            <stop stop-color="#059669" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <linearGradient id="ms-badge-bg" x1="38" y1="38" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <filter id="ms-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#ms-shadow)">
        {{-- Clipboard Backboard --}}
        <rect x="12" y="10" width="40" height="48" rx="7" fill="url(#ms-board-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Paper Sheet --}}
        <rect x="16" y="16" width="32" height="38" rx="4" fill="url(#ms-paper-bg)" />

        {{-- Top Clip Mechanism --}}
        <path d="M25 10 C25 7.8 26.8 6 29 6 H35 C37.2 6 39 7.8 39 10 V13 H25 V10 Z" fill="url(#ms-clip-bg)" />
        <rect x="23" y="11" width="18" height="4" rx="2" fill="#34D399" />

        {{-- Checklist Rows --}}
        {{-- Row 1 (Checked) --}}
        <rect x="20" y="22" width="6" height="6" rx="1.5" fill="#10B981" />
        <path d="M21.5 25 L23 26.5 L25 23.5" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
        <rect x="28" y="24" width="16" height="2.5" rx="1" fill="#94A3B8" />

        {{-- Row 2 (Checked) --}}
        <rect x="20" y="31" width="6" height="6" rx="1.5" fill="#10B981" />
        <path d="M21.5 34 L23 35.5 L25 32.5" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
        <rect x="28" y="33" width="13" height="2.5" rx="1" fill="#94A3B8" />

        {{-- Row 3 (In Progress) --}}
        <rect x="20" y="40" width="6" height="6" rx="1.5" fill="#D1FAE5" stroke="#059669" stroke-width="1" />
        <rect x="28" y="42" width="10" height="2.5" rx="1" fill="#CBD5E1" />

        {{-- Field Operations GPS / Radar Badge --}}
        <circle cx="47" cy="47" r="10" fill="url(#ms-badge-bg)" stroke="#FFFFFF" stroke-width="1.8" />
        <circle cx="47" cy="47" r="6.5" stroke="#A7F3D0" stroke-width="0.9" stroke-dasharray="2 2" />
        <path d="M47 41 V45 M47 49 V53 M41 47 H45 M49 47 H53" stroke="#FFFFFF" stroke-width="1" stroke-linecap="round" />
        <circle cx="47" cy="47" r="2.2" fill="#FFFFFF" />
    </g>
</svg>
