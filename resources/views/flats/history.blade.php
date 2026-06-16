<div class="modal-header">
    <div>
        <h5 class="modal-title mb-1"><i class="fa-solid fa-clock-rotate-left me-2 text-info"></i> Flat History</h5>
        <p class="text-muted mb-0 small">Resident history for Flat {{ $flat->flat_no }} (Block {{ $flat->block->block_name ?? '-' }})</p>
    </div>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-0">
    @if($history->isEmpty())
        <div class="p-5 text-center text-muted">
            <i class="fa-solid fa-users-slash fs-1 mb-3"></i>
            <p>No residents found for this flat.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Resident Name</th>
                        <th>Type</th>
                        <th>Move In Date</th>
                        <th>Move Out Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $resident)
                        @php
                            $isCurrent = is_null($resident->move_out_date) || \Carbon\Carbon::parse($resident->move_out_date)->isFuture();
                        @endphp
                        <tr>
                            <td class="ps-4 fw-semibold">
                                {{ $resident->user ? $resident->user->name : 'Unknown User' }}
                                @if($resident->user && $resident->user->phone)
                                    <br><small class="text-muted fw-normal">{{ $resident->user->phone }}</small>
                                @endif
                            </td>
                            <td>
                                @if($resident->type === 'owner')
                                    <span class="badge bg-primary">Owner</span>
                                @else
                                    <span class="badge bg-info text-dark">Rental</span>
                                @endif
                            </td>
                            <td>{{ $resident->move_in_date ? \Carbon\Carbon::parse($resident->move_in_date)->format('d M, Y') : '-' }}</td>
                            <td>{{ $resident->move_out_date ? \Carbon\Carbon::parse($resident->move_out_date)->format('d M, Y') : '-' }}</td>
                            <td>
                                @if($isCurrent)
                                    <span class="badge bg-success">Current</span>
                                @else
                                    <span class="badge bg-secondary">Past</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
</div>
