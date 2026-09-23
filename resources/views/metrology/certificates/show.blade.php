<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 shadow-sm border border-brand-100 dark:border-brand-800">
                    <i class="fas fa-certificate text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                            {{ $certificate->reference ?: ('CERT-#' . $certificate->id) }}
                        </h2>
                        <x-badge :variant="$certificate->status?->badgeVariant() ?? 'neutral'">
                            {{ $certificate->status?->label() ?? ucfirst((string) $certificate->status) }}
                        </x-badge>
                        @if($certificate->is_locked)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600" title="{{ __('Locked against modification') }}">
                                <i class="fas fa-lock text-[11px] text-amber-500"></i>
                                <span>{{ __('Locked') }}</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" title="{{ __('Editable Draft') }}">
                                <i class="fas fa-lock-open text-[11px]"></i>
                                <span>{{ __('Unlocked') }}</span>
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Associated with') }}: 
                        <strong class="text-gray-700 dark:text-gray-200">{{ $certificate->equipment?->short_name ?: $certificate->equipment?->full_name }}</strong> 
                        ({{ $certificate->equipment?->internal_code }})
                    </p>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center flex-wrap gap-2">
                <a href="{{ route('metrology.calibration-certificates') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('Back to List') }}</span>
                </a>

                @if($certificate->certificate_path)
                    <a href="{{ route('metrology.calibration-certificates.download', $certificate) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fas fa-download"></i>
                        <span>{{ __('Download PDF') }}</span>
                    </a>
                @endif

                @can('edit calibration certificates')
                    @if(! $certificate->is_locked)
                        <a href="{{ route('metrology.calibration-certificates.edit', $certificate) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            <i class="fas fa-edit"></i>
                            <span>{{ __('Edit Certificate') }}</span>
                        </a>

                        <form method="POST" action="{{ route('metrology.calibration-certificates.approve', $certificate) }}" onsubmit="return confirm('{{ __('Are you sure you want to approve and officially lock this certificate?') }}')" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                <i class="fas fa-check-double"></i>
                                <span>{{ __('Approve & Lock') }}</span>
                            </button>
                        </form>
                    @else
                        <!-- Unlock with mandatory reason button -->
                        <button type="button" x-data="" @click="$dispatch('open-modal', 'unlock-certificate-modal')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            <i class="fas fa-unlock"></i>
                            <span>{{ __('Unlock Certificate') }}</span>
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: 'points' }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Flash Notifications -->
            @if(session('success'))
                <x-alert variant="success">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert variant="danger">{{ session('error') }}</x-alert>
            @endif

            <!-- 3-Card Header Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Certificate Metrology Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-microscope text-brand-600"></i>
                        <span>{{ __('Calibration Information') }}</span>
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Laboratory') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $certificate->laboratory_name ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Calibration Date') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $certificate->calibration_date ? $certificate->calibration_date->format('Y-m-d') : '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Expiry Date') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $certificate->expiry_date ? $certificate->expiry_date->format('Y-m-d') : '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Validity Period') }}:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $certificate->validity_period_months }} {{ __('Months') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Equipment Context Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-tools text-emerald-600"></i>
                            <span>{{ __('Equipment Details') }}</span>
                        </h4>
                        @if($certificate->equipment)
                            <a href="{{ route('metrology.equipment.show', $certificate->equipment) }}" class="text-[11px] text-brand-600 hover:text-brand-700 dark:text-brand-400 font-semibold hover:underline">
                                {{ __('View Profile') }} &rarr;
                            </a>
                        @endif
                    </div>

                    <div class="flex items-start gap-3.5">
                        <!-- Equipment Image Thumbnail -->
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center shadow-inner">
                            @if($certificate->equipment?->image_url)
                                <img src="{{ $certificate->equipment->image_url }}" alt="{{ $certificate->equipment->full_name }}" class="w-full h-full object-contain p-1">
                            @else
                                <div class="text-2xl text-gray-400">
                                    <i class="fas {{ $certificate->equipment?->category?->icon() ?? 'fa-tools' }}"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0 space-y-1.5 text-xs">
                            <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-700/60">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Equipment') }}:</span>
                                <span class="font-bold text-gray-900 dark:text-white truncate ps-1.5">{{ $certificate->equipment?->short_name ?: $certificate->equipment?->full_name }}</span>
                            </div>
                            <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-700/60">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Internal Code') }}:</span>
                                <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $certificate->equipment?->internal_code ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-700/60">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Serial Number') }}:</span>
                                <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $certificate->equipment?->serial_number ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between py-0.5">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Category') }}:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->equipment?->category?->label() ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compliance & Financial Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-shield-alt text-brand-600"></i>
                        <span>{{ __('Audit & Financials') }}</span>
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Calibration Cost') }}:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($certificate->price, 2) }} DZD</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Created By') }}:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->creator?->name ?: 'System / Legacy' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Approved By') }}:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->approver?->name ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Approved At') }}:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->approved_at ? $certificate->approved_at->format('Y-m-d H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="border-b border-gray-200 dark:border-gray-700 flex items-center gap-4 text-sm font-medium">
                <button type="button" @click="activeTab = 'points'" :class="activeTab === 'points' ? 'border-brand-600 text-brand-600 dark:text-brand-400 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent'" class="py-3 px-1 flex items-center gap-2 transition">
                    <i class="fas fa-list-ol"></i>
                    <span>{{ __('Calibration Points') }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ $certificate->calibrationPoints->count() }}
                    </span>
                </button>

                @if($certificate->certificate_path)
                    <button type="button" @click="activeTab = 'document'" :class="activeTab === 'document' ? 'border-brand-600 text-brand-600 dark:text-brand-400 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent'" class="py-3 px-1 flex items-center gap-2 transition">
                        <i class="fas fa-file-pdf"></i>
                        <span>{{ __('Certificate Document') }}</span>
                    </button>
                @endif

                <button type="button" @click="activeTab = 'environment'" :class="activeTab === 'environment' ? 'border-brand-600 text-brand-600 dark:text-brand-400 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 border-b-2 border-transparent'" class="py-3 px-1 flex items-center gap-2 transition">
                    <i class="fas fa-cloud-sun"></i>
                    <span>{{ __('Ambient & Remarks') }}</span>
                </button>
            </div>

            <!-- Tab 2: Calibration Points Table -->
            <div x-show="activeTab === 'points'" class="space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 overflow-hidden shadow-sm">
                    <div class="p-4 border-b border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-ruler text-brand-600"></i>
                            <span>{{ __('Conformity Points & Measurement Uncertainties') }}</span>
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-200/80 dark:border-gray-700/80 uppercase">
                                <tr>
                                    <th class="py-3 px-4">#</th>
                                    <th class="py-3 px-4">{{ __('Physical Quantity / Parameter') }}</th>
                                    <th class="py-3 px-4">{{ __('Nominal Value') }}</th>
                                    <th class="py-3 px-4">{{ __('Correction (C)') }}</th>
                                    <th class="py-3 px-4">{{ __('Uncertainty (U)') }}</th>
                                    <th class="py-3 px-4">{{ __('Lower Limit (C - U)') }}</th>
                                    <th class="py-3 px-4">{{ __('Upper Limit (C + U)') }}</th>
                                    <th class="py-3 px-4">{{ __('Tolerance Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @forelse($certificate->calibrationPoints as $idx => $pt)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td class="py-3 px-4 text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                            {{ $pt->equipmentSpecification?->grandeur?->name ?? __('Standard Parameter') }}
                                            @if($pt->equipmentSpecification?->grandeur?->symbol)
                                                <span class="text-xs text-gray-500 font-normal">({{ $pt->equipmentSpecification->grandeur->symbol }})</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white font-mono">{{ $pt->nominal_value }}</td>
                                        <td class="py-3 px-4 font-mono">{{ $pt->correction }}</td>
                                        <td class="py-3 px-4 font-mono">{{ $pt->uncertainty }}</td>
                                        <td class="py-3 px-4 font-mono text-gray-500">{{ round($pt->lower_limit, 4) }}</td>
                                        <td class="py-3 px-4 font-mono text-gray-500">{{ round($pt->upper_limit, 4) }}</td>
                                        <td class="py-3 px-4">
                                            <x-badge :variant="$pt->status?->badgeVariant() ?? 'neutral'">
                                                {{ $pt->status?->label() ?? 'In Tolerance' }}
                                            </x-badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-gray-400 italic">
                                            {{ __('No calibration points recorded for this certificate.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Certificate Document Viewer -->
            @if($certificate->certificate_path)
                <div x-show="activeTab === 'document'" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-pdf text-2xl text-rose-500"></i>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">
                                        {{ $certificate->file_name ?: basename($certificate->certificate_path) }}
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        @if($certificate->file_size)
                                            {{ number_format($certificate->file_size / 1024, 1) }} KB &bull;
                                        @endif
                                        {{ __('SHA256 CAS Verified') }}: <span class="font-mono text-[10px]">{{ substr($certificate->certificate_hash ?: '', 0, 16) }}...</span>
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('metrology.calibration-certificates.download', $certificate) }}" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                <i class="fas fa-download mr-1"></i> {{ __('Download Document') }}
                            </a>
                        </div>

                        <!-- Embedded PDF Frame -->
                        <div class="w-full h-[750px] rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900">
                            <iframe src="{{ $certificate->certificate_url }}#toolbar=1" class="w-full h-full" frameborder="0"></iframe>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 4: Environmental Conditions & Technical Remarks -->
            <div x-show="activeTab === 'environment'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center mb-2">
                            <i class="fas fa-temperature-high text-lg"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">{{ __('Ambient Temperature') }}</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white mt-1">
                            {{ $certificate->environmental_conditions['temperature_celsius'] ?? '—' }} °C
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mb-2">
                            <i class="fas fa-tint text-lg"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">{{ __('Relative Humidity') }}</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white mt-1">
                            {{ $certificate->environmental_conditions['humidity_percent'] ?? '—' }} %
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 flex items-center justify-center mb-2">
                            <i class="fas fa-compress-arrows-alt text-lg"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">{{ __('Atmospheric Pressure') }}</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white mt-1">
                            {{ $certificate->environmental_conditions['atmospheric_pressure_hpa'] ?? '—' }} hPa
                        </p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-2">
                    <h4 class="font-bold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Metrologist Technical Remarks') }}
                    </h4>
                    <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">
                        {{ $certificate->remarks ?: __('No special remarks recorded for this certificate.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal: Unlock Certificate with Reason -->
        <x-modal name="unlock-certificate-modal" focusable>
            <form method="POST" action="{{ route('metrology.calibration-certificates.unlock', $certificate) }}" class="p-6 space-y-4">
                @csrf
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center shrink-0">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">{{ __('Authorize Certificate Unlock') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('This action will be recorded in the immutable ISO audit trail.') }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Mandatory Justification / Reason') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" required rows="3" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-rose-500 focus:border-rose-500" placeholder="{{ __('Specify the reason for unlocking this certified record...') }}"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="$dispatch('close-modal', 'unlock-certificate-modal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        {{ __('Confirm Unlock') }}
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
