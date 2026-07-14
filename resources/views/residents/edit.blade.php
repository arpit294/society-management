<form id="resident-ajax-form" method="POST" action="{{ route('residents.update', $resident->id) }}">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit {{ \App\Models\Setting::label('resident', 'Resident') }}</h5>
            <p class="text-muted mb-0 small">Update the {{ strtolower(\App\Models\Setting::label('resident', 'resident')) }} record.</p>
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
                        <option value="{{ $block->id }}" {{ $resident->block_id == $block->id ? 'selected' : '' }}>{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ \App\Models\Setting::label('unit', 'Flat') }} No</label>
                <select name="flat_id" class="form-control">
                    <option value="">Select {{ \App\Models\Setting::label('unit', 'Flat') }}</option>
                    @foreach ($flats as $flat)
                        <option value="{{ $flat->id }}" data-is-commercial="{{ $flat->is_commercial ? '1' : '0' }}" {{ $resident->flat_id == $flat->id ? 'selected' : '' }}>{{ $flat->flat_no }} {{ $flat->unit_type && strtolower($flat->unit_type) !== 'flat' ? '(' . strtoupper($flat->unit_type) . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ \App\Models\Setting::label('resident', 'Resident') }} Type <span class="text-danger">*</span></label>
                <select name="type" id="resident-type-select" class="form-select js-resident-type-toggle" data-owner-section="#owner-details-section-edit">
                    <option value="">Select Type</option>
                    <option value="owner" {{ $resident->type == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="rental" {{ $resident->type == 'rental' ? 'selected' : '' }}>Rental</option>
                </select>
            </div>

            <div id="owner-details-section-edit" class="col-md-12 {{ $resident->type == 'rental' ? '' : 'd-none' }}">
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
            <div class="col-md-12 mt-3 {{ $resident->flat && $resident->flat->is_commercial ? '' : 'd-none' }}" id="commercial-profile-section-edit" style="{{ $resident->flat && $resident->flat->is_commercial ? '' : 'display: none;' }}">
                <div class="card border-0 bg-body-tertiary rounded-3 p-3 shadow-sm">
                    <h6 class="mb-3 fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Occupant & Commercial Profile</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Occupant Category</label>
                            <select class="form-select form-select-sm" name="occupant_category" id="occupant_category_edit">
                                <option value="individual" {{ old('occupant_category', $resident->occupant_category) == 'individual' ? 'selected' : '' }}>Individual / Residential Family</option>
                                <option value="business" {{ old('occupant_category', $resident->occupant_category) == 'business' ? 'selected' : '' }}>Commercial Business / Corporate</option>
                                <option value="shopkeeper" {{ old('occupant_category', $resident->occupant_category) == 'shopkeeper' ? 'selected' : '' }}>Retail Shopkeeper / Merchant</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Business Name (business_name)</label>
                            <input type="text" class="form-control form-control-sm" name="business_name" value="{{ old('business_name', $resident->business_name) }}" placeholder="e.g. Apex Traders Pvt Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Attn: Contact Person (contact_person)</label>
                            <input type="text" class="form-control form-control-sm" name="contact_person" value="{{ old('contact_person', $resident->contact_person) }}" placeholder="e.g. Rajesh Kumar (Manager)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Occupant GSTIN (gst_number)</label>
                            <input type="text" class="form-control form-control-sm text-uppercase" name="gst_number" value="{{ old('gst_number', $resident->gst_number) }}" placeholder="e.g. 24AAACC1206D1ZM">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Trade / Shop License No.</label>
                            <input type="text" class="form-control form-control-sm" name="trade_license_no" value="{{ old('trade_license_no', $resident->trade_license_no) }}" placeholder="Optional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">User</label>
                <select name="user_id" id="resident-user-select" class="form-control">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}" {{ $resident->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->resident_details }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Move In Date</label>
                <input type="date" name="move_in_date" class="form-control" value="{{ $resident->move_in_date ? \Carbon\Carbon::parse($resident->move_in_date)->format('Y-m-d') : '' }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Move Out Date</label>
                <input type="date" name="move_out_date" class="form-control" value="{{ $resident->move_out_date ? \Carbon\Carbon::parse($resident->move_out_date)->format('Y-m-d') : '' }}">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
