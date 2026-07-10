<form id="block-ajax-form" method="POST" action="{{ route('blocks.store') }}">
    @csrf

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Add {{ \App\Models\Setting::label('block', 'Block') }}</h5>
            <p class="text-muted mb-0 small">Fill in the details and save the {{ strtolower(\App\Models\Setting::label('block', 'block')) }}.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ \App\Models\Setting::label('block', 'Block/Wing') }} Name</label>
                <input type="text" name="block_name" class="form-control" value="{{ old('block_name') }}" placeholder="e.g. A, Tower 1, Sector 4">
                @error('block_name')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Structure Type</label>
                <select name="block_type" class="form-select">
                    <option value="residential_tower" {{ old('block_type') == 'residential_tower' ? 'selected' : '' }}>Residential Tower / Wing</option>
                    <option value="commercial_wing" {{ old('block_type') == 'commercial_wing' ? 'selected' : '' }}>Commercial Wing / Arcade</option>
                    <option value="rowhouse_sector" {{ old('block_type') == 'rowhouse_sector' ? 'selected' : '' }}>Row Houses / Villa Sector (Horizontal)</option>
                    <option value="mixed_phase" {{ old('block_type') == 'mixed_phase' ? 'selected' : '' }}>Mixed-Use Phase</option>
                </select>
                @error('block_type')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Label Type</label>
                <select name="label_type" class="form-select">
                    <option value="Wing" {{ old('label_type') == 'Wing' ? 'selected' : '' }}>Wing</option>
                    <option value="Tower" {{ old('label_type') == 'Tower' ? 'selected' : '' }}>Tower</option>
                    <option value="Sector" {{ old('label_type') == 'Sector' ? 'selected' : '' }}>Sector</option>
                    <option value="Phase" {{ old('label_type') == 'Phase' ? 'selected' : '' }}>Phase</option>
                    <option value="Lane" {{ old('label_type') == 'Lane' ? 'selected' : '' }}>Lane</option>
                    <option value="Block" {{ old('label_type') == 'Block' ? 'selected' : '' }}>Block</option>
                </select>
                @error('label_type')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Total Floors <small class="text-muted">(0 for horizontal)</small></label>
                <input type="number" name="total_floor" class="form-control" value="{{ old('total_floor', 0) }}"
                    min="0">
                @error('total_floor')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Total {{ \App\Models\Setting::label('unit', 'Flat') }}s / Units</label>
                <input type="number" name="total_flats" class="form-control" value="{{ old('total_flats') }}"
                    min="0">
                @error('total_flats')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
