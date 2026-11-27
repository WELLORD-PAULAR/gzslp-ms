<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171923">
    <title>@yield('title', 'Graphic Zone | Workspace')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-gray-900">
    <header class="gz-admin-top">
        <a class="gz-brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/graphic-zone-logo.jpg') }}" alt="Graphic Zone logo">
            <span class="gz-brand-name">GRAPHIC ZONE<small>PRINTING SERVICES</small></span>
        </a>
        <div class="flex items-center gap-3 text-sm">
            <span class="hidden text-gray-500 sm:inline">Signed in as <strong class="text-gray-800">{{ auth()->user()->name }}</strong></span>
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-black tracking-wider text-gray-800">{{ auth()->user()->isAdmin() ? 'ADMIN' : 'EMPLOYEE' }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="rounded-lg border border-gray-200 px-3 py-2 font-bold text-gray-600 hover:bg-gray-50" type="submit">Log out</button>
            </form>
            <button class="gz-menu-toggle rounded-lg border border-gray-200 p-2 text-gray-700" type="button" data-menu-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open navigation menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>
    <div class="gz-admin-shell">
        <aside class="gz-sidebar hidden" id="admin-sidebar" data-menu-panel>
            <p class="gz-side-label">Workspace</p>
            <nav aria-label="Workspace navigation">
                <a class="gz-side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>▦</span> Overview</a>
                <a class="gz-side-link {{ request()->routeIs('admin.account.*') ? 'active' : '' }}" href="{{ route('admin.account.dashboard') }}"><span>◉</span> My account</a>
                <p class="gz-side-label">Shop management</p>
                <a class="gz-side-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><span>▧</span> Products</a>
                <a class="gz-side-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><span>✳</span> Services</a>
                <a class="gz-side-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}"><span>▤</span> Inventory</a>
                <a class="gz-side-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.suppliers.index') }}"><span>♧</span> Suppliers</a>
                <a class="gz-side-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><span>▣</span> Orders</a>
                @can('access-admin')
                    <p class="gz-side-label">Administration</p>
                    <a class="gz-side-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><span>▥</span> Reports</a>
                    <a class="gz-side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span>♙</span> Accounts</a>
                @endcan
                <p class="gz-side-label">Customer view</p>
                <a class="gz-side-link" href="{{ route('admin.storefront.preview') }}"><span>↗</span> Visit website</a>
            </nav>
            <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-black uppercase tracking-widest text-cyan-300">Graphic Zone</p>
                <p class="mt-2 text-xs leading-5 text-gray-400">Make every print count. Manage your shop from one place.</p>
            </div>
        </aside>
        <main class="gz-admin-main">
            @yield('content')
        </main>
    </div>
</body>
</html>
