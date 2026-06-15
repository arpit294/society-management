<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand me-auto">
            <svg class="sidebar-brand-full" width="180" height="40" viewBox="0 0 180 40"
                xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="brandGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#8b5cf6" />
                    </linearGradient>
                </defs>
                <g transform="translate(5, 4)">
                    <rect x="4" y="12" width="10" height="20" rx="2" fill="url(#brandGrad)"
                        opacity="0.8" />
                    <rect x="16" y="4" width="10" height="28" rx="2" fill="url(#brandGrad)" />
                    <path d="M0 32h30" stroke="#ffffff" stroke-width="2" stroke-linecap="round" opacity="0.2" />
                </g>
                <text x="44" y="26" font-family="system-ui, -apple-system, sans-serif" font-size="18" font-weight="700"
                    fill="#f8fafc" letter-spacing="0.5">Society</text>
            </svg>
            <svg class="sidebar-brand-narrow" width="32" height="32" viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="brandGradNarrow" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#8b5cf6" />
                    </linearGradient>
                </defs>
                <g transform="translate(1, 0)">
                    <rect x="4" y="12" width="10" height="20" rx="2" fill="url(#brandGradNarrow)"
                        opacity="0.8" />
                    <rect x="16" y="4" width="10" height="28" rx="2" fill="url(#brandGradNarrow)" />
                    <path d="M0 32h30" stroke="#ffffff" stroke-width="2" stroke-linecap="round" opacity="0.2" />
                </g>
            </svg>
        </div>
        <button class="btn-close d-lg-none" type="button" data-coreui-theme="dark" aria-label="Close"
            onclick="(function(){const sidebar=document.getElementById('sidebar');if(!sidebar||!window.coreui?.Sidebar)return;const instance=window.coreui.Sidebar.getInstance(sidebar);instance?.toggle();})()"></button>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M425.706 142.294A240 240 0 0 0 16 312v88h144v-32H48v-56c0-114.691 93.309-208 208-208s208 93.309 208 208v56H352v32h144v-88a238.43 238.43 0 0 0-70.294-169.706"
                        class="ci-primary" />
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M80 264h32v32H80zm160-136h32v32h-32zm-104 40h32v32h-32zm264 96h32v32h-32zm-102.778 71.1 69.2-144.173-28.85-13.848-69.183 144.135a64.141 64.141 0 1 0 28.833 13.886M256 416a32 32 0 1 1 32-32 32.036 32.036 0 0 1-32 32"
                        class="ci-primary" />
                </svg>
                Dashboard
                <span class="badge badge-sm bg-info ms-auto">NEW</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
                <!-- User Icon -->
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M256 256A112 112 0 1 0 256 32a112 112 0 0 0 0 224zm0 32c-88.366 0-160 71.634-160 160v32h320v-32c0-88.366-71.634-160-160-160z" />
                </svg>
                User Management
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('flats.index') }}">
                <!-- Flat / Building Icon -->
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M96 48C78.3 48 64 62.3 64 80v352c0 17.7 14.3 32 32 32h96V336h128v128h96c17.7 0 32-14.3 32-32V80c0-17.7-14.3-32-32-32H96zm64 64h48v48h-48v-48zm0 96h48v48h-48v-48zm0 96h48v48h-48v-48zm144-192h48v48h-48v-48zm0 96h48v48h-48v-48zm0 96h48v48h-48v-48z" />
                </svg>
                Flat Management
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('flat-types.index') }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M224 48H32v128h192V48zm0 192H32v224h192V240zm64-192v224h192V48H288zm0 288v160h192V336H288z" />
                </svg>
                Flat Types
            </a>
        </li>



        <li class="nav-item">
            <a class="nav-link" href="{{ route('blocks.index') }}">
                <!-- Block Management Icon -->
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M32 32C14.3 32 0 46.3 0 64V448c0 17.7 14.3 32 32 32H480c17.7 0 32-14.3 32-32V64c0-17.7-14.3-32-32-32H32zm64 64H224V224H96V96zm192 0H416V224H288V96zM96 288H224V416H96V288zm192 0H416V416H288V288z" />
                </svg>
                Block Management
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('complains.index') }}">
                <!-- Complaint Icon -->
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M256 32C114.6 32 0 125.1 0 240c0 49.6 21.4 95 57 130.7C44.5 421.1 22.7 481.2 22 483.5c-1.6 5.4-1.1 11.3 1.4 16.3s7.2 8.1 12.7 8.1c66.6 0 119.4-33.3 151.4-58.4 22.2 6.4 46 9.8 70.5 9.8 141.4 0 256-92.9 256-208S397.4 32 256 32zm0 395.3c-23.1 0-45.7-3.6-67-10.6l-16.1-5.3-13.8 9.6c-27 18.8-67 43.1-118 48 9.3-26.6 24.3-64.8 32-84.3l8.8-22.3-16.4-17.5C33.8 310.6 16 276.6 16 240c0-106 107.5-192 240-192s240 86 240 192-107.5 192-240 192zM272 160h-32v112h32V160zm-16 176c-13.3 0-24 10.7-24 24s10.7 24 24 24 24-10.7 24-24-10.7-24-24-24z" />
                </svg>
                Complaints
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('residents.index') }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M256 160c-44.2 0-80 35.8-80 80s35.8 80 80 80 80-35.8 80-80-35.8-80-80-80zm0 128c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm171.1-125.1L355.2 234.8c-2.4 12.5-8 23.9-15.8 33.6l64.1 64.1c12.5 12.5 32.8 12.5 45.3 0 12.5-12.5 12.5-32.8 0-45.3l-21.7-24.3zM140.7 268.4c-7.8-9.7-13.4-21.1-15.8-33.6L52.9 162.9c-12.5-12.5-12.5-32.8 0-45.3 12.5-12.5 32.8-12.5 45.3 0l71.9 71.9L140.7 268.4z" />
                </svg>
                Residents
            </a>
        </li>
        <li class="nav-group {{ request()->is('maintenance-bills*') || request()->is('payments*') || request()->is('expense-categories*') || request()->is('expenses*') || request()->is('prepayments*') ? 'show' : '' }}">
            <a class="nav-link nav-group-toggle" href="#">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M448 32H64C28.65 32 0 60.65 0 96v320c0 35.35 28.65 64 64 64h384c35.35 0 64-28.65 64-64V96c0-35.35-28.65-64-64-64zM64 64h384c17.64 0 32 14.36 32 32v64H32V96c0-17.64 14.36-32 32-32zm384 384H64c-17.64 0-32-14.36-32-32V192h448v224c0 17.64-14.36 32-32 32zM128 256h128v32H128v-32zm0 64h256v32H128v-32zm0 64h256v32H128v-32z" />
                </svg>
                Finances
            </a>
            <ul class="nav-group-items">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('maintenance-bills*') || request()->is('payments*') || request()->is('prepayments*') ? 'active' : '' }}" href="{{ route('maintenance-bills.index') }}">
                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                        Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('expense-categories*') ? 'active' : '' }}" href="{{ route('expense-categories.index') }}">
                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                        Expense Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('expenses*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                        Expenses
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.maintenance') ? 'active' : '' }}" href="{{ route('reports.maintenance') }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="var(--ci-primary-color, currentcolor)" d="M448 32H64C28.65 32 0 60.65 0 96v320c0 35.35 28.65 64 64 64h384c35.35 0 64-28.65 64-64V96c0-35.35-28.65-64-64-64zM64 64h384c17.64 0 32 14.36 32 32v64H32V96c0-17.64 14.36-32 32-32zm384 384H64c-17.64 0-32-14.36-32-32V192h448v224c0 17.64-14.36 32-32 32zM128 256h128v32H128v-32zm0 64h256v32H128v-32zm0 64h256v32H128v-32z" />
                </svg>
                Reports
            </a>
        </li>
    </ul>
    {{-- <div class="sidebar-footer border-top d-none d-md-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div> --}}
</div>


{{--  --}}
