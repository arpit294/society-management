<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Global Settings</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    @push('scripts')
                    <script>
                        $(document).ready(function() {
                            toastr.success("{{ session('success') }}");
                        });
                    </script>
                    @endpush
                @endif
                
                <form action="{{ route('settings.store') }}" method="POST">
                    @csrf
                    
                    <h5 class="mb-3">General Settings</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Society Name</label>
                            <input type="text" name="society_name" class="form-control" value="{{ $settings['society_name'] ?? 'My Society' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Society Address</label>
                            <input type="text" name="society_address" class="form-control" value="{{ $settings['society_address'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}">
                        </div>
                    </div>

                    <h5 class="mb-3">Billing & Finances</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '₹' }}" placeholder="e.g. ₹ or $">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Invoice Footer Notes</label>
                            <textarea name="invoice_notes" class="form-control" rows="3">{{ $settings['invoice_notes'] ?? 'Thank you for your timely payment!' }}</textarea>
                            <div class="form-text">These notes will appear at the bottom of generated PDF invoices.</div>
                        </div>
                    </div>

                    <h5 class="mb-3">Late Penalty Percentages</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Monthly Penalty (%)</label>
                            <input type="number" step="0.01" name="penalty_monthly_percent" class="form-control" value="{{ $settings['penalty_monthly_percent'] ?? '5' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quarterly Penalty (%)</label>
                            <input type="number" step="0.01" name="penalty_quarterly_percent" class="form-control" value="{{ $settings['penalty_quarterly_percent'] ?? '10' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Yearly Penalty (%)</label>
                            <input type="number" step="0.01" name="penalty_yearly_percent" class="form-control" value="{{ $settings['penalty_yearly_percent'] ?? '15' }}">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-user-page>
