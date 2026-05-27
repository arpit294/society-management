<x-layout>
    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">
            <div class="container-lg px-4 py-4">
                {{ $slot }}
            </div>
        </div>
        <x-footer />
    </div>
</x-layout>
