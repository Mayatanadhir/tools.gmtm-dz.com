@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Measuring Instruments: Industrial Digital Process Transmitter & Field Measurement Device --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- Industrial Blue Casing Gradient --}}
        <linearGradient id="ins-body" x1="12" y1="12" x2="52" y2="52" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#38BDF8" />
            <stop offset="35%" stop-color="#0284C7" />
            <stop offset="80%" stop-color="#0369A1" />
            <stop offset="100%" stop-color="#0C4A6E" />
        </linearGradient>

        {{-- Front Bezel Ring Gradient --}}
        <linearGradient id="ins-bezel" x1="20" y1="16" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#7DD3FC" />
            <stop offset="40%" stop-color="#0284C7" />
            <stop offset="100%" stop-color="#075985" />
        </linearGradient>

        {{-- Inner Dark Recess Gradient --}}
        <radialGradient id="ins-inner-recess" cx="34" cy="34" r="15" gradientUnits="userSpaceOnUse">
            <stop offset="65%" stop-color="#082F49" />
            <stop offset="100%" stop-color="#031A29" />
        </radialGradient>

        {{-- Digital LCD Screen Gradient (Illuminated Industrial Pale Green) --}}
        <linearGradient id="ins-lcd" x1="24" y1="26" x2="44" y2="38" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#D1FAE5" />
            <stop offset="50%" stop-color="#A7F3D0" />
            <stop offset="100%" stop-color="#6EE7B7" />
        </linearGradient>

        {{-- Stainless Steel Metallic Gradient (Nameplate & Lug) --}}
        <linearGradient id="ins-steel" x1="22" y1="8" x2="46" y2="14" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FFFFFF" />
            <stop offset="50%" stop-color="#E2E8F0" />
            <stop offset="100%" stop-color="#94A3B8" />
        </linearGradient>

        {{-- Subtle Field Transmitter Shadow --}}
        <filter id="ins-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#082F49" flood-opacity="0.3" />
        </filter>
    </defs>

    <g filter="url(#ins-shadow)">
        {{-- Left Conduit / Cable Entry Port --}}
        <rect x="8" y="18" width="10" height="12" rx="2" fill="#0369A1" stroke="#0284C7" stroke-width="0.8" />
        <ellipse cx="8" cy="24" rx="2.5" ry="5.5" fill="#0C4A6E" stroke="#38BDF8" stroke-width="0.8" />
        <ellipse cx="8" cy="24" rx="1.5" ry="3.5" fill="#082F49" />
        <line x1="12" y1="20" x2="12" y2="28" stroke="#075985" stroke-width="0.8" />
        <line x1="15" y1="19" x2="15" y2="29" stroke="#075985" stroke-width="0.8" />

        {{-- Bottom Process / Sensor Connection Hub --}}
        <rect x="18" y="44" width="10" height="13" rx="2" fill="#075985" stroke="#0369A1" stroke-width="0.8" />
        <ellipse cx="23" cy="56" rx="4.5" ry="1.8" fill="#0C4A6E" stroke="#38BDF8" stroke-width="0.8" />

        {{-- Top Stainless Steel Calibration Nameplate --}}
        <rect x="24" y="9" width="20" height="5" rx="1" fill="url(#ins-steel)" stroke="#64748B" stroke-width="0.6" />
        <circle cx="26" cy="11.5" r="0.8" fill="#475569" />
        <circle cx="42" cy="11.5" r="0.8" fill="#475569" />
        <line x1="29" y1="11.5" x2="39" y2="11.5" stroke="#64748B" stroke-width="0.6" stroke-dasharray="1.5 1" />

        {{-- Right Grounding Lug & Terminal Screw --}}
        <path d="M47 15 L52 16 L53 21 L48 20 Z" fill="url(#ins-steel)" stroke="#64748B" stroke-width="0.6" />
        <circle cx="50.5" cy="18" r="1.5" fill="#CBD5E1" stroke="#475569" stroke-width="0.6" />
        <line x1="49.5" y1="18" x2="51.5" y2="18" stroke="#334155" stroke-width="0.6" />

        {{-- Main Cylindrical Instrument Housing --}}
        <circle cx="34" cy="34" r="20" fill="url(#ins-body)" stroke="#0284C7" stroke-width="1.2" />

        {{-- Notched Bezel Perimeter Grips (Explosion-Proof Ridges) --}}
        <rect x="32" y="13" width="4" height="2" rx="0.5" fill="#38BDF8" />
        <rect x="32" y="53" width="4" height="2" rx="0.5" fill="#0369A1" />
        <rect x="13" y="32" width="2" height="4" rx="0.5" fill="#0284C7" />
        <rect x="53" y="32" width="2" height="4" rx="0.5" fill="#0284C7" />
        <rect x="19" y="19" width="3" height="3" rx="0.5" transform="rotate(45 20.5 20.5)" fill="#38BDF8" />
        <rect x="47" y="19" width="3" height="3" rx="0.5" transform="rotate(45 48.5 20.5)" fill="#0284C7" />
        <rect x="19" y="47" width="3" height="3" rx="0.5" transform="rotate(45 20.5 48.5)" fill="#075985" />
        <rect x="47" y="47" width="3" height="3" rx="0.5" transform="rotate(45 48.5 48.5)" fill="#0369A1" />

        {{-- Front Circular Bezel Ring --}}
        <circle cx="34" cy="34" r="17.5" fill="url(#ins-bezel)" stroke="#38BDF8" stroke-width="0.8" />

        {{-- Circular Glass Lens / Dark Recessed Window --}}
        <circle cx="34" cy="34" r="13.5" fill="url(#ins-inner-recess)" stroke="#075985" stroke-width="1" />

        {{-- Illuminated Digital LCD Screen Box --}}
        <rect x="23" y="27" width="22" height="14" rx="2" fill="url(#ins-lcd)" stroke="#059669" stroke-width="0.8" />

        {{-- Digital Value Readout (e.g. 101.3) --}}
        <text x="31.5" y="36" font-size="6" font-family="monospace" font-weight="900" fill="#064E3B" letter-spacing="-0.5" text-anchor="middle">101.3</text>

        {{-- Engineering Unit Tag (e.g. bar / kPa) --}}
        <text x="40.5" y="32.5" font-size="3" font-family="sans-serif" font-weight="900" fill="#047857" text-anchor="middle">bar</text>

        {{-- LCD Segment Bargraph Indicator --}}
        <rect x="25.5" y="37.5" width="17" height="1.8" rx="0.5" fill="#A7F3D0" stroke="#059669" stroke-width="0.4" />
        <rect x="26" y="38" width="11" height="0.8" rx="0.3" fill="#047857" />

        {{-- Glass Reflection Glare Highlight --}}
        <path d="M24 23 Q34 18 44 23 C38 23 30 25 24 23 Z" fill="#FFFFFF" opacity="0.35" />
        <circle cx="44" cy="24" r="1" fill="#FFFFFF" opacity="0.5" />
    </g>
</svg>
