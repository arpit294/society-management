<x-user-page>
    @push('styles')
        <style>
            .flat-card {
                border: 1px solid #2d3348;
                background: #1f2937;
                border-radius: 0.5rem;
            }

            .flat-card .card-body {
                padding: 1rem;
            }

            .flat-title {
                color: #fff;
                font-weight: 600;
            }

            .btn-flat {
                background: #6366f1;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 0.4rem;
                font-weight: 500;
            }

            .btn-flat:hover {
                background: #4f46e5;
            }

            .flat-table {
                color: #fff;
                margin-bottom: 0;
            }

            .flat-table thead th {
                border-bottom: 1px solid #374151;
                color: #cbd5e1;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .flat-table td {
                border-bottom: 1px solid #2d3348;
                vertical-align: middle;
            }

            .status-badge {
                padding: 0.35rem 0.7rem;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .status-occupied {
                background: rgba(34, 197, 94, 0.15);
                color: #22c55e;
            }

            .status-vacant {
                background: rgba(239, 68, 68, 0.15);
                color: #ef4444;
            }

            .btn-action {
                padding: 0.3rem 0.7rem;
                font-size: 0.8rem;
                border-radius: 0.35rem;
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="flat-title mb-0">Flat Management</h4>

        <button class="btn btn-primary btn-flat">
            Add Flat
        </button>
    </div>

    <div class="card flat-card">
        <div class="card-body table-responsive">

            <table class="table flat-table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Flat No</th>
                        <th>Block</th>
                        <th>Owner</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>


            </table>

        </div>
    </div>
</x-user-page>
