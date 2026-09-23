@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Calibrator Movements: Fluke 700G Digital Pressure Calibrator Gauge in Industrial Green with Prominent Logistics Transfer Motion Arrow --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- Industrial Green Rubber Boot Casing Gradient --}}
        <linearGradient id="cm-gauge-body" x1="14" y1="12" x2="50" y2="48" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#10B981" />
            <stop offset="35%" stop-color="#059669" />
            <stop offset="75%" stop-color="#047857" />
            <stop offset="100%" stop-color="#064E3B" />
        </linearGradient>

        {{-- Dark Inner Dial Recess Plate Gradient --}}
        <radialGradient id="cm-gauge-face" cx="32" cy="30" r="15" gradientUnits="userSpaceOnUse">
            <stop offset="65%" stop-color="#1E293B" />
            <stop offset="100%" stop-color="#0F172A" />
        </radialGradient>

        {{-- High-Contrast Backlit LCD Screen Gradient --}}
        <linearGradient id="cm-lcd-bg" x1="21" y1="24" x2="43" y2="34" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#93C5FD" />
            <stop offset="50%" stop-color="#60A5FA" />
            <stop offset="100%" stop-color="#3B82F6" />
        </linearGradient>

        {{-- Prominent Logistics Motion Arrow Gradient --}}
        <linearGradient id="cm-arrow-grad" x1="8" y1="14" x2="56" y2="14" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#34D399" />
            <stop offset="60%" stop-color="#10B981" />
            <stop offset="100%" stop-color="#059669" />
        </linearGradient>

        {{-- Stainless Steel Bottom Fitting Stem Gradient --}}
        <linearGradient id="cm-steel-stem" x1="26" y1="48" x2="38" y2="60" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#F1F5F9" />
            <stop offset="50%" stop-color="#CBD5E1" />
            <stop offset="100%" stop-color="#94A3B8" />
        </linearGradient>

        {{-- Shadow Filter --}}
        <filter id="cm-shadow" x="2" y="2" width="60" height="60" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="2.5" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.32" />
        </filter>
    </defs>

    <g filter="url(#cm-shadow)">
        {{-- ================= 1. PROMINENT LOGISTICS MOTION ARROW ================= --}}
        {{-- Waypoint Origin Pulsing Beacon --}}
        <circle cx="8" cy="22" r="3.5" fill="#047857" stroke="#34D399" stroke-width="1.2" />
        <circle cx="8" cy="22" r="1.5" fill="#FFFFFF" />

        {{-- Motion Speed Trail Dashes --}}
        <path d="M 12 15 C 16 9, 23 6, 31 5" stroke="#34D399" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="3 2.5" opacity="0.75" />

        {{-- Primary Sweeping Motion Trajectory Arc --}}
        <path d="M 9 20 C 13 4, 49 4, 55 17" stroke="url(#cm-arrow-grad)" stroke-width="3.5" stroke-linecap="round" />

        {{-- Prominent Sharp Arrowhead --}}
        <path d="M 48 11 L 58 17 L 51 24 Z" fill="#34D399" stroke="#064E3B" stroke-width="0.8" stroke-linejoin="round" />

        {{-- ================= 2. BOTTOM STAINLESS STEEL PRESSURE CONNECTION ================= --}}
        {{-- Hex Nut Fitting --}}
        <path d="M27 48 H37 V53.5 H27 Z" fill="url(#cm-steel-stem)" stroke="#64748B" stroke-width="0.8" />
        <line x1="30.2" y1="48" x2="30.2" y2="53.5" stroke="#94A3B8" stroke-width="0.6" />
        <line x1="33.8" y1="48" x2="33.8" y2="53.5" stroke="#94A3B8" stroke-width="0.6" />
        {{-- Technical Specification Tag --}}
        <rect x="29" y="49.5" width="6" height="2.8" rx="0.4" fill="#1E293B" />
        <text x="32" y="51.5" font-size="1.6" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" text-anchor="middle">1 BAR</text>

        {{-- Threaded Process Connection Nipple --}}
        <rect x="29.5" y="53.5" width="5" height="5.5" fill="#64748B" />
        <line x1="29.5" y1="55" x2="34.5" y2="55" stroke="#CBD5E1" stroke-width="0.6" />
        <line x1="29.5" y1="56.8" x2="34.5" y2="56.8" stroke="#CBD5E1" stroke-width="0.6" />
        <line x1="29.5" y1="58.5" x2="34.5" y2="58.5" stroke="#CBD5E1" stroke-width="0.6" />

        {{-- ================= 3. FLUKE 700G INDUSTRIAL GREEN RUBBER CASING ================= --}}
        {{-- Side Ergonomic Rubber Grip Ridges --}}
        <rect x="12" y="21" width="3" height="6" rx="1.5" fill="#047857" />
        <rect x="12" y="32" width="3" height="6" rx="1.5" fill="#047857" />
        <rect x="49" y="21" width="3" height="6" rx="1.5" fill="#047857" />
        <rect x="49" y="32" width="3" height="6" rx="1.5" fill="#047857" />

        {{-- Main Circular Gauge Body --}}
        <circle cx="32" cy="30" r="18.5" fill="url(#cm-gauge-body)" stroke="#10B981" stroke-width="1.2" />

        {{-- Inner Dark Face Recess --}}
        <circle cx="32" cy="30" r="14.5" fill="url(#cm-gauge-face)" stroke="#064E3B" stroke-width="0.8" />

        {{-- Top Power Pushbutton with Glowing Icon --}}
        <circle cx="32" cy="19.5" r="2.2" fill="#064E3B" stroke="#34D399" stroke-width="0.8" />
        <path d="M32 18 V20" stroke="#A7F3D0" stroke-width="0.7" stroke-linecap="round" />
        <circle cx="32" cy="19.8" r="1.1" stroke="#A7F3D0" stroke-width="0.6" stroke-dasharray="2.5 1" fill="none" />

        {{-- Instrument Identity Sub-Banner --}}
        <rect x="22.5" y="22.5" width="19" height="2" rx="0.5" fill="#F59E0B" />
        <rect x="22.5" y="22.5" width="6.5" height="2" rx="0.5" fill="#1E293B" />

        {{-- ================= 4. BACKLIT DIGITAL LCD SCREEN ================= --}}
        <rect x="21" y="25.5" width="22" height="9.5" rx="1.5" fill="url(#cm-lcd-bg)" stroke="#1D4ED8" stroke-width="0.5" />
        
        {{-- Sun Backlight Indicator Icon --}}
        <circle cx="23" cy="27.5" r="0.7" fill="#082F49" />
        
        {{-- Digital Measurement Readout (15.000 PSI) --}}
        <text x="31" y="31.5" font-family="monospace, sans-serif" font-size="5" font-weight="900" fill="#082F49" text-anchor="middle" letter-spacing="-0.5">15.00</text>
        <text x="39.5" y="29.2" font-family="sans-serif" font-size="2.4" font-weight="900" fill="#082F49" text-anchor="middle">PSI</text>
        
        {{-- Segmented Bargraph Scale --}}
        <rect x="22.5" y="33" width="19" height="1" rx="0.3" fill="#1E40AF" opacity="0.3" />
        <rect x="22.5" y="33" width="13.5" height="1" rx="0.3" fill="#082F49" />

        {{-- ================= 5. KEYPAD CONTROLS & BOTTOM EMBOSSED BRANDING ================= --}}
        {{-- 4 Navigational Push Buttons --}}
        <rect x="22" y="36.5" width="4.2" height="2.2" rx="0.5" fill="#334155" stroke="#475569" stroke-width="0.4" />
        <rect x="27.2" y="36.5" width="4.2" height="2.2" rx="0.5" fill="#334155" stroke="#475569" stroke-width="0.4" />
        <rect x="32.5" y="36.5" width="4.2" height="2.2" rx="0.5" fill="#334155" stroke="#475569" stroke-width="0.4" />
        <rect x="37.8" y="36.5" width="4.2" height="2.2" rx="0.5" fill="#334155" stroke="#475569" stroke-width="0.4" />

        {{-- Embossed Calibrator Bottom Rim Accent --}}
        <path d="M25 42.8 Q32 45.5 39 42.8" stroke="#047857" stroke-width="1" stroke-linecap="round" fill="none" />
        <text x="32" y="44" font-family="sans-serif" font-size="2.6" font-weight="900" fill="#A7F3D0" text-anchor="middle" letter-spacing="0.5">CAL</text>
    </g>
</svg>
