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
            <div class="col-md-12">
                <label class="form-label">{{ \App\Models\Setting::label('block', 'Block/Wing') }} Name</label>
                <input type="text" name="block_name" class="form-control" value="{{ old('block_name') }}" placeholder="e.g. {{ \App\Models\Setting::get('society_property_type') === 'rowhouse_villa' ? 'Phase 1, North Sector, Row B' : (\App\Models\Setting::get('society_property_type') === 'commercial_complex' ? 'Wing A, Trade Center, Plaza 1' : 'A, Tower 1, Block B') }}">
                @error('block_name')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Total Floors / Levels <small class="text-muted">({{ \App\Models\Setting::get('society_property_type') === 'rowhouse_villa' ? '0 for horizontal plots/villas' : '0 for ground structure' }})</small></label>
                <input type="number" name="total_floor" class="form-control" value="{{ old('total_floor', 0) }}" min="0">
                @error('total_floor')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Total {{ \App\Models\Setting::label('unit_plural', 'Flats') }} Capacity</label>
                <input type="number" name="total_flats" class="form-control" value="{{ old('total_flats') }}" min="0" placeholder="Number of {{ strtolower(\App\Models\Setting::label('unit_plural', 'flats')) }}">
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
