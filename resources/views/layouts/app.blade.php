<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="ltr"
      data-nav-layout="vertical"
      data-theme-mode="light"
      data-header-styles="light"
      data-menu-styles="light">

<head>
    {{-- =========================================================
        Meta Data
    ========================================================== --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ config('app.name', 'expense_ledger') }}</title>

    <meta name="description" content="expense_ledger - Personal finance and expense management">
    <meta name="author" content="expense_ledger">

    {{-- =========================================================
        Favicon
    ========================================================== --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- =========================================================
        Bootstrap CSS - Local
    ========================================================== --}}
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/bootstrap-icons.min.css') }}" rel="stylesheet">

    {{-- =========================================================
        Global Theme Styles
        Theme: Bootstrap Blue Finance UI
    ========================================================== --}}
    <style>
        :root {
            --el-primary: #0f4fbf;
            --el-primary-dark: #0b3f99;
            --el-primary-soft: #eaf3ff;
            --el-primary-light: #dbeafe;

            --el-bg: #f4f8ff;
            --el-bg-soft: #eef6ff;
            --el-card: #ffffff;
            --el-border: #d7e3f5;

            --el-text: #102a56;
            --el-muted: #64748b;

            --el-success: #16a34a;
            --el-danger: #dc2626;
            --el-warning: #f59e0b;

            --el-sidebar-width: 248px;
            --el-sidebar-rail-width: 64px;
            --el-header-height: 72px;
            --el-radius: 16px;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            overflow-x: hidden;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(15, 79, 191, 0.10), transparent 28rem),
                linear-gradient(135deg, #ffffff 0%, var(--el-bg) 45%, var(--el-bg-soft) 100%);
            color: var(--el-text);
        }

        a {
            text-decoration: none;
        }

        .page {
            min-height: 100vh;
        }

        /* =========================================================
            Loader
        ========================================================== */
        #loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(244, 248, 255, 0.96);
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        #loader.loaded {
            opacity: 0;
            visibility: hidden;
        }

        .el-loader-spinner {
            width: 42px;
            height: 42px;
            border: 4px solid var(--el-primary-light);
            border-top-color: var(--el-primary);
            border-radius: 50%;
            animation: elSpin 0.8s linear infinite;
        }

        @keyframes elSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* =========================================================
            Header
        ========================================================== */
        .app-header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--el-sidebar-width);
            height: var(--el-header-height);
            z-index: 1030;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(215, 227, 245, 0.90);
            transition: left 0.2s ease;
        }

        .main-header-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
        }

        .header-content-left,
        .header-content-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--el-text);
        }

        .sidemenu-toggle {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid var(--el-border);
            background: #ffffff;
            color: var(--el-primary);
            box-shadow: 0 6px 16px rgba(15, 79, 191, 0.08);
        }

        .sidemenu-toggle i {
            font-size: 22px;
        }

        .el-search-box {
            position: relative;
            width: min(380px, 42vw);
        }

        .el-search-box input {
            width: 100%;
            height: 44px;
            border-radius: 14px;
            border: 1px solid var(--el-border);
            background: #ffffff;
            color: var(--el-text);
            font-size: 14px;
            padding: 0 14px 0 42px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .el-search-box input:focus {
            border-color: var(--el-primary);
            box-shadow: 0 0 0 4px rgba(15, 79, 191, 0.10);
        }

        .el-search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--el-muted);
            font-size: 15px;
        }

        .el-icon-btn {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid var(--el-border);
            background: #ffffff;
            color: var(--el-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .el-icon-btn i {
            font-size: 18px;
        }

        .el-icon-btn:hover {
            background: var(--el-primary-soft);
            border-color: #b8cff6;
            color: var(--el-primary-dark);
        }

        /* =========================================================
            Header Date & Time
        ========================================================== */
        .el-time-widget {
            display: flex;
            align-items: center;
        }

        .el-current-time-box {
            min-height: 42px;
            padding: 7px 14px;
            border: 1px solid var(--el-border);
            border-radius: 14px;
            background: #ffffff;
            color: var(--el-text);
            box-shadow: 0 6px 16px rgba(15, 79, 191, 0.06);
            display: flex;
            align-items: center;
            gap: 9px;
            white-space: nowrap;
        }

        .el-current-time-box::after {
            display: none;
        }

        .el-current-time-box i {
            color: var(--el-primary);
            font-size: 17px;
        }

        .el-current-time-text {
            font-size: 13px;
            font-weight: 700;
            color: var(--el-text);
        }

        .el-timezone-dropdown {
            width: 330px;
            border: 1px solid var(--el-border);
            border-radius: 16px;
            padding: 10px;
            box-shadow: 0 20px 45px rgba(15, 42, 86, 0.14);
        }

        .el-timezone-item {
            padding: 9px 10px;
            border-radius: 11px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .el-timezone-item:hover {
            background: var(--el-primary-soft);
        }

        .el-timezone-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--el-text);
        }

        .el-timezone-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--el-muted);
            white-space: nowrap;
        }

        @media (max-width: 1199.98px) {
            .el-time-widget {
                display: none;
            }
        }

        .el-profile-toggle {
            border: 1px solid var(--el-border);
            background: #ffffff;
            border-radius: 999px;
            padding: 5px 10px 5px 5px;
            color: var(--el-text);
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 8px 20px rgba(15, 79, 191, 0.06);
        }

        .el-profile-toggle::after {
            display: none;
        }

        .el-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--el-primary), #4f8df7);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        .el-user-name {
            max-width: 150px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-size: 13px;
            font-weight: 600;
        }

        .el-header-dropdown {
            width: 250px;
            border: 1px solid var(--el-border);
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 42, 86, 0.14);
            padding: 8px;
        }

        .el-header-dropdown .dropdown-item {
            border-radius: 10px;
            font-size: 13px;
            padding: 9px 11px;
            color: var(--el-text);
        }

        .el-header-dropdown .dropdown-item:hover {
            background: var(--el-primary-soft);
            color: var(--el-primary-dark);
        }

        /* =========================================================
            Main Content
        ========================================================== */
        .main-content.app-content {
            margin-left: var(--el-sidebar-width);
            padding-top: var(--el-header-height);
            min-height: auto;
            transition: margin-left 0.2s ease;
        }

        .el-content-container {
            padding: 24px;
            min-height: calc(93vh - var(--el-header-height));
        }

        .el-page-title-block {
            margin-bottom: 22px;
        }

        .el-page-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--el-text);
            margin: 0;
        }

        .el-page-subtitle {
            color: var(--el-muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /* =========================================================
            Reusable Page UI Classes
        ========================================================== */
        .el-card {
            background: var(--el-card);
            border: 1px solid rgba(215, 227, 245, 0.92);
            border-radius: var(--el-radius);
            box-shadow: 0 14px 35px rgba(15, 79, 191, 0.07);
        }

        .el-card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--el-border);
        }

        .el-card-body {
            padding: 20px;
        }

        .el-btn-primary,
        .btn-primary {
            background: var(--el-primary) !important;
            border-color: var(--el-primary) !important;
            color: #ffffff !important;
            border-radius: 11px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(15, 79, 191, 0.20);
        }

        .el-btn-primary:hover,
        .btn-primary:hover {
            background: var(--el-primary-dark) !important;
            border-color: var(--el-primary-dark) !important;
        }

        .el-btn-light {
            background: #ffffff;
            border: 1px solid var(--el-border);
            color: var(--el-text);
            border-radius: 11px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-color: var(--el-border);
            border-radius: 11px;
            min-height: 40px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--el-primary);
            box-shadow: 0 0 0 4px rgba(15, 79, 191, 0.10);
        }

        .table {
            color: var(--el-text);
            font-size: 13px;
        }

        .table thead th {
            color: var(--el-muted);
            font-weight: 700;
            font-size: 12px;
            border-bottom-color: var(--el-border);
            background: #f8fbff;
        }

        .table tbody td {
            border-bottom-color: #edf3fb;
            vertical-align: middle;
        }

        /* =========================================================
            Footer
        ========================================================== */
        .footer {
            margin-left: var(--el-sidebar-width);
            background: transparent;
            color: var(--el-muted);
            font-size: 13px;
            transition: margin-left 0.2s ease;
        }

        .footer a {
            color: var(--el-primary-dark);
            font-weight: 700;
        }

        /* =========================================================
            Overlay / Scroll To Top
        ========================================================== */
        .el-responsive-overlay {
            display: none;
        }

        .scrollToTop {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 42px;
            height: 42px;
            display: none;
            align-items: center;
            justify-content: center;
            background: var(--el-primary);
            color: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 28px rgba(15, 79, 191, 0.25);
            cursor: pointer;
            z-index: 1020;
        }

        .scrollToTop.show {
            display: flex;
        }

        /* =========================================================
            Desktop Sidebar Toggle
        ========================================================== */
        html[data-toggled="close"] .app-sidebar {
            width: var(--el-sidebar-rail-width);
            transform: translateX(0);
        }

        html[data-toggled="close"] .app-header {
            left: var(--el-sidebar-rail-width);
        }

        html[data-toggled="close"] .main-content.app-content,
        html[data-toggled="close"] .footer {
            margin-left: var(--el-sidebar-rail-width);
        }

        /* =========================================================
            Responsive
        ========================================================== */
        @media (max-width: 991.98px) {
            .app-header {
                left: 0;
            }

            .main-content.app-content,
            .footer {
                margin-left: 0;
            }

            .el-search-box {
                display: none;
            }

            .el-user-name {
                display: none;
            }

            body.sidebar-open .app-sidebar {
                transform: translateX(0);
            }

            body.sidebar-open .el-responsive-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 42, 86, 0.35);
                z-index: 1035;
            }
        }

        

        @media (max-width: 575.98px) {
            .el-content-container {
                padding: 16px;
            }

            .main-header-container {
                padding: 0 14px;
            }
        }
    </style>

    @yield('page_styles')
</head>

<body>
    @php
        $authUser = Auth::user();

        $userName = trim($authUser->name ?? '');

        if ($userName === '') {
            $userName = trim(($authUser->first_name ?? '') . ' ' . ($authUser->last_name ?? ''));
        }

        if ($userName === '') {
            $userName = 'User';
        }

        $userEmail = $authUser->email ?? '';
        $userInitial = strtoupper(substr($userName, 0, 1));

        $profileUrl = \Illuminate\Support\Facades\Route::has('profile.edit')
            ? route('profile.edit')
            : '#';
    @endphp

    {{-- =========================================================
        Loader
    ========================================================== --}}
    <div id="loader">
        <div class="el-loader-spinner"></div>
    </div>

    <div class="page">

        {{-- =========================================================
            Header
        ========================================================== --}}
        <header class="app-header">
            <div class="main-header-container container-fluid">

                {{-- Header Left --}}
                <div class="header-content-left">
                    <a aria-label="Toggle Sidebar"
                       class="sidemenu-toggle header-link"
                       id="sidebarToggle"
                       href="javascript:void(0);">
                        <i class="bi bi-list"></i>
                    </a>

                </div>

                {{-- Header Right --}}
                <div class="header-content-right">
                
                {{--Date & Time --}}
                <div class="el-time-widget">
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                        class="el-current-time-box dropdown-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false">

                            <i class="bi bi-clock-history"></i>

                            <span class="el-current-time-text" id="currentDateTime">
                                Loading time...
                            </span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end el-timezone-dropdown">
                            <div class="px-2 pb-2 border-bottom mb-2">
                                <p class="mb-0 fw-bold">World Time</p>
                            </div>

                            <div id="timezoneList"></div>
                        </div>
                    </div>
                </div>

                    {{-- Profile --}}
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                           class="el-profile-toggle dropdown-toggle"
                           id="mainHeaderProfile"
                           data-bs-toggle="dropdown"
                           data-bs-auto-close="outside"
                           aria-expanded="false">
                            <span class="el-user-avatar">{{ $userInitial }}</span>
                            <span class="el-user-name">{{ $userName }}</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end el-header-dropdown"
                             aria-labelledby="mainHeaderProfile">

                            <div class="px-2 py-3 border-bottom text-center">
                                <div class="el-user-avatar mx-auto mb-2">{{ $userInitial }}</div>
                                <p class="fw-bold mb-0">{{ $userName }}</p>
                                <span class="text-muted small">{{ $userEmail }}</span>
                            </div>

                            <div class="py-2">
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ $profileUrl }}">
                                    <i class="bi bi-person"></i>
                                    Profile
                                </a>

                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf

                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                       href="javascript:void(0);"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Sign Out
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- =========================================================
            Sidebar
        ========================================================== --}}
        @include('layouts.sidebar')

        {{-- =========================================================
            Main Content
        ========================================================== --}}
        <div class="main-content app-content">
            <div class="container-fluid el-content-container">

                @isset($header)
                    <div class="el-page-title-block">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}

            </div>
        </div>

        {{-- =========================================================
            Footer
        ========================================================== --}}
        <footer class="footer mt-auto py-3 text-center">
            <div class="container-fluid">
                <span>
                    Copyright © <span id="year"></span>
                    <a href="javascript:void(0);">THE VAULT</a>.
                    All rights reserved.
                </span>
            </div>
        </footer>

    </div>

    {{-- =========================================================
        Mobile Sidebar Overlay
    ========================================================== --}}
    <div id="responsive-overlay" class="el-responsive-overlay"></div>

    {{-- =========================================================
        Scroll To Top
    ========================================================== --}}
    <div class="scrollToTop" id="scrollToTop">
        <span class="arrow">
            <i class="bi bi-arrow-up"></i>
        </span>
    </div>

    {{-- =========================================================
        Bootstrap JS - Local
    ========================================================== --}}
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    @yield('page_scripts')

    {{-- =========================================================
        Global Scripts
    ========================================================== --}}
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf_token"]').getAttribute('content');

        document.addEventListener('DOMContentLoaded', function () {
            const loader = document.getElementById('loader');
            const year = document.getElementById('year');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const responsiveOverlay = document.getElementById('responsive-overlay');
            const scrollToTop = document.getElementById('scrollToTop');

            if (loader) {
                setTimeout(function () {
                    loader.classList.add('loaded');
                }, 250);
            }

            if (year) {
                year.textContent = new Date().getFullYear();
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    if (window.innerWidth <= 991) {
                        document.body.classList.toggle('sidebar-open');
                    } else {
                        const html = document.documentElement;

                        if (html.getAttribute('data-toggled') === 'close') {
                            html.removeAttribute('data-toggled');
                        } else {
                            html.setAttribute('data-toggled', 'close');
                        }
                    }
                });
            }

            if (responsiveOverlay) {
                responsiveOverlay.addEventListener('click', function () {
                    document.body.classList.remove('sidebar-open');
                });
            }

            window.addEventListener('resize', function () {
                if (window.innerWidth > 991) {
                    document.body.classList.remove('sidebar-open');
                }
            });

            window.addEventListener('scroll', function () {
                if (!scrollToTop) {
                    return;
                }

                if (window.scrollY > 300) {
                    scrollToTop.classList.add('show');
                } else {
                    scrollToTop.classList.remove('show');
                }
            });

            /* =========================================================
                Server Synced Date & Time
            ========================================================== */
            const currentDateTime = document.getElementById('currentDateTime');
            const timezoneList = document.getElementById('timezoneList');

            const serverTimeUrl = "{{ route('server.time') }}";

            let serverBaseTime = null;
            let browserBaseTime = null;

            const timeZones = [
                {
                    label: 'Chennai',
                    timezone: 'Asia/Kolkata'
                },
                {
                    label: 'Muscat',
                    timezone: 'Asia/Muscat'
                },
                {
                    label: 'Berlin',
                    timezone: 'Europe/Berlin'
                },

                {
                    label: 'London',
                    timezone: 'Europe/London'
                },
                {
                    label: 'Paris',
                    timezone: 'Europe/Paris'
                },
            ];

            function getCurrentServerBasedDate() {
                if (!serverBaseTime || !browserBaseTime) {
                    return new Date();
                }

                const elapsedMilliseconds = Date.now() - browserBaseTime;

                return new Date(serverBaseTime.getTime() + elapsedMilliseconds);
            }

            function formatDateTime(date, timezone, showDate = true) {
                const options = {
                    timeZone: timezone,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };

                if (showDate) {
                    options.weekday = 'short';
                    options.day = '2-digit';
                    options.month = 'short';
                    options.year = 'numeric';
                }

                return new Intl.DateTimeFormat('en-IN', options).format(date);
            }

            function updateHeaderDateTime() {
                const currentServerDate = getCurrentServerBasedDate();

                if (currentDateTime) {
                    currentDateTime.textContent = formatDateTime(currentServerDate, 'Asia/Kolkata', true);
                }

                if (timezoneList) {
                    timezoneList.innerHTML = '';

                    timeZones.forEach(function (zone) {
                        const item = document.createElement('div');
                        item.className = 'el-timezone-item';

                        item.innerHTML = `
                            <div>
                                <div class="el-timezone-name">${zone.label}</div>
                                <div class="text-muted small">${zone.timezone}</div>
                            </div>
                            <div class="el-timezone-value">
                                ${formatDateTime(currentServerDate, zone.timezone, false)}
                            </div>
                        `;

                        timezoneList.appendChild(item);
                    });
                }
            }

            function syncServerTime() {
                fetch(serverTimeUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        serverBaseTime = new Date(data.server_time);
                        browserBaseTime = Date.now();

                        updateHeaderDateTime();
                    })
                    .catch(function () {
                        if (currentDateTime) {
                            currentDateTime.textContent = 'Unable to sync time';
                        }
                    });
            }

            syncServerTime();

            setInterval(updateHeaderDateTime, 1000);

            // Resync with Laravel server every 60 seconds
            setInterval(syncServerTime, 60000);

            if (scrollToTop) {
                scrollToTop.addEventListener('click', function () {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>
</body>
</html>