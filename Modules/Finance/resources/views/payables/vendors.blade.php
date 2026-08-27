<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Vendor & Contractor Directory</h2>
            <p class="text-muted small mb-0">Manage service providers, security agencies, AMC contractors, and supplier profiles</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddVendor">
            <i class="fa-solid fa-plus me-1"></i> Register Vendor
        </button>
    </div>

    <!-- Vendor Cards / Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vendor Name</th>
                            <th>Service Category</th>
                            <th>Contact Person</th>
                            <th>Phone & Email</th>
                            <th>GSTIN / PAN</th>
                            <th>Bank Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $idx => $v)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong class="text-dark">{{ $v->name }}</strong></td>
                            <td><span class="badge bg-secondary">{{ $v->service_type }}</span></td>
                            <td>{{ $v->contact_person ?? 'N/A' }}</td>
                            <td>
                                {{ $v->phone ?? '-' }}<br>
                                <small class="text-muted">{{ $v->email ?? '-' }}</small>
                            </td>
                            <td>
                                <small>GST: {{ $v->gstin ?? 'N/A' }}<br>PAN: {{ $v->pan_number ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <small>{{ $v->bank_name ?? '-' }}<br>{{ $v->bank_account_no ?? '-' }} ({{ $v->bank_ifsc ?? '-' }})</small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-vendor" data-url="{{ route('finance.vendors.destroy', $v->id) }}" title="Delete Vendor">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No vendors registered yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Vendor -->
<div class="modal fade" id="modalAddVendor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddVendor" action="{{ route('finance.vendors.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Register Vendor / Contractor</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company / Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Apex Security Solutions">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                        <input type="text" name="service_type" class="form-control" required placeholder="e.g. Security, Lift AMC, Housekeeping">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Manager Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="10-digit number">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="vendor@example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" placeholder="GST Number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" placeholder="PAN">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bank Name & Account No.</label>
                        <input type="text" name="bank_name" class="form-control mb-2" placeholder="Bank Name (e.g. HDFC Bank)">
                        <input type="text" name="bank_account_no" class="form-control" placeholder="Account Number">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Register Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddVendor').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error registering vendor');
            }
        });
    });

    $(document).on('click', '.btn-delete-vendor', function() {
        if (!confirm('Are you sure you want to delete this vendor?')) return;
        $.ajax({
            url: $(this).data('url'),
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error deleting vendor');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
