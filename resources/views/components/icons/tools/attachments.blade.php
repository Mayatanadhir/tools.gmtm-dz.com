@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Attachments List: Multi-Layered Project Dossier with Metallic Paperclip & Document Preview --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="at-back-doc" x1="18" y1="6" x2="52" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#E2E8F0" />
            <stop offset="1" stop-color="#CBD5E1" />
        </linearGradient>
        <linearGradient id="at-front-doc" x1="12" y1="12" x2="48" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#F8FAFC" />
        </linearGradient>
        <linearGradient id="at-clip-grad" x1="16" y1="8" x2="28" y2="34" gradientUnits="userSpaceOnUse">
            <stop stop-color="#34D399" />
            <stop offset="1" stop-color="#059669" />
        </linearGradient>
        <linearGradient id="at-badge-bg" x1="34" y1="36" x2="52" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#059669" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <filter id="at-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#064E3B" flood-opacity="0.22" />
        </filter>
    </defs>

    <g filter="url(#at-shadow)">
        {{-- Back Document (Angled) --}}
        <rect x="20" y="8" width="32" height="42" rx="5" fill="url(#at-back-doc)" stroke="#94A3B8" stroke-width="1.2" transform="rotate(6 36 29)" />

        {{-- Front Document --}}
        <rect x="14" y="12" width="34" height="46" rx="5" fill="url(#at-front-doc)" stroke="#CBD5E1" stroke-width="1.2" />

        {{-- Document Content Lines --}}
        <rect x="20" y="24" width="22" height="2.5" rx="1" fill="#047857" />
        <rect x="20" y="30" width="18" height="2.5" rx="1" fill="#CBD5E1" />
        <rect x="20" y="36" width="14" height="2.5" rx="1" fill="#CBD5E1" />
        
        {{-- Thumbnail / Technical Chart Preview Mini-Box --}}
        <rect x="20" y="42" width="12" height="10" rx="2" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="1" />
        <path d="M22 49 L25 45 L28 48 L30 46" stroke="#059669" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />

        {{-- 3D Metallic Paperclip Wrapping the Top Corner --}}
        <path d="M22 6 C18 6 15 9 15 13 V30 C15 33.5 17.5 36 21 36 C24.5 36 27 33.5 27 30 V15 C27 13 25.5 11.5 23.5 11.5 C21.5 11.5 20 13 20 15 V28" 
              stroke="url(#at-clip-grad)" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" fill="none" />

        {{-- Project File Tag Badge --}}
        <circle cx="47" cy="46" r="9" fill="url(#at-badge-bg)" stroke="#FFFFFF" stroke-width="1.8" />
        <text x="47" y="49" font-size="8" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" text-anchor="middle">PDF</text>
    </g>
</svg>
