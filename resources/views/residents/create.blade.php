<div class="modal-header">
    <h5 class="modal-title" id="residentModalLabel">Add {{ \App\Models\Setting::label('resident', 'Resident') }}</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="resident-ajax-form" action="{{ route('residents.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="block_id" class="form-label">{{ \App\Models\Setting::label('block', 'Block') }} <span class="text-danger">*</span></label>
                <select class="form-select" id="block_id" name="block_id">
                    <option value="">Select {{ \App\Models\Setting::label('block', 'Block') }}</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6">
                <label for="flat_id" class="form-label">{{ \App\Models\Setting::label('unit', 'Flat') }} No <span class="text-danger">*</span></label>
                <select class="form-select" id="flat_id" name="flat_id">
                    <option value="">Select {{ \App\Models\Setting::label('block', 'Block') }} First</option>
                </select>
            </div>

            <div class="col-md-12">
                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->resident_details }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                <label for="type" class="form-label">Resident Type <span class="text-danger">*</span></label>
                <select class="form-select js-resident-type-toggle" id="type" name="type" data-owner-section="#owner-details-section">
                    <option value="">Select Type</option>
                    <option value="owner">Owner</option>
                    <option value="rental">Rental</option>
                </select>
            </div>

            <div id="owner-details-section" class="col-md-12 d-none">
                <div class="card bg-light border-1">
                    <div class="card-body p-3">
                        <label for="owner_user_id" class="form-label mb-1">Owner of this {{ \App\Models\Setting::label('unit', 'Flat') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="owner_user_id" name="owner_user_id">
                            <option value="">Select Owner</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->resident_details }}</option>
                            @endforeach
                        </select>
                        <div class="form-text mb-0 mt-1"><i class="fas fa-info-circle"></i> If this unit is rented out, you must assign an Owner. (If the unit already has an owner, you can skip this).</div>
                    </div>
                </div>
            </div>

            <!-- Commercial / Business Occupant Profile -->
            <div class="col-md-12 mt-3">
                <div class="card border-0 bg-body-tertiary rounded-3 p-3 shadow-sm">
                    <h6 class="mb-3 fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Occupant & Commercial Profile</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Occupant Category</label>
                            <select class="form-select form-select-sm" name="occupant_category" id="occupant_category_create">
                                <option value="individual" {{ old('occupant_category') == 'individual' ? 'selected' : '' }}>Individual / Residential Family</option>
                                <option value="business" {{ old('occupant_category') == 'business' ? 'selected' : '' }}>Commercial Business / Corporate</option>
                                <option value="shopkeeper" {{ old('occupant_category') == 'shopkeeper' ? 'selected' : '' }}>Retail Shopkeeper / Merchant</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company / Business Name</label>
                            <input type="text" class="form-control form-control-sm" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Apex Traders Pvt Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">GSTIN (for Tax Invoices)</label>
                            <input type="text" class="form-control form-control-sm text-uppercase" name="gstin" value="{{ old('gstin') }}" placeholder="e.g. 24AAACC1206D1ZM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Trade / Shop License No.</label>
                            <input type="text" class="form-control form-control-sm" name="trade_license_no" value="{{ old('trade_license_no') }}" placeholder="Optional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <label for="move_in_date" class="form-label">Move In Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="move_in_date" name="move_in_date">
            </div>

            <div class="col-md-6">
                <label for="move_out_date" class="form-label">Move Out Date</label>
                <input type="date" class="form-control" id="move_out_date" name="move_out_date">
                <div class="form-text">Leave blank if currently residing.</div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
</form>

