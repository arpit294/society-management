<form id="flat-ajax-form" method="POST" action="{{ route('flats.store') }}">
    @csrf

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Add {{ \App\Models\Setting::label('unit', 'Flat') }}</h5>
            <p class="text-muted mb-0 small">Create a new {{ strtolower(\App\Models\Setting::label('unit', 'flat')) }} record.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ \App\Models\Setting::label('block', 'Block') }}</label>
                <select name="block_id" class="form-control">
                    <option value="">Select {{ \App\Models\Setting::label('block', 'Block') }}</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}" data-total-floor="{{ $block->total_floor }}" {{ old('block_id') == $block->id ? 'selected' : '' }}>
                            {{ $block->block_name }}</option>
                    @endforeach
                </select>
                @error('block_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                @php
                    $structure = $structureType ?? \App\Models\Setting::get('society_property_type', 'flat_residential');
                @endphp
                <select name="unit_type" id="unit_type_select" class="form-select">
                    @if ($structure === 'commercial_complex')
                        <option value="shop" {{ old('unit_type', 'shop') == 'shop' ? 'selected' : '' }}>Commercial Shop</option>
                        <option value="office" {{ old('unit_type') == 'office' ? 'selected' : '' }}>Office Space</option>
                        <option value="showroom" {{ old('unit_type') == 'showroom' ? 'selected' : '' }}>Showroom</option>
                        <option value="warehouse" {{ old('unit_type') == 'warehouse' ? 'selected' : '' }}>Warehouse / Godown</option>
                    @elseif ($structure === 'rowhouse_villa')
                        <option value="villa" {{ old('unit_type', 'villa') == 'villa' ? 'selected' : '' }}>Villa / Bungalow</option>
                        <option value="rowhouse" {{ old('unit_type') == 'rowhouse' ? 'selected' : '' }}>Row House</option>
                        <option value="tenement" {{ old('unit_type') == 'tenement' ? 'selected' : '' }}>Tenement</option>
                        <option value="plot" {{ old('unit_type') == 'plot' ? 'selected' : '' }}>Plot / Land</option>
                    @elseif ($structure === 'flat_residential')
                        <option value="flat" {{ old('unit_type', 'flat') == 'flat' ? 'selected' : '' }}>Flat / Apartment</option>
                        <option value="penthouse" {{ old('unit_type') == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                        <option value="duplex" {{ old('unit_type') == 'duplex' ? 'selected' : '' }}>Duplex Flat</option>
                    @else
                        <option value="flat" {{ old('unit_type', 'flat') == 'flat' ? 'selected' : '' }}>Flat / Apartment</option>
                        <option value="shop" {{ old('unit_type') == 'shop' ? 'selected' : '' }}>Commercial Shop</option>
                        <option value="villa" {{ old('unit_type') == 'villa' ? 'selected' : '' }}>Villa / Bungalow</option>
                        <option value="rowhouse" {{ old('unit_type') == 'rowhouse' ? 'selected' : '' }}>Row House</option>
                        <option value="office" {{ old('unit_type') == 'office' ? 'selected' : '' }}>Office Space</option>
                        <option value="showroom" {{ old('unit_type') == 'showroom' ? 'selected' : '' }}>Showroom</option>
                        <option value="penthouse" {{ old('unit_type') == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                        <option value="warehouse" {{ old('unit_type') == 'warehouse' ? 'selected' : '' }}>Warehouse / Godown</option>
                    @endif
                </select>
                @error('unit_type')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ \App\Models\Setting::label('unit', 'Flat') }} / Unit No</label>
                <input type="text" name="flat_no" class="form-control" value="{{ old('flat_no') }}" placeholder="e.g. 101, Shop 12, Villa 4">
                @error('flat_no')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6" id="floor_no_wrapper">
                <label class="form-label">Floor No <small class="text-muted">(0 for Ground/Villa)</small></label>
                <input type="number" id="floor_no" name="floor_no" class="form-control" value="{{ old('floor_no', 0) }}" min="0">
                <small class="text-muted d-none" id="floor-help">Max floors: <span></span></small>
                @error('floor_no')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 {{ ($globalBillingMethod ?? 'fixed') === 'per_sqft' ? 'd-none' : '' }}" id="maintenance_rate_wrapper">
                <label class="form-label">Maintenance Rate Category <span class="text-danger">*</span></label>
                <select name="flat_type_id" id="flat_type_id_select" class="form-select" {{ ($globalBillingMethod ?? 'fixed') === 'per_sqft' ? '' : 'required' }}>
                    <option value="">Select Maintenance Rate</option>
                    @foreach ($flatTypes as $type)
                        <option value="{{ $type->id }}" data-calc-method="{{ $type->calculation_method }}" data-category-type="{{ strtolower($type->category_type ?? 'residential') }}" data-name="{{ strtolower($type->name) }}" {{ old('flat_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ \App\Helpers\CurrencyHelper::formatCurrency($type->owner_maintenance_fee) }})
                        </option>
                    @endforeach
                </select>
                @error('flat_type_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 {{ ($globalBillingMethod ?? 'fixed') !== 'per_sqft' ? 'd-none' : '' }}" id="area_sqft_wrapper">
                <label class="form-label" id="area_sqft_label">Carpet Area (Sq. Ft.)</label>
                <input type="number" step="0.01" name="area_sqft" id="area_sqft_input" class="form-control" value="{{ old('area_sqft') }}" placeholder="e.g. 1200.50">
                <div id="area_validation_warning" class="alert alert-warning py-1 px-2 mt-1 mb-0 small d-none">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Carpet Area Required:</strong> Commercial Unit or Carpet Area Based billing (`per_sqft`) is active. Please enter valid area to prevent bill calculation from being 0.
                </div>
                @error('area_sqft')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Empty" {{ old('status', 'Empty') == 'Empty' ? 'selected' : '' }}>Empty</option>
                    <option value="Occupied" {{ old('status') == 'Occupied' ? 'selected' : '' }}>Occupied</option>
                </select>
                @error('status')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <script>
            function filterMaintenanceRateOptions() {
                const unitType = document.getElementById('unit_type_select')?.value || 'flat';
                const flatTypeSelect = document.getElementById('flat_type_id_select');
                const wrapper = document.getElementById('maintenance_rate_wrapper');
                const globalMethod = "{{ $globalBillingMethod ?? 'fixed' }}";
                if (!flatTypeSelect) return;

                const isCommercial = ['shop', 'office', 'showroom', 'warehouse'].includes(unitType);

                if (isCommercial) {
                    if (wrapper) wrapper.classList.add('d-none');
                    flatTypeSelect.removeAttribute('required');
                    flatTypeSelect.value = '';
                } else if (globalMethod !== 'per_sqft') {
                    if (wrapper) wrapper.classList.remove('d-none');
                    flatTypeSelect.setAttribute('required', 'required');
                }

                let visibleCount = 0;
                Array.from(flatTypeSelect.options).forEach(opt => {
                    if (opt.value === '') {
                        opt.style.display = '';
                        opt.hidden = false;
                        return;
                    }
                    const catType = (opt.getAttribute('data-category-type') || '').toLowerCase();
                    const name = (opt.getAttribute('data-name') || '').toLowerCase();

                    let shouldShow = false;
                    if (unitType === 'flat') {
                        shouldShow = catType === 'flat' || ((catType === 'residential' || catType === '') && (name.includes('bhk') || name.includes('flat') || (!name.includes('villa') && !name.includes('row') && !name.includes('shop') && !name.includes('office'))));
                    } else if (unitType === 'villa') {
                        shouldShow = catType === 'villa' || name.includes('villa') || name.includes('bungalow');
                    } else if (unitType === 'rowhouse') {
                        shouldShow = catType === 'rowhouse' || name.includes('row');
                    } else if (unitType === 'penthouse') {
                        shouldShow = catType === 'penthouse' || name.includes('penthouse');
                    } else if (unitType === 'shop') {
                        shouldShow = catType === 'shop' || name.includes('shop');
                    } else if (unitType === 'office') {
                        shouldShow = catType === 'office' || name.includes('office');
                    } else if (unitType === 'showroom') {
                        shouldShow = catType === 'showroom' || name.includes('showroom');
                    } else if (unitType === 'warehouse') {
                        shouldShow = catType === 'warehouse' || name.includes('warehouse') || name.includes('godown');
                    } else {
                        shouldShow = catType === unitType || name.includes(unitType);
                    }

                    if (shouldShow) {
                        opt.style.display = '';
                        opt.hidden = false;
                        visibleCount++;
                    } else {
                        opt.style.display = 'none';
                        opt.hidden = true;
                    }
                });

                if (visibleCount === 0 && !isCommercial) {
                    Array.from(flatTypeSelect.options).forEach(opt => {
                        if (opt.value === '') return;
                        const catType = (opt.getAttribute('data-category-type') || '').toLowerCase();
                        if (catType !== 'commercial' && !['shop', 'office', 'showroom', 'warehouse'].includes(catType)) {
                            opt.style.display = '';
                            opt.hidden = false;
                        }
                    });
                }

                const currentOpt = flatTypeSelect.options[flatTypeSelect.selectedIndex];
                if (currentOpt && currentOpt.hidden && currentOpt.value !== '') {
                    flatTypeSelect.value = '';
                }

                if (typeof window.checkBillingMethodCreate === 'function') {
                    window.checkBillingMethodCreate();
                }
            }

            document.getElementById('unit_type_select')?.addEventListener('change', function() {
                const isHorizontal = this.value === 'villa' || this.value === 'rowhouse';
                const floorWrapper = document.getElementById('floor_no_wrapper');
                if (floorWrapper) {
                    floorWrapper.style.opacity = isHorizontal ? '0.5' : '1';
                    const floorInput = document.getElementById('floor_no');
                    if (isHorizontal && floorInput) floorInput.value = 0;
                }
                filterMaintenanceRateOptions();
            });

            (function() {
                const globalMethod = "{{ $globalBillingMethod ?? 'fixed' }}";
                const flatTypeSelect = document.getElementById('flat_type_id_select');
                const areaInput = document.getElementById('area_sqft_input');
                const areaLabel = document.getElementById('area_sqft_label');
                const areaWarning = document.getElementById('area_validation_warning');

                window.checkBillingMethodCreate = function() {
                    let isPerSqft = globalMethod === 'per_sqft';
                    const wrapper = document.getElementById('maintenance_rate_wrapper');
                    const unitType = document.getElementById('unit_type_select')?.value || 'flat';
                    const isCommercial = ['shop', 'office', 'showroom', 'warehouse'].includes(unitType);

                    if (globalMethod === 'per_sqft' || isCommercial) {
                        if (wrapper) wrapper.classList.add('d-none');
                        if (flatTypeSelect) flatTypeSelect.removeAttribute('required');
                    } else {
                        if (wrapper) wrapper.classList.remove('d-none');
                        if (flatTypeSelect) flatTypeSelect.setAttribute('required', 'required');
                    }

                    if (flatTypeSelect && flatTypeSelect.selectedIndex > 0 && !isCommercial) {
                        const option = flatTypeSelect.options[flatTypeSelect.selectedIndex];
                        const method = option?.getAttribute('data-calc-method');
                        if (method === 'per_sqft' || method === 'hybrid') {
                            isPerSqft = true;
                        } else if (method === 'fixed' && globalMethod !== 'per_sqft') {
                            isPerSqft = false;
                        }
                    }

                    const areaWrapper = document.getElementById('area_sqft_wrapper');
                    if (isPerSqft || isCommercial) {
                        if (areaWrapper) areaWrapper.classList.remove('d-none');
                        if (areaInput) areaInput.setAttribute('required', 'required');
                        if (areaLabel) areaLabel.innerHTML = 'Carpet Area (Sq. Ft.) <span class="text-danger">*</span>';
                        if (areaWarning) areaWarning.classList.remove('d-none');
                    } else {
                        if (areaWrapper) areaWrapper.classList.add('d-none');
                        if (areaInput) areaInput.removeAttribute('required');
                        if (areaLabel) areaLabel.innerHTML = 'Carpet Area (Sq. Ft.)';
                        if (areaWarning) areaWarning.classList.add('d-none');
                    }
                };

                flatTypeSelect?.addEventListener('change', window.checkBillingMethodCreate);
                document.getElementById('unit_type_select')?.addEventListener('change', window.checkBillingMethodCreate);
                filterMaintenanceRateOptions();
            })();
        </script>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
