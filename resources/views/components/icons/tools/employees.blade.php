@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Personnel & Employees: Enterprise Security ID Badge with Staff Avatar & Biometric Chip --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="em-badge-bg" x1="12" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#ECFDF5" />
        </linearGradient>
        <linearGradient id="em-header-grad" x1="14" y1="8" x2="50" y2="16" gradientUnits="userSpaceOnUse">
            <stop stop-color="#059669" />
            <stop offset="1" stop-color="#064E3B" />
        </linearGradient>
        <linearGradient id="em-avatar-bg" x1="24" y1="20" x2="40" y2="36" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <linearGradient id="em-chip-gold" x1="38" y1="36" x2="48" y2="46" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FDE68A" />
            <stop offset="1" stop-color="#F59E0B" />
        </linearGradient>
        <filter id="em-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.22" />
        </filter>
    </defs>

    <g filter="url(#em-shadow)">
        {{-- Badge Lanyard Slot Hole --}}
        <rect x="26" y="7" width="12" height="3" rx="1.5" fill="#94A3B8" />

        {{-- ID Badge Card Body --}}
        <rect x="12" y="12" width="40" height="46" rx="6" fill="url(#em-badge-bg)" stroke="#A7F3D0" stroke-width="1.2" />

        {{-- Top Corporate Department Header Bar --}}
        <path d="M12 18 C12 14.7 14.7 12 18 12 H46 C49.3 12 52 14.7 52 18 V20 H12 Z" fill="url(#em-header-grad)" />

        {{-- Staff Profile Picture Avatar Box --}}
        <rect x="16" y="24" width="18" height="18" rx="4" fill="url(#em-avatar-bg)" stroke="#6EE7B7" stroke-width="0.8" />
        {{-- Avatar Silhouette: Head & Shoulders --}}
        <circle cx="25" cy="29" r="4" fill="#FFFFFF" />
        <path d="M18 40 C18 36.5 21 34.5 25 34.5 C29 34.5 32 36.5 32 40 Z" fill="#FFFFFF" />

        {{-- Smart Biometric Security Chip --}}
        <rect x="38" y="25" width="10" height="8" rx="1.5" fill="url(#em-chip-gold)" stroke="#D97706" stroke-width="0.8" />
        <line x1="43" y1="25" x2="43" y2="33" stroke="#B45309" stroke-width="0.6" />
        <line x1="38" y1="29" x2="48" y2="29" stroke="#B45309" stroke-width="0.6" />

        {{-- Employee Identification Credentials Lines --}}
        <rect x="16" y="45" width="22" height="2.5" rx="1" fill="#059669" />
        <rect x="16" y="50" width="16" height="2" rx="1" fill="#94A3B8" />

        {{-- Verification Department Star / Crest --}}
        <circle cx="43" cy="46" r="6" fill="#047857" stroke="#FFFFFF" stroke-width="1.2" />
        <path d="M41 46 L42.5 47.5 L45.5 44.5" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
    </g>
</svg>
