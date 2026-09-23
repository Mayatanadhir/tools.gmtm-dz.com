@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Expenses & Charges: Financial Ledger with Stacked Coins, Invoice Receipt & Cost Trendline --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="ex-card-bg" x1="10" y1="10" x2="54" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="#065F46" />
            <stop offset="1" stop-color="#022C22" />
        </linearGradient>
        <linearGradient id="ex-receipt-bg" x1="14" y1="12" x2="38" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#ECFDF5" />
        </linearGradient>
        <linearGradient id="ex-coin-gold" x1="36" y1="32" x2="52" y2="52" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FCD34D" />
            <stop offset="1" stop-color="#F59E0B" />
        </linearGradient>
        <filter id="ex-shadow" x="6" y="6" width="52" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-color="#022C22" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#ex-shadow)">
        {{-- Executive Card Frame --}}
        <rect x="10" y="10" width="44" height="46" rx="8" fill="url(#ex-card-bg)" stroke="#059669" stroke-width="1.2" />

        {{-- Financial Receipt Slip --}}
        <path d="M15 14 H37 V48 L34 46 L31 48 L28 46 L25 48 L22 46 L19 48 L15 46 Z" fill="url(#ex-receipt-bg)" />
        
        {{-- Receipt Header & Lines --}}
        <rect x="19" y="18" width="14" height="3" rx="1" fill="#059669" />
        <rect x="19" y="24" width="14" height="2" rx="1" fill="#94A3B8" />
        <rect x="19" y="28" width="10" height="2" rx="1" fill="#CBD5E1" />
        <rect x="19" y="32" width="12" height="2" rx="1" fill="#CBD5E1" />
        <line x1="18" y1="37" x2="33" y2="37" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="2 1" />
        <rect x="19" y="40" width="8" height="2.5" rx="1" fill="#059669" />

        {{-- Stacked Financial Coins on the Right (Preserved Golden Standard) --}}
        {{-- Coin 1 (Bottom) --}}
        <ellipse cx="44" cy="46" rx="8" ry="3.5" fill="#D97706" />
        <path d="M36 46 V48 C36 49.9 39.6 51.5 44 51.5 C48.4 51.5 52 49.9 52 48 V46" fill="#B45309" />
        <ellipse cx="44" cy="46" rx="8" ry="3.5" fill="url(#ex-coin-gold)" />

        {{-- Coin 2 (Middle) --}}
        <ellipse cx="44" cy="41" rx="8" ry="3.5" fill="#D97706" />
        <path d="M36 41 V43 C36 44.9 39.6 46.5 44 46.5 C48.4 46.5 52 44.9 52 43 V41" fill="#B45309" />
        <ellipse cx="44" cy="41" rx="8" ry="3.5" fill="url(#ex-coin-gold)" />

        {{-- Coin 3 (Top) --}}
        <ellipse cx="44" cy="36" rx="8" ry="3.5" fill="#D97706" />
        <path d="M36 36 V38 C36 39.9 39.6 41.5 44 41.5 C48.4 41.5 52 39.9 52 38 V36" fill="#B45309" />
        <ellipse cx="44" cy="36" rx="8" ry="3.5" fill="url(#ex-coin-gold)" />
        <text x="44" y="38" font-size="5" font-family="sans-serif" font-weight="bold" fill="#78350F" text-anchor="middle">$</text>

        {{-- Downward Cost Expense Sparkline Badge --}}
        <circle cx="45" cy="20" r="7.5" fill="#059669" stroke="#FFFFFF" stroke-width="1.5" />
        <path d="M42 18 L48 24 M48 24 V20 M48 24 H44" stroke="#FFFFFF" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
    </g>
</svg>
