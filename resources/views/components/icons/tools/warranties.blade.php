@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Bank Guarantees: Financial Assurance Vault Shield with Security Dial & Bond Seal --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="wr-shield-bg" x1="12" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <linearGradient id="wr-inner-bg" x1="16" y1="12" x2="48" y2="50" gradientUnits="userSpaceOnUse">
            <stop stop-color="#10B981" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <linearGradient id="wr-gold-crest" x1="26" y1="22" x2="38" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FCD34D" />
            <stop offset="1" stop-color="#F59E0B" />
        </linearGradient>
        <filter id="wr-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#064E3B" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#wr-shadow)">
        {{-- Outer Security Shield --}}
        <path d="M32 8 L52 14 V28 C52 42 43.5 51.5 32 56 C20.5 51.5 12 42 12 28 V14 Z" 
              fill="url(#wr-shield-bg)" stroke="#34D399" stroke-width="1.2" />

        {{-- Inner Shield Layer --}}
        <path d="M32 12 L48 17 V28 C48 39.5 41 47.5 32 51.5 C23 47.5 16 39.5 16 28 V17 Z" 
              fill="url(#wr-inner-bg)" stroke="#A7F3D0" stroke-width="1" />

        {{-- Bank Vault Security Dial / Lock Geometry --}}
        <circle cx="32" cy="30" r="11" fill="#064E3B" stroke="url(#wr-gold-crest)" stroke-width="2" />
        <circle cx="32" cy="30" r="8" stroke="#34D399" stroke-width="1" stroke-dasharray="2 3" />
        
        {{-- Inner Golden Guarantee Currency / Pillar Crest --}}
        <path d="M28 26 H36 M30 26 V34 M34 26 V34 M27 34 H37" stroke="url(#wr-gold-crest)" stroke-width="1.8" stroke-linecap="round" />

        {{-- Certified Green Assurance Checkmark Badge --}}
        <circle cx="45" cy="45" r="8.5" fill="#F59E0B" stroke="#FFFFFF" stroke-width="1.8" />
        <path d="M41 45 L44 48 L49 42" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
    </g>
</svg>
