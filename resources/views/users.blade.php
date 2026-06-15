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
                                        <td>{{ $user->role }}</td>

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
