<form id="resident-ajax-form" method="POST" action="{{ route('residents.update', $resident->id) }}">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit Resident</h5>
            <p class="text-muted mb-0 small">Update the resident record.</p>
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
                        <option value="{{ $block->id }}" {{ $resident->block_id == $block->id ? 'selected' : '' }}>{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Flat No</label>
                <select name="flat_id" class="form-control">
                    <option value="">Select Flat</option>
                    @foreach ($flats as $flat)
                        <option value="{{ $flat->id }}" {{ $resident->flat_id == $flat->id ? 'selected' : '' }}>{{ $flat->flat_no }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Resident Type</label>
                <select name="type" id="resident-type-select" class="form-select">
                    <option value="">Select Type</option>
                    <option value="owner" {{ $resident->type == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="rental" {{ $resident->type == 'rental' ? 'selected' : '' }}>Rental</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">User</label>
                <select name="user_id" id="resident-user-select" class="form-control">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}" {{ $resident->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Move In Date</label>
<<<<<<< HEAD
                <input type="date" name="move_in_date" class="form-control" value="{{ $resident->move_in_date ? \Carbon\Carbon::parse($resident->move_in_date)->format('Y-m-d') : '' }}">
=======
                <input type="date" name="move_in_date" class="form-control" value="{{ $resident->move_in_date ? $resident->move_in_date->format('Y-m-d') : '' }}">
>>>>>>> 5b0068f1ee95b41d004294bc1026be3c22013584
            </div>

            <div class="col-md-6">
                <label class="form-label">Move Out Date</label>
<<<<<<< HEAD
                <input type="date" name="move_out_date" class="form-control" value="{{ $resident->move_out_date ? \Carbon\Carbon::parse($resident->move_out_date)->format('Y-m-d') : '' }}">
=======
                <input type="date" name="move_out_date" class="form-control" value="{{ $resident->move_out_date ? $resident->move_out_date->format('Y-m-d') : '' }}">
>>>>>>> 5b0068f1ee95b41d004294bc1026be3c22013584
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
