@php
    /*
    |--------------------------------------------------------------------------
    | Sidebar Route Helpers
    |--------------------------------------------------------------------------
    | These helpers prevent errors while modules/routes are still being built.
    | Once routes are created, links will automatically start working.
    */

    $routeUrl = function (string $routeName, string $fallback = '#') {
        return \Illuminate\Support\Facades\Route::has($routeName)
            ? route($routeName)
            : $fallback;
    };

    $isActive = function ($patterns) {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return 'active';
            }
        }

        return '';
    };

    $authUser = auth()->user();
    $canAccess = function (string $area) use ($authUser) {
        return $authUser && $authUser->canAccessArea($area);
    };

    $homeRoute = $authUser ? $authUser->defaultRouteName() : 'dashboard';
@endphp

<aside class="app-sidebar sticky" id="sidebar">

    {{-- =========================================================
        Sidebar Header / Brand
    ========================================================== --}}
    <div class="main-sidebar-header">
        <a href="{{ $routeUrl($homeRoute, url('/dashboard')) }}" class="sidebar-brand">
            <span class="sidebar-brand-icon">
                <i class="bi bi-journal-text"></i>
            </span>

            <span class="sidebar-brand-text">
                <span class="sidebar-brand-name">THE VAULT</span>
                <span class="sidebar-brand-subtitle">Personal finance manager</span>
            </span>
        </a>
    </div>

    {{-- =========================================================
        Sidebar Menu
    ========================================================== --}}
    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            <ul class="main-menu">

                @if ($canAccess(\App\Models\UserType::ACCESS_OVERVIEW))
                    {{-- =====================================================
                        1. Overview
                    ====================================================== --}}
                    <li class="main-menu-title">Overview</li>

                    <li class="slide {{ $isActive('dashboard') }}">
                        <a href="{{ $routeUrl('dashboard', url('/dashboard')) }}" class="side-menu__item">
                            <span class="side-menu__icon"><i class="bi bi-speedometer2"></i></span>
                            <span class="side-menu__label">Dashboard</span>
                        </a>
                    </li>
                @endif

                @if ($canAccess(\App\Models\UserType::ACCESS_TRANSACTIONS))
                    {{-- =====================================================
                        2. Transaction Management
                    ====================================================== --}}
                    <li class="main-menu-title">Transaction Management</li>

                    <li class="slide {{ $isActive('accounts.index*') }}">
                        <a href="{{ $routeUrl('accounts.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-bank"></i>
                            </span>
                            <span class="side-menu__label">Accounts</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('income') }}">
                        <a href="{{ $routeUrl('income') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <span class="side-menu__label">Incomes</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('transactions.index*') }}">
                        <a href="{{ $routeUrl('transactions.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-receipt-cutoff"></i>
                            </span>
                            <span class="side-menu__label">Transactions</span>
                        </a>
                    </li>
                @endif

                @if ($canAccess(\App\Models\UserType::ACCESS_PLANNING))
                    {{-- =====================================================
                        3. Planning
                    ====================================================== --}}
                    <li class="main-menu-title">Planning</li>

                    <li class="slide {{ $isActive('savings_goals*') }}">
                        <a href="{{ $routeUrl('savings_goals') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-piggy-bank"></i>
                            </span>
                            <span class="side-menu__label">Savings Goals</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('budgets.*') }}">
                        <a href="{{ $routeUrl('budgets.index') }}" class="side-menu__item">
                            <span class="side-menu__icon"><i class="bi bi-pie-chart"></i></span>
                            <span class="side-menu__label">Budget</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('recurring-expenses.*') }}">
                        <a href="{{ $routeUrl('recurring-expenses.index') }}" class="side-menu__item">
                            <span class="side-menu__icon"><i class="bi bi-repeat"></i></span>
                            <span class="side-menu__label">Recurring Expenses</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('bill-reminders.*') }}">
                        <a href="{{ $routeUrl('bill-reminders.index') }}" class="side-menu__item">
                            <span class="side-menu__icon"><i class="bi bi-alarm"></i></span>
                            <span class="side-menu__label">Bill Reminders</span>
                        </a>
                    </li>
                @endif

                @if ($canAccess(\App\Models\UserType::ACCESS_USER_MANAGEMENT))
                    {{-- =====================================================
                        4. User Management
                    ====================================================== --}}
                    <li class="main-menu-title">User Management</li>

                    <li class="slide {{ $isActive('users.*') }}">
                        <a href="{{ $routeUrl('users.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-people"></i>
                            </span>
                            <span class="side-menu__label">Users</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('user-types.*') }}">
                        <a href="{{ $routeUrl('user-types.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <span class="side-menu__label">User Types</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('user-permissions.*') }}">
                        <a href="{{ $routeUrl('user-permissions.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-person-lock"></i>
                            </span>
                            <span class="side-menu__label">User Permissions</span>
                        </a>
                    </li>

                    <li class="slide {{ $isActive('user_log.*') }}">
                        <a href="{{ $routeUrl('user_log.index') }}" class="side-menu__item">
                            <span class="side-menu__icon">
                                <i class="bi bi-card-list"></i>
                            </span>
                            <span class="side-menu__label">User Log</span>
                        </a>
                    </li>
                @endif
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>

        </nav>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.slide.has-sub > .side-menu__item').forEach(function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();

                const slide = this.closest('.slide.has-sub');

                if (!slide) {
                    return;
                }

                slide.classList.toggle('open');
            });
        });
    });
</script>