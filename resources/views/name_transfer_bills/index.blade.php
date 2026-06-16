<x-user-page>

    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Name Transfer Bills</h4>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="status-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="status-form" method="POST">
                    @csrf
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold">Update Status</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold text-uppercase">Status</label>
                            <select name="status" id="bill-status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3" id="payment-method-container" style="display: none;">
                            <label class="form-label text-muted small fw-semibold text-uppercase">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="upi">UPI</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-status">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        
        <script>
            $(document).ready(function() {
                let currentStatusUrl = '';

                $(document).on('click', '.btn-status', function() {
                    currentStatusUrl = $(this).data('url');
                    let currentStatus = $(this).data('status');
                    $('#bill-status').val(currentStatus).trigger('change');
                    $('#status-modal').modal('show');
                });

                $('#bill-status').on('change', function() {
                    if ($(this).val() === 'paid') {
                        $('#payment-method-container').show();
                    } else {
                        $('#payment-method-container').hide();
                    }
                });

                $('#status-form').on('submit', function(e) {
                    e.preventDefault();
                    let btn = $('#btn-save-status');
                    let originalText = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop('disabled', true);
                    
                    $.ajax({
                        url: currentStatusUrl,
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $('#status-modal').modal('hide');
                                window.LaravelDataTables['nametransferbills-table'].ajax.reload(null, false);
                                showToast('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('danger', 'Error updating status');
                        },
                        complete: function() {
                            btn.html(originalText).prop('disabled', false);
                        }
                    });
                });

                $(document).on('click', '.btn-delete-bill', function() {
                    let url = $(this).data('url');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                }
                            }).catch(error => {
                                let msg = error.responseJSON && error.responseJSON.message ? error.responseJSON.message : 'Error deleting bill';
                                Swal.showValidationMessage(msg);
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.LaravelDataTables['nametransferbills-table'].ajax.reload(null, false);
                            Swal.fire(
                                'Deleted!',
                                'The bill has been deleted.',
                                'success'
                            );
                        }
                    });
                });

                $(document).on('click', '.btn-approve', function() {
                    let url = $(this).data('url');
                    
                    Swal.fire({
                        title: 'Approve Transfer?',
                        text: "This will update the resident records and finalize the transfer.",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, approve it!',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return $.ajax({
                                url: url,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                }
                            }).catch(error => {
                                let msg = error.responseJSON && error.responseJSON.message ? error.responseJSON.message : 'Error approving transfer';
                                Swal.showValidationMessage(msg);
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.LaravelDataTables['nametransferbills-table'].ajax.reload(null, false);
                            Swal.fire(
                                'Approved!',
                                'The transfer has been approved successfully.',
                                'success'
                            );
                        }
                    });
                });
            });
        </script>
    @endpush
</x-user-page>
