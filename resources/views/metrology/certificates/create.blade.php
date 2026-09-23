<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-plus text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
                        {{ __('Register Calibration Certificate') }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Create a new metrological verification record conforming to ISO 17025 standards') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('metrology.calibration-certificates') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                <i class="fas fa-times mr-1"></i> {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        points: [
            { nominal_value: '', correction: '', uncertainty: '', equipment_specification_id: '' }
        ],
        calDate: '{{ now()->format('Y-m-d') }}',
        validityMonths: 12,
        expiryDate: '',
        updateExpiry() {
            if (!this.calDate) return;
            const d = new Date(this.calDate);
            d.setMonth(d.getMonth() + parseInt(this.validityMonths || 12));
            this.expiryDate = d.toISOString().split('T')[0];
        },
        addPoint() {
            this.points.push({ nominal_value: '', correction: '', uncertainty: '', equipment_specification_id: '' });
        },
        removePoint(index) {
            if (this.points.length > 1) {
                this.points.splice(index, 1);
            }
        },
        init() {
            this.updateExpiry();
        }
    }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('metrology.calibration-certificates.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: General & Equipment Context -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <i class="fas fa-info-circle text-brand-600"></i>
                        <span>{{ __('Certificate & Equipment Specification') }}</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Target Equipment / Standard') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="equipment_id" required class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                                <option value="">{{ __('Select Equipment...') }}</option>
                                @foreach($equipments as $eq)
                                    <option value="{{ $eq->id }}" @selected(old('equipment_id', request('equipment_id')) == $eq->id)>
                                        {{ $eq->short_name ?: $eq->full_name }} ({{ $eq->internal_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('equipment_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Certificate Reference / Number') }}
                            </label>
                            <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. CERT-2026-0089" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                            @error('reference') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Certificate Type') }}
                            </label>
                            <select name="certificate_type" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                                <option value="periodic">{{ __('Periodic Calibration (Annual)') }}</option>
                                <option value="initial">{{ __('Initial Calibration') }}</option>
                                <option value="after_repair">{{ __('Recalibration After Maintenance') }}</option>
                                <option value="intermediate">{{ __('Intermediate Verification') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Accredited Laboratory') }}
                            </label>
                            <input type="text" name="laboratory_name" value="{{ old('laboratory_name') }}" placeholder="e.g. ONML, CETIM, Fluke Calibration" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Calibration Date') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="calibration_date" x-model="calDate" @change="updateExpiry()" required class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Validity Period (Months)') }}
                            </label>
                            <input type="number" name="validity_period_months" x-model="validityMonths" @change="updateExpiry()" min="1" max="60" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Calculated Expiry Date') }}
                            </label>
                            <input type="date" name="expiry_date" x-model="expiryDate" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Service Cost / Price (DZD)') }}
                            </label>
                            <input type="number" name="price" step="0.01" min="0" value="{{ old('price', '0.00') }}" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Upload Official Certificate PDF') }}
                            </label>
                            <input type="file" name="certificate_file" accept="application/pdf" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                            <p class="text-[11px] text-gray-400 mt-1">{{ __('PDF up to 20MB. Automatically compressed with SHA256 CAS verification.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Ambient Environmental Conditions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <i class="fas fa-cloud-sun text-brand-600"></i>
                        <span>{{ __('Ambient Environmental Conditions & Remarks') }}</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Temperature (°C)') }}</label>
                            <input type="number" step="0.1" name="environmental_conditions[temperature_celsius]" placeholder="20.0" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Relative Humidity (%)') }}</label>
                            <input type="number" step="0.1" min="0" max="100" name="environmental_conditions[humidity_percent]" placeholder="50.0" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Atmospheric Pressure (hPa)') }}</label>
                            <input type="number" step="0.1" name="environmental_conditions[atmospheric_pressure_hpa]" placeholder="1013.2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Metrologist Remarks') }}</label>
                        <textarea name="remarks" rows="2" placeholder="{{ __('Optional laboratory notes or environmental remarks...') }}" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    </div>
                </div>

                <!-- Section 3: Metrological Calibration Points Grid -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-list-ol text-brand-600"></i>
                            <span>{{ __('Calibration Points') }}</span>
                        </h3>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="addPoint()" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 text-xs font-semibold rounded-lg transition">
                                <i class="fas fa-plus mr-1"></i> {{ __('Add Point') }}
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase font-semibold text-gray-500 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="py-2.5 px-3 w-12 text-center">#</th>
                                    <th class="py-2.5 px-3">{{ __('Nominal Value (Setpoint)') }} <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3">{{ __('Correction (C)') }} <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3">{{ __('Uncertainty (U)') }} <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-16 text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(pt, idx) in points" :key="idx">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td class="py-2 px-3 text-center font-mono text-gray-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="any" :name="'points[' + idx + '][nominal_value]'" x-model="pt.nominal_value" required placeholder="0.0" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="any" :name="'points[' + idx + '][correction]'" x-model="pt.correction" required placeholder="0.0" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="any" min="0" :name="'points[' + idx + '][uncertainty]'" x-model="pt.uncertainty" required placeholder="0.0" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" @click="removePoint(idx)" :disabled="points.length === 1" class="p-1 text-rose-500 hover:text-rose-700 disabled:opacity-30 transition">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form Bottom Action Bar -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('metrology.calibration-certificates') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fas fa-save mr-1.5"></i>
                        <span>{{ __('Save Certificate as Draft') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
