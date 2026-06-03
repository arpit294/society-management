<x-user-page>
    @push('styles')
        <style>
            .users-toast-container {
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 1085;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                width: min(360px, calc(100vw - 2rem));
                pointer-events: none;
            }

            .users-toast {
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                overflow: hidden;
                padding: 0.95rem 1rem;
                color: #172033;
                background: #ffffff;
                border: 1px solid #e6eaf0;
                border-left: 5px solid #64748b;
                border-radius: 0.65rem;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
                opacity: 0;
                pointer-events: auto;
                transform: translateX(1rem);
                transition: opacity 0.22s ease, transform 0.22s ease;
            }

            .users-toast.show {
                opacity: 1;
                transform: translateX(0);
            }

            .users-toast.success {
                border-left-color: #16a34a;
            }

            .users-toast.danger,
            .users-toast.error {
                border-left-color: #dc2626;
            }

            .users-toast-icon {
                display: inline-flex;
                flex: 0 0 2rem;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                color: #ffffff;
                font-size: 0.85rem;
                font-weight: 700;
                line-height: 1;
                background: #64748b;
                border-radius: 50%;
            }

            .users-toast.success .users-toast-icon {
                background: #16a34a;
            }

            .users-toast.danger .users-toast-icon,
            .users-toast.error .users-toast-icon {
                background: #dc2626;
            }

            .users-toast-title {
                margin-bottom: 0.15rem;
                color: #0f172a;
                font-size: 0.95rem;
                font-weight: 700;
            }

            .users-toast-message {
                margin: 0;
                color: #475569;
                font-size: 0.875rem;
                line-height: 1.35;
            }

            @media (max-width: 575.98px) {
                .users-toast-container {
                    top: 0.75rem;
                    right: 0.75rem;
                    left: 0.75rem;
                    width: auto;
                }
            }
        </style>
    @endpush

    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Resident Management</h4>

        <button type="button" class="btn btn-primary" id="btn-add-resident" data-url="{{ route('residents.create') }}"
            data-title="Add Resident">Add Resident</button>
    </div>

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="residents-filter-block">Filter by Block</label>
                <select id="residents-filter-block" class="form-select" style="max-width: 320px;">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col d-none" id="residents-filter-reset-col" style="min-width: 200px;">
                <button type="button" id="residents-filter-reset" class="btn btn-outline-secondary w-100">
                    Reset filters
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="resident-modal" tabindex="-1" aria-labelledby="resident-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="resident-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle dynamic dropdown filtering for Resident Type
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'resident-type-select') {
                        const type = e.target.value;
                        const userSelect = document.getElementById('resident-user-select');
                        if (!userSelect) return;
                        
                        const options = userSelect.querySelectorAll('option');
                        
                        // Reset user selection when type changes
                        userSelect.value = '';

                        options.forEach(option => {
                            const role = option.getAttribute('data-role');
                            if (!role) {
                                option.style.display = '';
                                option.hidden = false;
                                option.disabled = false;
                                return;
                            }

                            if (type) {
                                if (role.toLowerCase() === type) {
                                    option.style.display = '';
                                    option.hidden = false;
                                    option.disabled = false;
                                } else {
                                    option.style.display = 'none';
                                    option.hidden = true;
                                    option.disabled = true;
                                }
                            } else {
                                option.style.display = '';
                                option.hidden = false;
                                option.disabled = false;
                            }
                        });
                    }
                });
            });
        </script>
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
