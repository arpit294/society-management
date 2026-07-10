<form id="flat-ajax-form" method="POST" action="{{ route('flats.store') }}">
    @csrf

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Add Flat</h5>
            <p class="text-muted mb-0 small">Create a new flat record.</p>
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
                <select name="unit_type" id="unit_type_select" class="form-select">
                    <option value="flat" {{ old('unit_type') == 'flat' ? 'selected' : '' }}>Flat / Apartment</option>
                    <option value="shop" {{ old('unit_type') == 'shop' ? 'selected' : '' }}>Commercial Shop</option>
                    <option value="office" {{ old('unit_type') == 'office' ? 'selected' : '' }}>Commercial Office</option>
                    <option value="villa" {{ old('unit_type') == 'villa' ? 'selected' : '' }}>Villa / Bungalow</option>
                    <option value="rowhouse" {{ old('unit_type') == 'rowhouse' ? 'selected' : '' }}>Row House</option>
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

            <div class="col-md-6">
                <label class="form-label">Maintenance Rate Category <span class="text-danger">*</span></label>
                <select name="flat_type_id" class="form-select" required>
                    <option value="">Select Maintenance Rate</option>
                    @foreach ($flatTypes as $type)
                        <option value="{{ $type->id }}" {{ old('flat_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ \App\Helpers\CurrencyHelper::formatCurrency($type->owner_maintenance_fee) }})
                        </option>
                    @endforeach
                </select>
                @error('flat_type_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Carpet Area (Sq. Ft.)</label>
                <input type="number" step="0.01" name="area_sqft" class="form-control" value="{{ old('area_sqft') }}" placeholder="e.g. 1200.50">
                @error('area_sqft')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Electricity Meter No</label>
                <input type="text" name="electricity_meter_no" class="form-control" value="{{ old('electricity_meter_no') }}" placeholder="Optional">
                @error('electricity_meter_no')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Water Meter No</label>
                <input type="text" name="water_meter_no" class="form-control" value="{{ old('water_meter_no') }}" placeholder="Optional">
                @error('water_meter_no')
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

            <div class="col-md-6 d-flex align-items-center mt-4">
                <div class="form-check form-switch p-2 px-3 bg-body-tertiary rounded border w-100 d-flex align-items-center justify-content-between">
                    <label class="form-check-label small fw-semibold mb-0" for="has_commercial_license">Has Commercial License / Shop</label>
                    <input class="form-check-input m-0" type="checkbox" id="has_commercial_license" name="has_commercial_license" value="1" {{ old('has_commercial_license') ? 'checked' : '' }}>
                </div>
            </div>
        </div>
        <script>
            document.getElementById('unit_type_select')?.addEventListener('change', function() {
                const isHorizontal = this.value === 'villa' || this.value === 'rowhouse';
                const floorWrapper = document.getElementById('floor_no_wrapper');
                if (floorWrapper) {
                    floorWrapper.style.opacity = isHorizontal ? '0.5' : '1';
                    const floorInput = document.getElementById('floor_no');
                    if (isHorizontal && floorInput) floorInput.value = 0;
                }
            });
        </script>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
