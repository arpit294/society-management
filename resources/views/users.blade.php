<x-layout>
    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />



        @section('content')
            <div class="container-fluid">

                <div class="card bg-dark text-white">
                    <div class="card-header d-flex justify-content-between">
                        <h3>User Management</h3>

                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            Add User
                        </a>
                    </div>

                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <table class="table table-dark table-hover">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Designation</th>
                                    <th width="200">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>
                                            @php
                                                $rLower = strtolower($user->role ?? '');
                                            @endphp
                                            <span class="badge bg-secondary bg-opacity-25 text-body px-3 py-1 fw-semibold">{{ ucwords(str_replace('_', ' ', $user->role ?? 'N/A')) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $desig = trim((string) $user->designation);
                                                if (empty($desig)) {
                                                    if ($rLower === 'admin') $desig = 'Admin';
                                                    elseif (in_array($rLower, ['committee_member', 'commitee_member'])) $desig = 'Committee Member';
                                                    elseif (in_array($rLower, ['secretary', 'secretory'])) $desig = 'Secretary';
                                                }
                                                $dLower = strtolower($desig);
                                            @endphp
                                            @if(in_array($dLower, ['admin', 'society admin']))
                                                <span class="badge bg-danger text-white px-3 py-2 fw-bold shadow-sm"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                                            @elseif(in_array($dLower, ['committee member', 'committee_member', 'commitee member', 'commitee_member']))
                                                <span class="badge bg-primary text-white px-3 py-2 fw-bold shadow-sm"><i class="fa-solid fa-users-gear me-1"></i> Committee Member</span>
                                            @elseif(in_array($dLower, ['secretary', 'secretory']))
                                                <span class="badge bg-info text-dark px-3 py-2 fw-bold shadow-sm"><i class="fa-solid fa-user-tie me-1"></i> Secretary</span>
                                            @elseif(in_array($dLower, ['chairman']))
                                                <span class="badge bg-warning text-dark px-3 py-2 fw-bold shadow-sm"><i class="fa-solid fa-crown me-1"></i> Chairman</span>
                                            @elseif(in_array($dLower, ['treasurer']))
                                                <span class="badge bg-success text-white px-3 py-2 fw-bold shadow-sm"><i class="fa-solid fa-vault me-1"></i> Treasurer</span>
                                            @elseif(!empty($desig))
                                                <span class="badge bg-dark text-white px-3 py-2 fw-semibold shadow-sm">{{ ucwords($desig) }}</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1">-</span>
                                            @endif
                                        </td>

                                        <td class="d-flex gap-2">

                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
        @endsection


        <x-footer />
    </div>
</x-layout />
