<form action="{{ route('flat-types.store') }}" method="POST" id="flat-type-ajax-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add Flat Type</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. 2BHK Flat, Luxury Villa, Commercial Shop">
            </div>

            <div class="col-md-4 mb-3">
                <label for="category_type" class="form-label">Property Category</label>
                <select class="form-select" id="category_type" name="category_type">
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="owner_maintenance_fee" class="form-label">Owner Fee (Fixed) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                    <input type="number" step="0.01" class="form-control" id="owner_maintenance_fee" name="owner_maintenance_fee" required placeholder="0.00">
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="rental_maintenance_fee" class="form-label">Rental Fee (Fixed) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                    <input type="number" step="0.01" class="form-control" id="rental_maintenance_fee" name="rental_maintenance_fee" required placeholder="0.00">
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label for="rate_per_sqft" class="form-label">Rate / Sq.Ft. (₹)</label>
                <div class="input-group">
                    <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                    <input type="number" step="0.01" min="0" class="form-control" id="rate_per_sqft" name="rate_per_sqft" placeholder="0.00">
                </div>
            </div>
            
            <div class="col-md-12 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Flat Type</button>
    </div>
</form>
