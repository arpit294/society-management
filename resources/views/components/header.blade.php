<header class="header header-sticky glass-header p-0 mb-4">
    <div class="container-fluid border-bottom px-2 px-md-4">
        <button class="header-toggler header-toggler-offset js-sidebar-toggle" type="button">
            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="var(--ci-primary-color, currentcolor)"
                    d="M80 96h352v32H80zm0 144h352v32H80zm0 144h352v32H80z" class="ci-primary" />
            </svg>
        </button>

        <ul class="header-nav ms-auto d-flex align-items-center gap-1 sm-gap-2">
            <!-- 1. Home Button -->
            <li class="nav-item">
                <a class="nav-link p-2 d-flex align-items-center justify-content-center rounded-circle transition-hover" href="{{ route('dashboard') }}" title="Dashboard Home" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-house fs-5 text-body"></i>
                </a>
            </li>

            <!-- 2. Settings Button -->
            @can('setting_view')
            <li class="nav-item">
                <a class="nav-link p-2 d-flex align-items-center justify-content-center rounded-circle transition-hover" href="{{ route('settings.index') }}" title="Society Settings" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-gear fs-5 text-body"></i>
                </a>
            </li>
            @endcan

            <!-- Separator 1 -->
            <li class="nav-item py-1 d-flex align-items-center">
                <div class="vr mx-1 mx-sm-2 text-body text-opacity-25" style="height: 20px;"></div>
            </li>

            <!-- 3. Real-time Notifications Bell -->
            @php
                $headerNotifications = \App\Helpers\ActivityHelper::getRecentActivities(6);
                $unreadCount = $headerNotifications->count();
            @endphp

            <li class="nav-item dropdown d-flex align-items-center">
                <a class="nav-link p-2 position-relative d-flex align-items-center justify-content-center rounded-circle transition-hover" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="Live Society Alerts" style="width: 40px; height: 40px;">
                    <i class="fa-regular fa-bell fs-5 text-body"></i>
                    <span id="bell-badge-counter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light {{ $unreadCount > 0 ? 'notification-badge-pulse' : '' }}" style="font-size: 0.65rem; padding: 0.25em 0.5em; margin-left: -10px; margin-top: 6px;">
                        {{ $unreadCount }}
                        <span class="visually-hidden">unread alerts</span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0 pb-0 shadow-lg border-0 rounded-4 overflow-hidden responsive-dropdown-menu" style="width: 360px; max-width: calc(100vw - 1.5rem);">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-bold p-3 d-flex align-items-center justify-content-between border-bottom">
                        <span class="d-flex align-items-center gap-2 text-primary fs-6">
                            <i class="fa-solid fa-bell"></i> Notifications
                        </span>
                        <a href="javascript:void(0);" onclick="markAllNotificationsAsRead(event);" id="mark-all-read-btn" class="text-decoration-underline text-primary fw-semibold small" style="font-size: 0.8rem; cursor: pointer; {{ $unreadCount == 0 ? 'display: none !important;' : '' }}">Mark all as read</a>
                    </div>

                    <div id="notification-list-container" class="list-group list-group-flush" style="max-height: 380px; overflow-y: auto; {{ $unreadCount == 0 ? 'display: none !important;' : '' }}">
                        @forelse($headerNotifications as $notification)
                            <a href="{{ $notification->url ?? '#' }}" data-timestamp="{{ $notification->timestamp ? $notification->timestamp->getTimestamp() * 1000 : 0 }}" class="list-group-item list-group-item-action p-3 d-flex gap-3 align-items-start notification-item-row text-decoration-none">
                                <div class="avatar avatar-md {{ $notification->bg_class ?? 'bg-primary bg-opacity-10 text-primary' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="{{ $notification->icon }}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold text-truncate d-block text-body" style="font-size: 0.9rem;">{{ $notification->title }}</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $notification->time }}</span>
                                    </div>
                                    <p class="text-muted mb-0 text-truncate" style="font-size: 0.8rem;">{{ $notification->description }}</p>
                                    @if(isset($notification->badge_text))
                                        <span class="badge {{ $notification->badge_class ?? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' }} mt-1" style="font-size: 0.65rem;">{{ $notification->badge_text }}</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                        @endforelse
                    </div>

                    <!-- Empty State Container -->
                    <div id="notification-empty-state" class="p-5 text-center text-muted" style="{{ $unreadCount == 0 ? '' : 'display: none !important;' }}">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center bg-body-secondary bg-opacity-50 rounded-circle" style="width: 64px; height: 64px;">
                            <i class="fa-regular fa-bell-slash fs-3 opacity-50"></i>
                        </div>
                        <p class="mb-0 fw-semibold text-body" style="font-size: 0.9rem;">There are no new Notifications !</p>
                    </div>
                </div>
            </li>

            <!-- Separator 2 -->
            <li class="nav-item py-1 d-flex align-items-center">
                <div class="vr mx-2 text-body text-opacity-25" style="height: 20px;"></div>
            </li>

            <!-- 4. Theme Mode Toggle -->
            <li class="nav-item dropdown d-flex align-items-center">
                <button class="btn btn-link nav-link p-2 d-flex align-items-center justify-content-center rounded-circle transition-hover border-0" type="button"
                    aria-expanded="false" data-coreui-toggle="dropdown" title="Theme Mode" style="width: 40px; height: 40px;">
                    <svg class="icon theme-icon-active text-body" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512" style="width: 1.25rem; height: 1.25rem;">
                        <path fill="currentcolor"
                            d="M256 16C123.452 16 16 123.452 16 256s107.452 240 240 240 240-107.452 240-240S388.548 16 256 16m-22 446.849a208.35 208.35 0 0 1-169.667-125.9c-.364-.859-.706-1.724-1.057-2.587L234 429.939Zm0-69.582L50.889 290.76A210 210 0 0 1 48 256q0-9.912.922-19.67L234 339.939Zm0-90L54.819 202.96a206 206 0 0 1 9.514-27.913Q67.1 168.5 70.3 162.191L234 253.934Zm0-86.015L86.914 134.819a209.4 209.4 0 0 1 22.008-25.9q3.72-3.72 7.6-7.228L234 166.027Zm0-87.708-89.648-49.093A206.95 206.95 0 0 1 234 49.151ZM464 256a207.775 207.775 0 0 1-198 207.761V48.239A207.79 207.79 0 0 1 464 256" />
                    </svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="--cui-dropdown-min-width: 8rem">
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                            data-coreui-theme-value="light">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="currentcolor"
                                    d="M256 104c-83.813 0-152 68.187-152 152s68.187 152 152 152 152-68.187 152-152-68.187-152-152-152m0 272a120 120 0 1 1 120-120 120.136 120.136 0 0 1-120 120M240 16h32v48h-32zm0 432h32v48h-32zm208-208h48v32h-48zm-432 0h48v32H16zm372.687 171.314 22.627-22.627 32 32-22.627 22.627zm-320-320 22.628-22.628 32 32-22.628 22.628zm-.002 329.375 32-32 22.628 22.626-32 32zm320.002-320.003 32-32 22.628 22.628-32 32z" />
                            </svg>
                            Light
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                            data-coreui-theme-value="dark">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="currentcolor"
                                    d="M268.279 496c-67.574 0-130.978-26.191-178.534-73.745S16 311.293 16 243.718A252.25 252.25 0 0 1 154.183 18.676a24.44 24.44 0 0 1 34.46 28.958 220.12 220.12 0 0 0 54.8 220.923A218.75 218.75 0 0 0 399.085 333.2a220.2 220.2 0 0 0 65.277-9.846 24.439 24.439 0 0 1 28.959 34.461A252.26 252.26 0 0 1 268.279 496M153.31 55.781A219.3 219.3 0 0 0 48 243.718C48 365.181 146.816 464 268.279 464a219.3 219.3 0 0 0 187.938-105.31 253 253 0 0 1-57.13 6.513 250.54 250.54 0 0 1-178.268-74.016 252.15 252.15 0 0 1-67.509-235.4Z" />
                            </svg>
                            Dark
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center active" type="button"
                            data-coreui-theme-value="auto">
                            <svg class="icon icon-lg me-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="currentcolor"
                                    d="M256 16C123.452 16 16 123.452 16 256s107.452 240 240 240 240-107.452 240-240S388.548 16 256 16m-22 446.849a208.35 208.35 0 0 1-169.667-125.9c-.364-.859-.706-1.724-1.057-2.587L234 429.939Zm0-69.582L50.889 290.76A210 210 0 0 1 48 256q0-9.912.922-19.67L234 339.939Zm0-90L54.819 202.96a206 206 0 0 1 9.514-27.913Q67.1 168.5 70.3 162.191L234 253.934Zm0-86.015L86.914 134.819a209.4 209.4 0 0 1 22.008-25.9q3.72-3.72 7.6-7.228L234 166.027Zm0-87.708-89.648-49.093A206.95 206.95 0 0 1 234 49.151ZM464 256a207.775 207.775 0 0 1-198 207.761V48.239A207.79 207.79 0 0 1 464 256" />
                            </svg>
                            Auto
                        </button>
                    </li>
                </ul>
            </li>

            <!-- Separator 3 -->
            <li class="nav-item py-1 d-flex align-items-center">
                <div class="vr mx-2 text-body text-opacity-25" style="height: 20px;"></div>
            </li>

            <!-- 5. Language Switcher -->
            @php
                $currentLocale = app()->getLocale();
                $localeNames = [
                    'en' => ['name' => 'English', 'badge' => 'EN'],
                    'hi' => ['name' => 'हिन्दी', 'badge' => 'HI'],
                    'gu' => ['name' => 'ગુજરાતી', 'badge' => 'GU'],
                ];
                $activeBadge = $localeNames[$currentLocale]['badge'] ?? 'EN';
            @endphp
            <li class="nav-item dropdown d-flex align-items-center">
                <a class="nav-link p-2 position-relative d-flex align-items-center justify-content-center rounded-circle transition-hover" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="{{ __('Language') }}" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-language fs-4 text-body"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-light fw-bold" style="font-size: 0.6rem; padding: 0.2em 0.4em; margin-left: -12px; margin-top: 8px;">
                        {{ $activeBadge }}
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="min-width: 9rem;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === 'en' ? 'active fw-bold' : '' }}" href="{{ route('locale.change', 'en') }}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="width: 28px;">EN</span>
                                English
                            </span>
                            @if($currentLocale === 'en')
                                <i class="fa-solid fa-check text-primary ms-2"></i>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === 'hi' ? 'active fw-bold' : '' }}" href="{{ route('locale.change', 'hi') }}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="width: 28px;">HI</span>
                                हिन्दी
                            </span>
                            @if($currentLocale === 'hi')
                                <i class="fa-solid fa-check text-primary ms-2"></i>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === 'gu' ? 'active fw-bold' : '' }}" href="{{ route('locale.change', 'gu') }}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="width: 28px;">GU</span>
                                ગુજરાતી
                            </span>
                            @if($currentLocale === 'gu')
                                <i class="fa-solid fa-check text-primary ms-2"></i>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Separator 4 -->
            <li class="nav-item py-1 d-flex align-items-center">
                <div class="vr mx-2 text-body text-opacity-25" style="height: 20px;"></div>
            </li>

            <!-- 6. User Account Avatar Dropdown -->
            <li class="nav-item dropdown d-flex align-items-center ms-1">
                <a class="nav-link py-0 pe-0 d-flex align-items-center" data-coreui-toggle="dropdown" href="#" role="button"
                    aria-haspopup="true" aria-expanded="false" title="Account">
                    <div class="avatar avatar-md border border-primary border-opacity-25 shadow-sm"><img class="avatar-img" src="{{ asset('assets/img/avatars/8.jpg') }}"
                            alt="user@email.com"></div>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                        {{ __('Account') }}</div>

                    @guest
                        <a class="dropdown-item" href="{{ route('register') }}">
                            <svg class="icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M376 256c-44.112 0-80 35.888-80 80s35.888 80 80 80 80-35.888 80-80-35.888-80-80-80m48 96h-32v32h-32v-32h-32v-32h32v-32h32v32h32zM256 272c61.757 0 112-50.243 112-112S317.757 48 256 48 144 98.243 144 160s50.243 112 112 112m0-192c44.112 0 80 35.888 80 80s-35.888 80-80 80-80-35.888-80-80 35.888-80 80-80M256 304c-88.366 0-160 53.726-160 120v40h184.858A111.5 111.5 0 0 1 264 416H128c5.691-44.921 60.929-80 128-80 3.222 0 6.426.083 9.61.247A111.4 111.4 0 0 1 280.858 304z"
                                    class="ci-primary" />
                            </svg>
                            {{ __('Register') }}
                        </a>

                        <a class="dropdown-item" href="{{ route('login') }}">
                            <svg class="icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="var(--ci-primary-color, currentcolor)"
                                    d="M77.155 272.034H351.75v-32.001H77.155l75.053-75.053v-.001l-22.628-22.626-113.681 113.68.001.001h-.001L129.58 369.715l22.628-22.627v-.001z"
                                    class="ci-primary" />
                                <path fill="var(--ci-primary-color, currentcolor)" d="M160 16v32h304v416H160v32h336V16z"
                                    class="ci-primary" />
                            </svg>
                            {{ __('Login') }}
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
                                {{ __('Logout') }}
                            </button>
                        </form>
                    @endguest
                </div>
            </li>
        </ul>
    </div>
</header>

