<header class="header header-sticky p-0 mb-4">
    <div class="container-fluid border-bottom px-4">
        <button class="header-toggler" type="button"
            onclick="(function(){const sidebar=document.getElementById('sidebar');if(!sidebar||!window.coreui?.Sidebar)return;const instance=window.coreui.Sidebar.getInstance(sidebar);instance?.toggle();})()"
            style="margin-inline-start: -14px">
            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="var(--ci-primary-color, currentcolor)"
                    d="M80 96h352v32H80zm0 144h352v32H80zm0 144h352v32H80z" class="ci-primary" />
            </svg>
        </button>

        <ul class="header-nav">
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('dashboard') }}" title="Home">
                    <i class="fa-solid fa-house fs-5" style="color: var(--ci-primary-color, currentcolor);"></i>
                </a>
            </li>
            @can('setting_view')
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('settings.index') }}" title="Settings">
                    <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="var(--ci-primary-color, currentcolor)" d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/>
                    </svg>
                </a>
            </li>
            @endcan

            <li class="nav-item py-1">
                <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button"
                    aria-expanded="false" data-coreui-toggle="dropdown">
                    <svg class="icon icon-lg theme-icon-active" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512">
                        <path fill="var(--ci-primary-color, currentcolor)"
                            d="M256 16C123.452 16 16 123.452 16 256s107.452 240 240 240 240-107.452 240-240S388.548 16 256 16m-22 446.849a208.35 208.35 0 0 1-169.667-125.9c-.364-.859-.706-1.724-1.057-2.587L234 429.939Zm0-69.582L50.889 290.76A210 210 0 0 1 48 256q0-9.912.922-19.67L234 339.939Zm0-90L54.819 202.96a206 206 0 0 1 9.514-27.913Q67.1 168.5 70.3 162.191L234 253.934Zm0-86.015L86.914 134.819a209.4 209.4 0 0 1 22.008-25.9q3.72-3.72 7.6-7.228L234 166.027Zm0-87.708-89.648-49.093A206.95 206.95 0 0 1 234 49.151ZM464 256a207.775 207.775 0 0 1-198 207.761V48.239A207.79 207.79 0 0 1 464 256"
                            class="ci-primary" />
                    </svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem">
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                            data-coreui-theme-value="light">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M256 104c-83.813 0-152 68.187-152 152s68.187 152 152 152 152-68.187 152-152-68.187-152-152-152m0 272a120 120 0 1 1 120-120 120.136 120.136 0 0 1-120 120M240 16h32v48h-32zm0 432h32v48h-32zm208-208h48v32h-48zm-432 0h48v32H16zm372.687 171.314 22.627-22.627 32 32-22.627 22.627zm-320-320 22.628-22.628 32 32-22.628 22.628zm-.002 329.375 32-32 22.628 22.626-32 32zm320.002-320.003 32-32 22.628 22.628-32 32z"
                                    class="ci-primary" />
                            </svg>
                            Light
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                            data-coreui-theme-value="dark">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M268.279 496c-67.574 0-130.978-26.191-178.534-73.745S16 311.293 16 243.718A252.25 252.25 0 0 1 154.183 18.676a24.44 24.44 0 0 1 34.46 28.958 220.12 220.12 0 0 0 54.8 220.923A218.75 218.75 0 0 0 399.085 333.2a220.2 220.2 0 0 0 65.277-9.846 24.439 24.439 0 0 1 28.959 34.461A252.26 252.26 0 0 1 268.279 496M153.31 55.781A219.3 219.3 0 0 0 48 243.718C48 365.181 146.816 464 268.279 464a219.3 219.3 0 0 0 187.938-105.31 253 253 0 0 1-57.13 6.513 250.54 250.54 0 0 1-178.268-74.016 252.15 252.15 0 0 1-67.509-235.4Z"
                                    class="ci-primary" />
                            </svg>
                            Dark
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center active" type="button"
                            data-coreui-theme-value="auto">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M256 16C123.452 16 16 123.452 16 256s107.452 240 240 240 240-107.452 240-240S388.548 16 256 16m-22 446.849a208.35 208.35 0 0 1-169.667-125.9c-.364-.859-.706-1.724-1.057-2.587L234 429.939Zm0-69.582L50.889 290.76A210 210 0 0 1 48 256q0-9.912.922-19.67L234 339.939Zm0-90L54.819 202.96a206 206 0 0 1 9.514-27.913Q67.1 168.5 70.3 162.191L234 253.934Zm0-86.015L86.914 134.819a209.4 209.4 0 0 1 22.008-25.9q3.72-3.72 7.6-7.228L234 166.027Zm0-87.708-89.648-49.093A206.95 206.95 0 0 1 234 49.151ZM464 256a207.775 207.775 0 0 1-198 207.761V48.239A207.79 207.79 0 0 1 464 256"
                                    class="ci-primary" />
                            </svg>
                            Auto
                        </button>
                    </li>
                </ul>
            </li>
            <li class="nav-item py-1">
                <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#" role="button"
                    aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-md"><img class="avatar-img" src="{{ asset('assets/img/avatars/8.jpg') }}"
                            alt="user@email.com"></div>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                        Account</div>

                    @guest
                        <a class="dropdown-item" href="{{ route('register') }}">
                            <svg class="icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M376 256c-44.112 0-80 35.888-80 80s35.888 80 80 80 80-35.888 80-80-35.888-80-80-80m48 96h-32v32h-32v-32h-32v-32h32v-32h32v32h32zM256 272c61.757 0 112-50.243 112-112S317.757 48 256 48 144 98.243 144 160s50.243 112 112 112m0-192c44.112 0 80 35.888 80 80s-35.888 80-80 80-80-35.888-80-80 35.888-80 80-80M256 304c-88.366 0-160 53.726-160 120v40h184.858A111.5 111.5 0 0 1 264 416H128c5.691-44.921 60.929-80 128-80 3.222 0 6.426.083 9.61.247A111.4 111.4 0 0 1 280.858 304z"
                                    class="ci-primary" />
                            </svg>
                            Register
                        </a>

                        <a class="dropdown-item" href="{{ route('login') }}">
                            <svg class="icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M77.155 272.034H351.75v-32.001H77.155l75.053-75.053v-.001l-22.628-22.626-113.681 113.68.001.001h-.001L129.58 369.715l22.628-22.627v-.001z"
                                    class="ci-primary" />
                                <path fill="var(--ci-primary-color, currentcolor)" d="M160 16v32h304v416H160v32h336V16z"
                                    class="ci-primary" />
                            </svg>
                            Login
                        </a>
                    @else
                        <form action="{{ route('logout') }}" method="POST" class="mb-0">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <svg class="icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path fill="var(--ci-primary-color, currentcolor)"
                                        d="M77.155 272.034H351.75v-32.001H77.155l75.053-75.053v-.001l-22.628-22.626-113.681 113.68.001.001h-.001L129.58 369.715l22.628-22.627v-.001z"
                                        class="ci-primary" />
                                    <path fill="var(--ci-primary-color, currentcolor)" d="M160 16v32h304v416H160v32h336V16z"
                                        class="ci-primary" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    @endguest
                </div>
            </li>
        </ul>
    </div>

    {{-- hero section --}}
    <div class="container-fluid px-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb my-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User Management</a></li>
                <li class="breadcrumb-item"><a href="{{ route('residents.index') }}">Residents</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flats.index') }}">Flats</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blocks.index') }}">Blocks</a></li>
                <li class="breadcrumb-item"><a href="{{ route('complains.index') }}">Complaints</a></li>
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item"><a href="{{ route('maintenance-bills.index') }}">Maintenance Bills</a></li>
            </ol>
        </nav>
    </div>
</header>
