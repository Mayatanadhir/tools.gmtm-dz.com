@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Contracts Management: Legal Agreement Document with Golden Wax Seal & Signature Quill --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="ct-parchment-bg" x1="12" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#F1F5F9" />
        </linearGradient>
        <linearGradient id="ct-header-bg" x1="14" y1="8" x2="50" y2="16" gradientUnits="userSpaceOnUse">
            <stop stop-color="#047857" />
            <stop offset="1" stop-color="#064E3B" />
        </linearGradient>
        <linearGradient id="ct-seal-bg" x1="38" y1="38" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#F59E0B" />
            <stop offset="1" stop-color="#B45309" />
        </linearGradient>
        <filter id="ct-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#064E3B" flood-opacity="0.22" />
        </filter>
    </defs>

    <g filter="url(#ct-shadow)">
        {{-- Background Parchment Layer --}}
        <rect x="12" y="8" width="40" height="50" rx="6" fill="url(#ct-parchment-bg)" stroke="#CBD5E1" stroke-width="1.2" />

        {{-- Legal Decorative Header Band --}}
        <path d="M12 14 C12 10.7 14.7 8 18 8 H46 C49.3 8 52 10.7 52 14 V18 H12 Z" fill="url(#ct-header-bg)" />
        <line x1="20" y1="13" x2="44" y2="13" stroke="#A7F3D0" stroke-width="1.2" stroke-linecap="round" />

        {{-- Legal Text Clause Lines --}}
        <rect x="18" y="24" width="28" height="2.5" rx="1" fill="#047857" />
        <rect x="18" y="29.5" width="24" height="2.5" rx="1" fill="#CBD5E1" />
        <rect x="18" y="35" width="20" height="2.5" rx="1" fill="#CBD5E1" />
        <rect x="18" y="40.5" width="16" height="2.5" rx="1" fill="#CBD5E1" />

        {{-- Signature Stylized Script Line --}}
        <path d="M18 48 Q21 44 24 47 T30 46 T34 48" stroke="#059669" stroke-width="1.4" stroke-linecap="round" fill="none" />
        <line x1="18" y1="51" x2="35" y2="51" stroke="#94A3B8" stroke-width="0.9" />

        {{-- Certified Golden Wax Seal / Badge --}}
        <circle cx="45" cy="46" r="9.5" fill="url(#ct-seal-bg)" stroke="#FFFFFF" stroke-width="1.8" />
        {{-- Star / Ribbon icon in seal --}}
        <path d="M45 41.5 L46.5 44.5 L49.5 45 L47.2 47 L48 50 L45 48.5 L42 50 L42.8 47 L40.5 45 L43.5 44.5 Z" fill="#FEF3C7" />
        <path d="M42 53 L40 59 L45 56.5 L50 59 L48 53" fill="#D97706" />
    </g>
</svg>
