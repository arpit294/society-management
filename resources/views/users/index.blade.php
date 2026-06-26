<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">User Management</h4>
        @can('user_create')
            <button type="button" class="btn btn-primary" id="btn-add-user" data-url="{{ route('users.create') }}"
                data-title="Add User">Add User</button>
        @endcan
    </div>

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="users-filter-role">Filter by Role</label>
                <select id="users-filter-role" class="form-select" style="max-width: 320px;">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">
                            {{ config('roles.labels.' . $role, ucfirst(str_replace('_', ' ', $role))) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="users-filter-status">Filter by Status</label>
                <select id="users-filter-status" class="form-select" style="max-width: 320px;">
                    <option value="">All Status</option>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col d-none" id="users-filter-reset-col" style="min-width: 200px;">
                <button type="button" id="users-filter-reset" class="btn btn-outline-secondary w-100">
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

    <div class="modal fade" id="user-modal" tabindex="-1" aria-labelledby="user-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="user-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
