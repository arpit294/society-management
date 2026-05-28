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
        <h4 class="mb-0">Block Management</h4>

        <button type="button" class="btn btn-primary" id="btn-add-block" data-url="{{ route('blocks.create') }}"
            data-title="Add Block">Add Block</button>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="block-modal" tabindex="-1" aria-labelledby="block-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="block-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
