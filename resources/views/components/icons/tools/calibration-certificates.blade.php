@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Calibration Certificates: Official ISO Calibration Certificate Diploma with Verification Seal & Ribbon --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="cc-cert-bg" x1="12" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#F8FAFC" />
        </linearGradient>
        <linearGradient id="cc-seal-bg" x1="36" y1="36" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <filter id="cc-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#065F46" flood-opacity="0.2" />
        </filter>
    </defs>

    <g filter="url(#cc-shadow)">
        {{-- Certificate Diploma Parchment --}}
        <rect x="12" y="8" width="40" height="50" rx="6" fill="url(#cc-cert-bg)" stroke="#E2E8F0" stroke-width="1.2" />

        {{-- Formal Certificate Guilloche Inner Border --}}
        <rect x="15" y="11" width="34" height="44" rx="3" stroke="#CBD5E1" stroke-width="0.8" stroke-dasharray="3 1.5" fill="none" />

        {{-- Certificate Header Accreditation Seal --}}
        <circle cx="32" cy="18" r="4" fill="#059669" />
        <path d="M30 18 L31.5 19.5 L34 16.5" stroke="#FFFFFF" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />

        {{-- Certificate Title & Specification Lines --}}
        <rect x="22" y="25" width="20" height="2.5" rx="1" fill="#047857" />
        <rect x="19" y="30" width="26" height="1.8" rx="0.9" fill="#94A3B8" />
        <rect x="19" y="34" width="22" height="1.8" rx="0.9" fill="#CBD5E1" />
        <rect x="19" y="38" width="18" height="1.8" rx="0.9" fill="#CBD5E1" />
        <rect x="19" y="42" width="14" height="1.8" rx="0.9" fill="#CBD5E1" />

        {{-- Verification Signature Script Line --}}
        <path d="M19 47 Q22 45 25 47 T30 46" stroke="#475569" stroke-width="1" stroke-linecap="round" fill="none" />

        {{-- Official Emerald Accreditation Verification Seal / Medallion --}}
        <circle cx="45" cy="45" r="9.5" fill="url(#cc-seal-bg)" stroke="#FFFFFF" stroke-width="1.8" />
        {{-- Inner Green Certified Checkmark --}}
        <path d="M41 45 L44 48 L49 42" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        {{-- Hanging Ribbon Tails --}}
        <path d="M42 53 L39 60 L44 57 L49 60 L46 53" fill="#F59E0B" />
    </g>
</svg>
