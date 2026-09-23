@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Equipment: Additel 221A Handheld Process Calibrator (mA/V/Temp/HART) --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- Industrial Green Rubberized Body Gradient --}}
        <linearGradient id="eq-body" x1="10" y1="4" x2="54" y2="62" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#10B981" />
            <stop offset="30%" stop-color="#059669" />
            <stop offset="70%" stop-color="#047857" />
            <stop offset="100%" stop-color="#022C22" />
        </linearGradient>

        {{-- Dark Top Header Gradient --}}
        <linearGradient id="eq-header" x1="14" y1="4" x2="50" y2="12" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#1E293B" />
            <stop offset="100%" stop-color="#0F172A" />
        </linearGradient>

        {{-- LCD Screen Gradient (light gray display) --}}
        <linearGradient id="eq-lcd" x1="16" y1="14" x2="48" y2="34" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#F1F5F9" />
            <stop offset="50%" stop-color="#E2E8F0" />
            <stop offset="100%" stop-color="#CBD5E1" />
        </linearGradient>

        {{-- Orange Source Label --}}
        <linearGradient id="eq-orange" x1="16" y1="25" x2="48" y2="29" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FB923C" />
            <stop offset="100%" stop-color="#EA580C" />
        </linearGradient>

        {{-- Keypad Dark Zone --}}
        <linearGradient id="eq-keypad" x1="14" y1="36" x2="50" y2="62" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#1E293B" />
            <stop offset="100%" stop-color="#0F172A" />
        </linearGradient>

        {{-- Device Shadow --}}
        <filter id="eq-shadow" x="6" y="2" width="54" height="62" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.35" />
        </filter>

        {{-- Industrial Green body side highlight --}}
        <linearGradient id="eq-side" x1="10" y1="30" x2="16" y2="50" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#6EE7B7" />
            <stop offset="100%" stop-color="#059669" />
        </linearGradient>
    </defs>

    <g filter="url(#eq-shadow)">
        {{-- Main Industrial Green Body --}}
        <rect x="13" y="3" width="38" height="58" rx="6" fill="url(#eq-body)" stroke="#047857" stroke-width="1" />

        {{-- Side grip ridges left --}}
        <rect x="13" y="20" width="3" height="4" rx="1" fill="#10B981" />
        <rect x="13" y="26" width="3" height="4" rx="1" fill="#10B981" />
        <rect x="13" y="32" width="3" height="4" rx="1" fill="#10B981" />

        {{-- Side grip ridges right --}}
        <rect x="48" y="20" width="3" height="4" rx="1" fill="#047857" />
        <rect x="48" y="26" width="3" height="4" rx="1" fill="#047857" />
        <rect x="48" y="32" width="3" height="4" rx="1" fill="#047857" />

        {{-- Dark Top Header Band --}}
        <rect x="13" y="3" width="38" height="10" rx="6" fill="url(#eq-header)" />
        <rect x="13" y="8" width="38" height="5" fill="url(#eq-header)" />

        {{-- "Additel" brand text line --}}
        <rect x="22" y="5.5" width="20" height="1.2" rx="0.6" fill="#64748B" />
        <rect x="30" y="7.5" width="8" height="1" rx="0.5" fill="#475569" />

        {{-- LCD Display Panel --}}
        <rect x="16" y="13" width="32" height="22" rx="2" fill="url(#eq-lcd)" stroke="#94A3B8" stroke-width="0.7" />

        {{-- Status bar top of LCD --}}
        <rect x="16" y="13" width="32" height="3.5" rx="2" fill="#CBD5E1" />
        <rect x="16" y="14.5" width="32" height="2" fill="#CBD5E1" />
        {{-- Battery indicator --}}
        <rect x="42" y="14.2" width="4" height="2" rx="0.4" fill="#64748B" />
        <rect x="43" y="14.7" width="2.5" height="1" rx="0.2" fill="#22C55E" />

        {{-- MEASURE label --}}
        <rect x="17" y="17.5" width="9" height="1.5" rx="0.5" fill="#475569" />

        {{-- MEASURE value: 20.0000 large text representation --}}
        <text x="34" y="24.5" font-size="7" font-family="monospace" font-weight="900" fill="#1E293B" text-anchor="middle" letter-spacing="-0.3">20.00</text>

        {{-- mA unit --}}
        <text x="44" y="19" font-size="3" font-family="sans-serif" font-weight="700" fill="#334155" text-anchor="middle">mA</text>

        {{-- Orange SOURCE bar --}}
        <rect x="17" y="25.5" width="10" height="2.5" rx="0.6" fill="url(#eq-orange)" />
        <text x="22" y="27.5" font-size="2.5" font-family="sans-serif" font-weight="900" fill="#FFFFFF" text-anchor="middle">SOURCE</text>

        {{-- Pt100 label --}}
        <rect x="30" y="25.5" width="8" height="2.5" rx="0.6" fill="#E2E8F0" />
        <text x="34" y="27.5" font-size="2.3" font-family="sans-serif" fill="#475569" text-anchor="middle">Pt100</text>

        {{-- SOURCE value: 100.00 --}}
        <text x="34" y="33" font-size="5.5" font-family="monospace" font-weight="700" fill="#1E293B" text-anchor="middle">100.00</text>

        {{-- °C unit --}}
        <text x="44.5" y="27.5" font-size="3" font-family="sans-serif" font-weight="700" fill="#334155" text-anchor="middle">°C</text>

        {{-- Dark keypad area --}}
        <rect x="14" y="36" width="36" height="23" rx="3" fill="url(#eq-keypad)" />

        {{-- Red power button --}}
        <circle cx="19" cy="39.5" r="3" fill="#EF4444" stroke="#B91C1C" stroke-width="0.7" />
        <circle cx="19" cy="39.5" r="1.5" fill="#FCA5A5" opacity="0.5" />
        <circle cx="19" cy="39.5" r="0.6" fill="#7F1D1D" />

        {{-- F1, F2, F3, F4 function keys --}}
        <rect x="25" y="37.5" width="4" height="2.5" rx="0.6" fill="#374151" stroke="#4B5563" stroke-width="0.4" />
        <rect x="30.5" y="37.5" width="4" height="2.5" rx="0.6" fill="#374151" stroke="#4B5563" stroke-width="0.4" />
        <rect x="36" y="37.5" width="4" height="2.5" rx="0.6" fill="#374151" stroke="#4B5563" stroke-width="0.4" />
        <rect x="41.5" y="37.5" width="4" height="2.5" rx="0.6" fill="#374151" stroke="#4B5563" stroke-width="0.4" />
        <text x="27" y="39.5" font-size="1.8" font-family="monospace" fill="#9CA3AF" text-anchor="middle">F1</text>
        <text x="32.5" y="39.5" font-size="1.8" font-family="monospace" fill="#9CA3AF" text-anchor="middle">F2</text>
        <text x="38" y="39.5" font-size="1.8" font-family="monospace" fill="#9CA3AF" text-anchor="middle">F3</text>
        <text x="43.5" y="39.5" font-size="1.8" font-family="monospace" fill="#9CA3AF" text-anchor="middle">F4</text>

        {{-- Numeric keypad 3x3 grid --}}
        <rect x="16" y="42" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="21" y="42" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="26" y="42" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="16" y="46" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="21" y="46" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="26" y="46" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="16" y="50" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="21" y="50" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="26" y="50" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />

        {{-- Numeric key labels 1-9 --}}
        <text x="18" y="44.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">1</text>
        <text x="23" y="44.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">2</text>
        <text x="28" y="44.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">3</text>
        <text x="18" y="48.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">4</text>
        <text x="23" y="48.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">5</text>
        <text x="28" y="48.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">6</text>
        <text x="18" y="52.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">7</text>
        <text x="23" y="52.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">8</text>
        <text x="28" y="52.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">9</text>

        {{-- Special function keys right side: HART, mA, TC/RTD, Ω --}}
        <rect x="32" y="42" width="7" height="2.5" rx="0.6" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="41" y="42" width="5" height="2.5" rx="0.6" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="32" y="45.5" width="7" height="2.5" rx="0.6" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="41" y="45.5" width="5" height="2.5" rx="0.6" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <text x="35.5" y="43.7" font-size="1.8" font-family="monospace" fill="#34D399" text-anchor="middle">HART</text>
        <text x="43.5" y="43.7" font-size="1.8" font-family="monospace" fill="#9CA3AF" text-anchor="middle">V/Hz</text>
        <text x="35.5" y="47.2" font-size="1.7" font-family="monospace" fill="#9CA3AF" text-anchor="middle">TC/RTD</text>
        <text x="43.5" y="47.2" font-size="1.8" font-family="sans-serif" fill="#9CA3AF" text-anchor="middle">mA</text>

        {{-- Navigation arrow keys cluster --}}
        <polygon points="38,50 40,48.5 40,51.5" fill="#4B5563" />
        <polygon points="46,50 44,48.5 44,51.5" fill="#4B5563" />
        <polygon points="42,48 40.5,50 43.5,50" fill="#4B5563" />
        <polygon points="42,52 40.5,50.5 43.5,50.5" fill="#4B5563" />
        <circle cx="42" cy="50" r="1.2" fill="#374151" />

        {{-- Bottom row: 0 key + Enter/Esc --}}
        <rect x="16" y="54" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="21" y="54" width="4" height="3" rx="0.7" fill="#1F2937" stroke="#374151" stroke-width="0.4" />
        <rect x="26" y="54" width="11" height="3" rx="0.7" fill="#374151" stroke="#4B5563" stroke-width="0.4" />
        <rect x="38.5" y="54" width="7" height="3" rx="0.7" fill="#059669" stroke="#10B981" stroke-width="0.4" />
        <text x="18" y="56.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">*</text>
        <text x="23" y="56.2" font-size="2" font-family="monospace" font-weight="700" fill="#D1D5DB" text-anchor="middle">0</text>
        <text x="31.5" y="56.2" font-size="1.7" font-family="monospace" fill="#9CA3AF" text-anchor="middle">Esc</text>
        <text x="42" y="56.2" font-size="1.7" font-family="monospace" fill="#FFFFFF" text-anchor="middle">Enter</text>

        {{-- LCD screen glare highlight --}}
        <path d="M17 14 Q32 12 47 14 C40 15 24 16 17 14 Z" fill="#FFFFFF" opacity="0.25" />
    </g>
</svg>
