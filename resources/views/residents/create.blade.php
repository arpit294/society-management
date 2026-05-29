<form id="resident-ajax-form" method="POST" action="{{ route('residents.store') }}">
    @csrf

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Add Resident</h5>
            <p class="text-muted mb-0 small">Create a new resident record.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Block</label>
                <select name="block_id" class="form-control">
                    <option value="">Select Block</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Flat No</label>
                <select name="flat_id" class="form-control">
                    <option value="">Select Flat</option>
                    @foreach ($flats as $flat)
                        <option value="{{ $flat->id }}">{{ $flat->flat_no }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Resident Type</label>
                <select name="type" id="resident-type-select" class="form-select">
                    <option value="">Select Type</option>
                    <option value="owner">Owner</option>
                    <option value="rental">Rental</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">User</label>
                <select name="user_id" id="resident-user-select" class="form-control">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}">
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Move In Date</label>
                <input type="date" name="move_in_date" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Move Out Date</label>
                <input type="date" name="move_out_date" class="form-control">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

