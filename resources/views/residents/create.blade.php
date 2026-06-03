<div class="modal-header">
    <h5 class="modal-title" id="residentModalLabel">Add Resident</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="resident-ajax-form" action="{{ route('residents.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="block_id" class="form-label">Block <span class="text-danger">*</span></label>
                <select class="form-select" id="block_id" name="block_id">
                    <option value="">Select Block</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6">
                <label for="flat_id" class="form-label">Flat No <span class="text-danger">*</span></label>
                <select class="form-select" id="flat_id" name="flat_id">
                    <option value="">Select Block First</option>
                </select>
            </div>

            <div class="col-md-12">
                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                <label for="type" class="form-label">Resident Type <span class="text-danger">*</span></label>
                <select class="form-select" id="type" name="type">
                    <option value="">Select Type</option>
                    <option value="owner">Owner</option>
                    <option value="rental">Rental</option>
                </select>
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


