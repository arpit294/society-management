<form id="complain-ajax-form" method="POST" action="{{ route('complains.update', $complain->id) }}">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit Complaint</h5>
            <p class="text-muted mb-0 small">Update the details of the complaint.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="complain-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $complain->subject) }}">
                @error('subject')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">Select Category</option>
                    @foreach (['Maintenance Issues', 'Security Issues', 'Cleanliness & Housekeeping', 'Common Facilities', 'other'] as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $complain->category) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $complain->user_id) == $user->id)>{{ $user->resident_details }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $complain->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="pending" {{ $complain->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in-progress" {{ $complain->status === 'in-progress' ? 'selected' : '' }}>In-Progress</option>
                    <option value="resolved" {{ $complain->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
                @error('status')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Resolution Notes</label>
                <textarea name="resolution_notes" class="form-control" rows="3" placeholder="Notes for the resident...">{{ old('resolution_notes', $complain->resolution_notes) }}</textarea>
                @error('resolution_notes')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
