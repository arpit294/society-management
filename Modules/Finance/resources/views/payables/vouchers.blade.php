<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Payment Vouchers & Approvals</h2>
            <p class="text-muted small mb-0">Multi-tier approval workflow for society disbursements and ledger integration</p>
        </div>
        <div>
            <a href="{{ route('finance.vendor-bills.index') }}" class="btn btn-primary btn-sm px-3">
                <i class="fa-solid fa-plus me-1"></i> Pay a Vendor Bill
            </a>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'table table-hover align-middle w-100']) !!}
            </div>
        </div>
    </div>
</div>

@push('scripts')
{!! $dataTable->scripts() !!}
<script>
$(document).ready(function() {
    // Approve Voucher AJAX
    $(document).on('click', '.btn-approve-voucher', function() {
        var id = $(this).data('id');
        if (!confirm('Approve this payment voucher?')) return;

        $.ajax({
            url: '{{ url("finance/vouchers") }}/' + id + '/approve',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                window.LaravelDataTables["finance-vouchers-table"].ajax.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error approving voucher');
            }
        });
    });

    // Disburse Voucher AJAX
    $(document).on('click', '.btn-disburse-voucher', function() {
        var id = $(this).data('id');
        var num = $(this).data('number');
        var amount = $(this).data('amount');

        if (!confirm('Disburse payment of ₹' + amount + ' for voucher ' + num + '? This will immediately deduct the bank balance and post to General Ledger.')) return;

        $.ajax({
            url: '{{ url("finance/vouchers") }}/' + id + '/disburse',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                window.LaravelDataTables["finance-vouchers-table"].ajax.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error disbursing voucher');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
