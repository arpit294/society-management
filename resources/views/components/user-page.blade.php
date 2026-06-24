<x-layout>
    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">
            <div class="container-lg px-4 py-4">
                @if(session('error') && str_contains(session('error'), '<br>'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-4 border-danger" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
                            <div>{!! session('error') !!}</div>
                        </div>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
        <x-footer />
    </div>
</x-layout>
