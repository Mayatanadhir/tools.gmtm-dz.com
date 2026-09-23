<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('metrology.equipment') }}" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                        {{ $equipment->full_name }}
                    </h2>
                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if($itemCode = $equipment->internal_code)
                            <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 font-mono text-[11px]">{{ $itemCode }}</code>
                            <span>•</span>
                        @endif
                        @if($sn = $equipment->serial_number)
                            <span>S/N: {{ $sn }}</span>
                            <span>•</span>
                        @endif
                        <span>{{ $equipment->category->label() }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-badge :variant="$equipment->status->badgeVariant()" size="md" :dot="true">
                    {{ $equipment->status->label() }}
                </x-badge>

                @can('edit equipment')
                    <x-secondary-button
                        type="button"
                        @click="showEditModal = true"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold"
                        title="{{ __('Edit Equipment') }}"
                    >
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('Edit') }}</span>
                    </x-secondary-button>
                @endcan

                @can('create calibration certificates')
                    @if($equipment->requires_calibration)
                        <a href="{{ route('metrology.calibration-certificates.create', ['equipment_id' => $equipment->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            <i class="fas fa-certificate"></i>
                            <span>{{ __('Register Certificate') }}</span>
                        </a>
                    @endif
                @endcan

                @can('delete equipment')
                    <form method="POST" action="{{ route('metrology.equipment.destroy', $equipment) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this equipment?') }}')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200 dark:border-rose-900/50 transition" title="{{ __('Delete Equipment') }}">
                            <i class="fas fa-trash-alt"></i>
                            <span>{{ __('Delete') }}</span>
                        </button>
                    </form>
                @endcan

                <a href="{{ route('metrology.equipment') }}">
                    <x-secondary-button type="button">
                        {{ __('Back to Inventory') }}
                    </x-secondary-button>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $specsJson = json_encode($equipment->specifications->keyBy('grandeur_id')->map(fn($s) => [
            'selected' => true,
            'min' => (float) $s->range_min,
            'max' => (float) $s->range_max,
            'acc' => (float) $s->accuracy_value,
            'acc_type' => $s->accuracy_type->value ?? '%',
        ])->all());
    @endphp

    <div class="py-8" x-data="{
        activeTab: window.location.hash ? window.location.hash.replace('#', '') : 'overview',
        showEditModal: {{ ($errors->any() && old('_method') === 'PUT') ? 'true' : 'false' }},
        editFullName: '{{ old('_method') === 'PUT' ? addslashes((string) old('full_name', '')) : addslashes((string) $equipment->full_name) }}',
        editShortName: '{{ old('_method') === 'PUT' ? addslashes((string) old('short_name', '')) : addslashes((string) $equipment->short_name) }}',
        editInternalCode: '{{ old('_method') === 'PUT' ? addslashes((string) old('internal_code', '')) : addslashes((string) $equipment->internal_code) }}',
        editSerialNumber: '{{ old('_method') === 'PUT' ? addslashes((string) old('serial_number', '')) : addslashes((string) $equipment->serial_number) }}',
        editCategory: '{{ old('_method') === 'PUT' ? addslashes((string) old('category', 'measuring_instrument')) : $equipment->category->value }}',
        editPackage: '{{ old('_method') === 'PUT' ? addslashes((string) old('package', 'none')) : $equipment->package->value }}',
        editStatus: '{{ old('_method') === 'PUT' ? addslashes((string) old('status', 'active')) : $equipment->status->value }}',
        editRequiresCalibration: {{ old('_method') === 'PUT' ? (old('requires_calibration') ? 'true' : 'false') : ($equipment->requires_calibration ? 'true' : 'false') }},
        editDesignation: '{{ old('_method') === 'PUT' ? addslashes((string) old('designation', '')) : addslashes((string) $equipment->designation) }}',
        editNotes: '{{ old('_method') === 'PUT' ? addslashes((string) old('notes', '')) : addslashes((string) $equipment->notes) }}',
        editImageUrl: '{{ $equipment->image_url }}',
        editCertificateUrl: '{{ $equipment->certificate_url }}',
        editImagePreview: null,
        editRemoveImage: false,
        editRemoveCertificate: false,
        editSpecs: {{ $specsJson }},
        handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.editImagePreview = URL.createObjectURL(file);
                this.editRemoveImage = false;
            }
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-metrology-tabs active="equipment" />
                </aside>

                <!-- Main Content -->
                <main class="flex-1 w-full min-w-0 space-y-6">
                    <!-- Top Equipment Profile Card -->
                    <div class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col md:flex-row gap-6 items-start">
                        <div class="w-32 h-32 shrink-0 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 flex items-center justify-center shadow-sm p-1.5">
                            @if($equipment->image_url)
                                <a href="{{ $equipment->image_url }}" target="_blank" title="{{ __('View Full Image') }}" class="w-full h-full flex items-center justify-center group">
                                    <img src="{{ $equipment->image_url }}" alt="{{ $equipment->full_name }}" class="w-full h-full object-contain transition-transform duration-200 group-hover:scale-105">
                                </a>
                            @else
                                <div class="text-4xl text-gray-400">
                                    <i class="fas {{ $equipment->category->icon() }}"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge :variant="$equipment->category->badgeVariant()" size="sm">
                                    <i class="fas {{ $equipment->category->icon() }} me-1"></i>
                                    {{ $equipment->category->label() }}
                                </x-badge>
                                <x-badge variant="neutral" size="sm">
                                    {{ $equipment->package->label() }}
                                </x-badge>
                                @if($equipment->requires_calibration)
                                    <x-badge variant="info" size="sm" :dot="true">
                                        {{ __('Calibration Required') }}
                                    </x-badge>
                                @endif
                            </div>

                            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">
                                {{ $equipment->full_name }}
                            </h1>

                            @if($equipment->designation)
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $equipment->designation }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Interactive Tabs Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-6 rtl:space-x-reverse" aria-label="Tabs">
                            <button
                                type="button"
                                @click="activeTab = 'overview'"
                                :class="activeTab === 'overview'
                                    ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2"
                            >
                                <i class="fas fa-info-circle"></i>
                                <span>{{ __('Overview & Technical Specs') }}</span>
                            </button>

                            <button
                                type="button"
                                @click="activeTab = 'specifications'"
                                :class="activeTab === 'specifications'
                                    ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2"
                            >
                                <i class="fas fa-wave-square"></i>
                                <span>{{ __('Measurement & Generation Capabilities') }}</span>
                                <span class="ms-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ $equipment->specifications->count() }}
                                </span>
                            </button>

                            <button
                                type="button"
                                @click="activeTab = 'documents'"
                                :class="activeTab === 'documents'
                                    ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2"
                            >
                                <i class="fas fa-certificate"></i>
                                <span>{{ __('Calibration Certificates') }}</span>
                                <span class="ms-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ $equipment->calibrationCertificates->count() }}
                                </span>
                            </button>

                            <button
                                type="button"
                                @click="activeTab = 'audit'"
                                :class="activeTab === 'audit'
                                    ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2"
                            >
                                <i class="fas fa-history"></i>
                                <span>{{ __('Audit Trail') }}</span>
                            </button>


                        </nav>
                    </div>

                    <!-- Tab 1: Overview & Technical Specs -->
                    <div x-show="activeTab === 'overview'" x-transition class="space-y-6">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Internal Inventory Code') }}</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white mt-1 font-mono">
                                        {{ $equipment->internal_code ?: '—' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Serial Number (S/N)') }}</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white mt-1 font-mono">
                                        {{ $equipment->serial_number ?: '—' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Category') }}</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ $equipment->category->label() }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Logistics Package / Lot') }}</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ $equipment->package->label() }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Requires Calibration') }}</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                        {{ $equipment->requires_calibration ? __('Yes, periodic calibration required') : __('No') }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Registration Date') }}</div>
                                    <div class="text-sm text-gray-900 dark:text-white mt-1 font-mono">
                                        {{ $equipment->created_at?->format('Y-m-d H:i') ?: '—' }}
                                    </div>
                                </div>
                            </div>

                            @if($equipment->designation || $equipment->notes)
                                <div class="p-4 sm:p-6 space-y-4">
                                    @if($equipment->designation)
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Designation') }}</div>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 whitespace-pre-line">{{ $equipment->designation }}</p>
                                        </div>
                                    @endif

                                    @if($equipment->notes)
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Notes') }}</div>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 whitespace-pre-line">{{ $equipment->notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tab 2: Measurement & Source Capabilities -->
                    <div x-show="activeTab === 'specifications'" x-transition class="space-y-6">
                        @php
                            $mesureSpecs = $equipment->specifications->filter(fn($s) => $s->grandeur->type === \App\Enums\GrandeurType::Measurement);
                            $sourceSpecs = $equipment->specifications->filter(fn($s) => $s->grandeur->type === \App\Enums\GrandeurType::Source);
                        @endphp

                        @if($mesureSpecs->count() === 0 && $sourceSpecs->count() === 0)
                            <div class="p-8 text-center rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <i class="fas fa-sliders-h text-gray-400 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No physical specifications configured for this equipment.') }}</p>
                            </div>
                        @endif

                        @if($mesureSpecs->count() > 0)
                            <x-table>
                                <x-slot:toolbar>
                                    <h4 class="text-sm font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                                        <i class="fas fa-signal"></i>
                                        <span>{{ __('Measurement Capabilities (Sensors / In)') }}</span>
                                    </h4>
                                </x-slot:toolbar>

                                <x-slot:header>
                                    <x-table.th>{{ __('Parameter') }}</x-table.th>
                                    <x-table.th>{{ __('Unit Symbol') }}</x-table.th>
                                    <x-table.th>{{ __('Measurement Range') }}</x-table.th>
                                    <x-table.th class="text-end">{{ __('Accuracy') }}</x-table.th>
                                </x-slot:header>

                                @foreach($mesureSpecs as $spec)
                                    <x-table.tr>
                                        <x-table.td>
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $spec->grandeur->name }}</span>
                                        </x-table.td>
                                        <x-table.td>
                                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                                {{ $spec->grandeur->symbol }}
                                            </span>
                                        </x-table.td>
                                        <x-table.td>
                                            <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                                {{ $spec->range_min }} → {{ $spec->range_max }} {{ $spec->grandeur->symbol }}
                                            </span>
                                        </x-table.td>
                                        <x-table.td class="text-end">
                                            <span class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                                ±{{ $spec->accuracy_value }} {{ $spec->accuracy_type->value ?? '%' }}
                                            </span>
                                        </x-table.td>
                                    </x-table.tr>
                                @endforeach
                            </x-table>
                        @endif

                        @if($sourceSpecs->count() > 0)
                            <x-table>
                                <x-slot:toolbar>
                                    <h4 class="text-sm font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                                        <i class="fas fa-bolt"></i>
                                        <span>{{ __('Source Capabilities (Generators / Out)') }}</span>
                                    </h4>
                                </x-slot:toolbar>

                                <x-slot:header>
                                    <x-table.th>{{ __('Parameter') }}</x-table.th>
                                    <x-table.th>{{ __('Unit Symbol') }}</x-table.th>
                                    <x-table.th>{{ __('Generation Range') }}</x-table.th>
                                    <x-table.th class="text-end">{{ __('Accuracy') }}</x-table.th>
                                </x-slot:header>

                                @foreach($sourceSpecs as $spec)
                                    <x-table.tr>
                                        <x-table.td>
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $spec->grandeur->name }}</span>
                                        </x-table.td>
                                        <x-table.td>
                                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                                                {{ $spec->grandeur->symbol }}
                                            </span>
                                        </x-table.td>
                                        <x-table.td>
                                            <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                                {{ $spec->range_min }} → {{ $spec->range_max }} {{ $spec->grandeur->symbol }}
                                            </span>
                                        </x-table.td>
                                        <x-table.td class="text-end">
                                            <span class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                                ±{{ $spec->accuracy_value }} {{ $spec->accuracy_type->value ?? '%' }}
                                            </span>
                                        </x-table.td>
                                    </x-table.tr>
                                @endforeach
                            </x-table>
                        @endif
                    </div>

                    <!-- Tab 3: Calibration Certificates History & Actions -->
                    <div x-show="activeTab === 'documents'" x-transition class="space-y-6">
                        <x-table>
                            <x-slot:toolbar>
                                <div class="flex items-center justify-between w-full">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <i class="fas fa-certificate text-brand-600"></i>
                                        <span>{{ __('Calibration Certificates History') }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300 font-bold">
                                            {{ $equipment->calibrationCertificates->count() }}
                                        </span>
                                    </h4>

                                    @can('create calibration certificates')
                                        @if($equipment->requires_calibration)
                                            <a href="{{ route('metrology.calibration-certificates.create', ['equipment_id' => $equipment->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                <i class="fas fa-plus"></i>
                                                <span>{{ __('Register Certificate') }}</span>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </x-slot:toolbar>

                            <x-slot:header>
                                <x-table.th>{{ __('Certificate') }}</x-table.th>
                                <x-table.th>{{ __('Type') }}</x-table.th>
                                <x-table.th>{{ __('Laboratory') }}</x-table.th>
                                <x-table.th>{{ __('Calibration Date') }}</x-table.th>
                                <x-table.th>{{ __('Expiry Date') }}</x-table.th>
                                <x-table.th>{{ __('Status') }}</x-table.th>
                                <x-table.th class="text-end">{{ __('Actions') }}</x-table.th>
                            </x-slot:header>

                            @forelse($equipment->calibrationCertificates as $cert)
                                <x-table.tr>
                                    <x-table.td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                                                <i class="fas fa-certificate text-sm"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('metrology.calibration-certificates.show', $cert) }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition">
                                                    {{ $cert->reference ?: ('CERT-#' . $cert->id) }}
                                                </a>
                                                @if($cert->is_locked)
                                                    <span class="inline-flex items-center text-amber-600 dark:text-amber-400 ml-1 text-xs" title="{{ __('Locked & Approved') }}">
                                                        <i class="fas fa-lock text-[10px]"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </x-table.td>

                                    <x-table.td>
                                        <span class="text-xs text-gray-600 dark:text-gray-400 capitalize">
                                            {{ $cert->certificate_type }}
                                        </span>
                                    </x-table.td>

                                    <x-table.td>
                                        <span class="text-xs text-gray-800 dark:text-gray-200">
                                            {{ $cert->laboratory_name ?: '—' }}
                                        </span>
                                    </x-table.td>

                                    <x-table.td>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">
                                            {{ $cert->calibration_date ? $cert->calibration_date->format('Y-m-d') : '—' }}
                                        </span>
                                    </x-table.td>

                                    <x-table.td>
                                        @if($cert->expiry_date)
                                            <div class="space-y-0.5">
                                                <span class="text-xs font-medium text-gray-900 dark:text-white block">
                                                    {{ $cert->expiry_date->format('Y-m-d') }}
                                                </span>
                                                @php $rem = $cert->remaining_days; @endphp
                                                @if($rem !== null)
                                                    @if($rem < 0)
                                                        <span class="text-[10px] font-semibold text-rose-600 dark:text-rose-400 block">
                                                            <i class="fas fa-times-circle mr-0.5"></i> {{ __('Expired :days d ago', ['days' => abs($rem)]) }}
                                                        </span>
                                                    @elseif($rem <= 30)
                                                        <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 block">
                                                            <i class="fas fa-exclamation-triangle mr-0.5"></i> {{ __('Expires in :days d', ['days' => $rem]) }}
                                                        </span>
                                                    @else
                                                        <span class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400 block">
                                                            <i class="fas fa-check mr-0.5"></i> {{ __(':days days remaining', ['days' => $rem]) }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </x-table.td>

                                    <x-table.td>
                                        <x-badge :variant="$cert->status?->badgeVariant() ?? 'neutral'" size="sm">
                                            {{ $cert->status?->label() ?? ucfirst((string) $cert->status) }}
                                        </x-badge>
                                    </x-table.td>

                                    <x-table.td class="text-end">
                                        <x-table.actions>
                                            <x-table.action-view
                                                href="{{ route('metrology.calibration-certificates.show', $cert) }}"
                                                :title="__('View Details')"
                                            />

                                            @if($cert->certificate_path)
                                                <x-table.action-download
                                                    href="{{ route('metrology.calibration-certificates.download', $cert) }}"
                                                    :title="__('Download PDF')"
                                                />
                                            @endif

                                            @can('edit calibration certificates')
                                                @if(! $cert->is_locked)
                                                    <x-table.action-edit
                                                        href="{{ route('metrology.calibration-certificates.edit', $cert) }}"
                                                        :title="__('Edit')"
                                                    />
                                                    <form method="POST" action="{{ route('metrology.calibration-certificates.approve', $cert) }}" onsubmit="return confirm('{{ __('Are you sure you want to approve and officially lock this certificate?') }}')" class="inline">
                                                        @csrf
                                                        <x-table.action
                                                            type="success"
                                                            buttonType="submit"
                                                            :title="__('Approve & Lock')"
                                                        />
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('delete calibration certificates')
                                                @if(! $cert->is_locked)
                                                    <form method="POST" action="{{ route('metrology.calibration-certificates.destroy', $cert) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this certificate?') }}')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-table.action-delete
                                                            buttonType="submit"
                                                            :title="__('Delete')"
                                                        />
                                                    </form>
                                                @endif
                                            @endcan
                                        </x-table.actions>
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <x-table.empty :colspan="7" :message="__('No calibration certificates recorded for this equipment yet.')" />
                            @endforelse
                        </x-table>
                    </div>

                    <!-- Tab 4: Audit Trail -->
                    <div x-show="activeTab === 'audit'" x-transition class="space-y-6">
                        <x-table>
                            <x-slot:toolbar>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="fas fa-history"></i>
                                    <span>{{ __('Forensic Activity Log') }}</span>
                                </h4>
                            </x-slot:toolbar>

                            <x-slot:header>
                                <x-table.th>{{ __('Timestamp') }}</x-table.th>
                                <x-table.th>{{ __('Event') }}</x-table.th>
                                <x-table.th>{{ __('Causer / Actor') }}</x-table.th>
                                <x-table.th>{{ __('Changes / Description') }}</x-table.th>
                            </x-slot:header>

                            @forelse($equipment->activities as $act)
                                <x-table.tr>
                                    <x-table.td>
                                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                            {{ $act->created_at?->format('Y-m-d H:i:s') }}
                                        </span>
                                    </x-table.td>

                                    <x-table.td>
                                        <x-badge :variant="$act->description === 'created' ? 'success' : 'info'" size="sm">
                                            {{ ucfirst($act->description) }}
                                        </x-badge>
                                    </x-table.td>

                                    <x-table.td>
                                        @if($causer = $act->causer)
                                            <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $causer->name }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">System / CLI</span>
                                        @endif
                                    </x-table.td>

                                    <x-table.td>
                                        @if($changes = $act->properties->get('attributes'))
                                            <div class="text-xs font-mono text-gray-600 dark:text-gray-300">
                                                {{ json_encode(array_keys($changes)) }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <x-table.empty :colspan="4" :message="__('No audit logs recorded for this equipment.')" />
                            @endforelse
                        </x-table>
                    </div>


                </main>
            </div>
        </div>

        <!-- ================= EDIT EQUIPMENT MODAL ================= -->
        @can('edit equipment')
            <div
                x-show="showEditModal"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div
                        x-show="showEditModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showEditModal = false"
                        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                    ></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        x-show="showEditModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-start overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100 dark:border-gray-700"
                    >
                        <form action="{{ route('metrology.equipment.update', $equipment) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_redirect" value="{{ route('metrology.equipment.show', $equipment) }}">

                            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ __('Edit Equipment') }}
                                    </h3>
                                </div>
                                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500 text-xl font-bold">
                                    &times;
                                </button>
                            </div>

                            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                                <!-- Basic Information -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <x-input-label for="edit_full_name" :value="__('Full Name')" required />
                                        <x-text-input id="edit_full_name" name="full_name" type="text" class="mt-1 block w-full" x-model="editFullName" required />
                                        <x-input-error :messages="$errors->get('full_name')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="edit_short_name" :value="__('Short / Acronym Name')" />
                                        <x-text-input id="edit_short_name" name="short_name" type="text" class="mt-1 block w-full" x-model="editShortName" />
                                    </div>

                                    <div>
                                        <x-input-label for="edit_internal_code" :value="__('Storage / Internal Code')" />
                                        <x-text-input id="edit_internal_code" name="internal_code" type="text" class="mt-1 block w-full font-mono" x-model="editInternalCode" />
                                    </div>

                                    <div>
                                        <x-input-label for="edit_serial_number" :value="__('Serial Number (S/N)')" />
                                        <x-text-input id="edit_serial_number" name="serial_number" type="text" class="mt-1 block w-full font-mono" x-model="editSerialNumber" />
                                    </div>

                                    <div>
                                        <x-input-label for="edit_category" :value="__('Equipment Category')" required />
                                        <select
                                            id="edit_category"
                                            name="category"
                                            x-model="editCategory"
                                            class="mt-1 block w-full py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            required
                                        >
                                            @foreach(\App\Enums\EquipmentCategory::cases() as $cat)
                                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <x-input-label for="edit_package" :value="__('Logistics Package / Lot')" />
                                        <select
                                            id="edit_package"
                                            name="package"
                                            x-model="editPackage"
                                            class="mt-1 block w-full py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        >
                                            @foreach(\App\Enums\EquipmentPackage::cases() as $pkg)
                                                <option value="{{ $pkg->value }}">{{ $pkg->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <x-input-label for="edit_status" :value="__('Operational Status')" />
                                        <select
                                            id="edit_status"
                                            name="status"
                                            x-model="editStatus"
                                            class="mt-1 block w-full py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        >
                                            @foreach(\App\Enums\EquipmentStatus::cases() as $st)
                                                <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Calibration Switch -->
                                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="requires_calibration"
                                            value="1"
                                            x-model="editRequiresCalibration"
                                            class="rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-500 w-4 h-4"
                                        >
                                        <div>
                                            <div class="font-semibold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                                <i class="fas fa-certificate text-orange-500"></i>
                                                <span>{{ __('Requires Periodic Metrological Calibration') }}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Physical Specifications Section -->
                                <div x-show="editRequiresCalibration" x-transition class="space-y-4">
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center gap-2">
                                        <i class="fas fa-wave-square text-indigo-500"></i>
                                        <span>{{ __('Measurement & Generation Capabilities') }}</span>
                                    </h4>

                                    <!-- Measurement Parameters -->
                                    @if(isset($measurementGrandeurs) && $measurementGrandeurs->count() > 0)
                                        <div>
                                            <div class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-2">
                                                <i class="fas fa-signal me-1"></i> {{ __('Measurement Capabilities') }}
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($measurementGrandeurs as $g)
                                                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm space-y-2">
                                                        <label class="flex items-center justify-between cursor-pointer">
                                                            <span class="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                <span>{{ $g->name }}</span>
                                                                <span class="text-[11px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold">({{ $g->symbol }})</span>
                                                            </span>
                                                            <input type="checkbox" name="params[{{ $g->id }}][selected]" value="1" :checked="editSpecs[{{ $g->id }}]?.selected" class="rounded text-brand-600 focus:ring-brand-500">
                                                        </label>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][min]" placeholder="{{ __('Min') }}" :value="editSpecs[{{ $g->id }}]?.min ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][max]" placeholder="{{ __('Max') }}" :value="editSpecs[{{ $g->id }}]?.max ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][acc]" placeholder="{{ __('Accuracy') }}" :value="editSpecs[{{ $g->id }}]?.acc ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                            <select name="params[{{ $g->id }}][acc_type]" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                                <option value="%" :selected="editSpecs[{{ $g->id }}]?.acc_type === '%'">%</option>
                                                                <option value="abs" :selected="editSpecs[{{ $g->id }}]?.acc_type === 'abs'">Abs</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Source Parameters -->
                                    @if(isset($sourceGrandeurs) && $sourceGrandeurs->count() > 0)
                                        <div>
                                            <div class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-2 mt-4">
                                                <i class="fas fa-bolt me-1"></i> {{ __('Source Capabilities') }}
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($sourceGrandeurs as $g)
                                                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm space-y-2">
                                                        <label class="flex items-center justify-between cursor-pointer">
                                                            <span class="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                <span>{{ $g->name }}</span>
                                                                <span class="text-[11px] px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 font-bold">({{ $g->symbol }})</span>
                                                            </span>
                                                            <input type="checkbox" name="params[{{ $g->id }}][selected]" value="1" :checked="editSpecs[{{ $g->id }}]?.selected" class="rounded text-brand-600 focus:ring-brand-500">
                                                        </label>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][min]" placeholder="{{ __('Min') }}" :value="editSpecs[{{ $g->id }}]?.min ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][max]" placeholder="{{ __('Max') }}" :value="editSpecs[{{ $g->id }}]?.max ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="number" step="any" name="params[{{ $g->id }}][acc]" placeholder="{{ __('Accuracy') }}" :value="editSpecs[{{ $g->id }}]?.acc ?? ''" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                            <select name="params[{{ $g->id }}][acc_type]" class="py-1 px-2 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                                <option value="%" :selected="editSpecs[{{ $g->id }}]?.acc_type === '%'">%</option>
                                                                <option value="abs" :selected="editSpecs[{{ $g->id }}]?.acc_type === 'abs'">Abs</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Media Uploads: Image & Certificate -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <div>
                                        <x-input-label for="edit_image" :value="__('Change Equipment Photo')" />
                                        <input
                                            id="edit_image"
                                            name="image"
                                            type="file"
                                            accept="image/*"
                                            @change="handleImageChange($event)"
                                            class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                                        >
                                        <template x-if="editImagePreview || editImageUrl">
                                            <div class="mt-2 flex items-center gap-3">
                                                <img :src="editImagePreview || editImageUrl" class="w-16 h-16 object-contain p-0.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <label class="text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1 cursor-pointer">
                                                    <input type="checkbox" name="remove_image" value="1" x-model="editRemoveImage" class="rounded text-rose-600">
                                                    <span>{{ __('Remove image') }}</span>
                                                </label>
                                            </div>
                                        </template>
                                    </div>

                                    <div>
                                        <x-input-label for="edit_certificate" :value="__('Change Certificate (PDF)')" />
                                        <input
                                            id="edit_certificate"
                                            name="certificate"
                                            type="file"
                                            accept=".pdf"
                                            class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                                        >
                                        <template x-if="editCertificateUrl">
                                            <div class="mt-2 flex items-center gap-3">
                                                <a :href="editCertificateUrl" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                                    <i class="fas fa-file-pdf"></i>
                                                    <span>{{ __('View Current Certificate') }}</span>
                                                </a>
                                                <label class="text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1 cursor-pointer">
                                                    <input type="checkbox" name="remove_certificate" value="1" x-model="editRemoveCertificate" class="rounded text-rose-600">
                                                    <span>{{ __('Remove') }}</span>
                                                </label>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <x-input-label for="edit_designation" :value="__('Designation / Technical Notes')" />
                                        <textarea id="edit_designation" name="designation" rows="2" x-model="editDesignation" class="mt-1 block w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                                <x-secondary-button type="button" @click="showEditModal = false">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                                <x-primary-button type="submit">
                                    {{ __('Update Equipment') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
</x-app-layout>
