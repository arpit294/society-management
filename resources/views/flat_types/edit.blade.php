<form action="{{ route('flat-types.update', $flatType->id) }}" method="POST" id="flat-type-ajax-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit {{ \App\Models\Setting::label('unit_type', 'Flat Type') }}</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $flatType->name }}" required placeholder="e.g. 2BHK Flat, Luxury Villa, Commercial Shop">
            </div>

            <div class="col-md-4 mb-3">
                <label for="category_type" class="form-label">Unit Type</label>
                <select class="form-select" id="category_type" name="category_type">
                    <option value="flat" {{ old('category_type', $flatType->category_type ?? 'flat') == 'flat' || old('category_type', $flatType->category_type ?? '') == 'residential' ? 'selected' : '' }}>Flat / Apartment</option>
                    <option value="shop" {{ old('category_type', $flatType->category_type ?? '') == 'shop' || old('category_type', $flatType->category_type ?? '') == 'commercial' ? 'selected' : '' }}>Commercial Shop</option>
                    <option value="villa" {{ old('category_type', $flatType->category_type ?? '') == 'villa' ? 'selected' : '' }}>Villa / Bungalow</option>
                    <option value="rowhouse" {{ old('category_type', $flatType->category_type ?? '') == 'rowhouse' ? 'selected' : '' }}>Row House</option>
                    <option value="office" {{ old('category_type', $flatType->category_type ?? '') == 'office' ? 'selected' : '' }}>Office Space</option>
                    <option value="showroom" {{ old('category_type', $flatType->category_type ?? '') == 'showroom' ? 'selected' : '' }}>Showroom</option>
                    <option value="penthouse" {{ old('category_type', $flatType->category_type ?? '') == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                    <option value="warehouse" {{ old('category_type', $flatType->category_type ?? '') == 'warehouse' ? 'selected' : '' }}>Warehouse / Godown</option>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="owner_maintenance_fee" class="form-label">Owner Fee (Fixed) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                    <input type="number" step="0.01" class="form-control" id="owner_maintenance_fee" name="owner_maintenance_fee" value="{{ $flatType->owner_maintenance_fee }}" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="rental_maintenance_fee" class="form-label">Rental Fee (Fixed) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                    <input type="number" step="0.01" class="form-control" id="rental_maintenance_fee" name="rental_maintenance_fee" value="{{ $flatType->rental_maintenance_fee }}" required>
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ $flatType->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $flatType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update {{ \App\Models\Setting::label('unit_type', 'Flat Type') }}</button>
    </div>
</form>
