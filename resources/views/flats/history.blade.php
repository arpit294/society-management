<div class="modal-header">
    <div>
        <h5 class="modal-title mb-1"><i class="fa-solid fa-clock-rotate-left me-2 text-info"></i> {{ \App\Models\Setting::label('unit', 'Flat') }} History</h5>
        <p class="text-muted mb-0 small">{{ \App\Models\Setting::label('resident', 'Resident') }} history for {{ \App\Models\Setting::label('unit', 'Flat') }} {{ $flat->flat_no }} ({{ \App\Models\Setting::label('block', 'Block') }} {{ $flat->block->block_name ?? '-' }})</p>
    </div>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-0">
    @if($history->isEmpty())
        <div class="p-5 text-center text-muted">
            <i class="fa-solid fa-users-slash fs-1 mb-3"></i>
            <p>No {{ \App\Models\Setting::label('resident', 'Resident') }}s found for this {{ \App\Models\Setting::label('unit', 'flat') }}.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ \App\Models\Setting::label('resident', 'Resident') }} Name</th>
                        <th>Type</th>
                        <th>Move In Date</th>
                        <th>Move Out Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $hasCurrentOwner = false;
                        $hasCurrentRental = false;
                        $nextOwnerMoveIn = null;
                        $nextRentalMoveIn = null;
                    @endphp
                    @foreach($history as $resident)
                        @php
                            $type = $resident->type;
                            $rawMoveOut = $resident->move_out_date;
                            $isCurrentCandidate = is_null($rawMoveOut) || \Carbon\Carbon::parse($rawMoveOut)->startOfDay()->gte(now()->startOfDay());

                            $isCurrent = false;
                            $displayMoveOut = $rawMoveOut ? \Carbon\Carbon::parse($rawMoveOut)->format('d M, Y') : '-';

                            if ($type === 'owner') {
                                if ($isCurrentCandidate && !$hasCurrentOwner) {
                                    $isCurrent = true;
                                    $hasCurrentOwner = true;
                                } else {
                                    $isCurrent = false;
                                    if (!$rawMoveOut && $nextOwnerMoveIn) {
                                        $displayMoveOut = \Carbon\Carbon::parse($nextOwnerMoveIn)->format('d M, Y');
                                    }
                                }
                                $nextOwnerMoveIn = $resident->move_in_date;
                            } else {
                                if ($isCurrentCandidate && !$hasCurrentRental) {
                                    $isCurrent = true;
                                    $hasCurrentRental = true;
                                } else {
                                    $isCurrent = false;
                                    if (!$rawMoveOut && $nextRentalMoveIn) {
                                        $displayMoveOut = \Carbon\Carbon::parse($nextRentalMoveIn)->format('d M, Y');
                                    }
                                }
                                $nextRentalMoveIn = $resident->move_in_date;
                            }
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
                            <td>{{ $displayMoveOut }}</td>
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
