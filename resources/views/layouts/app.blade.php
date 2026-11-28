@php($isPreviewingStorefront = auth()->user()?->isPreviewingStorefront() ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#00cfe8">
    <title>@yield('title', 'Graphic Zone | Printing Services')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-gray-900">
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
<<<<<<< HEAD
        <nav aria-label="Main navigation" class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3 sm:px-6 lg:px-8">
            <a class="gz-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/graphic-zone-logo.jpg') }}" alt="Graphic Zone Printing Services logo">
                <span class="gz-brand-name">GRAPHIC ZONE<small>PRINTING SERVICES</small></span>
            </a>
            <button class="gz-menu-toggle" type="button" data-nav-toggle data-nav-target="#primary-navigation" aria-controls="primary-navigation" aria-expanded="false" aria-label="Open navigation menu">
                <span class="gz-menu-icon" aria-hidden="true">☰</span>
                <span class="gz-menu-icon-close" aria-hidden="true">×</span>
            </button>
            <div class="gz-mobile-nav order-3 w-full items-center justify-between gap-3 sm:order-none sm:w-auto sm:flex-1" id="primary-navigation" data-mobile-nav>
                <div class="flex flex-wrap items-center justify-center gap-1">
=======
        <nav aria-label="Main navigation" class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <a class="gz-brand" href="{{ route('home') }}">
                    <img src="{{ asset('images/graphic-zone-logo.jpg') }}" alt="Graphic Zone Printing Services logo">
                    <span class="gz-brand-name">GRAPHIC ZONE<small>PRINTING SERVICES</small></span>
                </a>
                <div class="hidden items-center gap-1 sm:flex">
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
                    <a class="gz-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    @auth
                        @if (! auth()->user()->isStaff() || $isPreviewingStorefront)
                            <a class="gz-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                            <a class="gz-nav-link {{ request()->routeIs('services*') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                            <a class="gz-nav-link {{ request()->routeIs('cart') ? 'active' : '' }}" href="{{ route('cart') }}">Cart <span class="ml-1 rounded-full bg-yellow-200 px-2 py-0.5 text-xs text-gray-900">{{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }}</span></a>
                        @endif
                    @endauth
                </div>
<<<<<<< HEAD
                <div class="flex shrink-0 items-center gap-3 text-sm font-semibold">
=======
                <div class="hidden shrink-0 items-center gap-3 text-sm font-semibold sm:flex">
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
                    @auth
                        @if ($isPreviewingStorefront)
                            <span class="hidden rounded-full bg-cyan-100 px-3 py-1 text-xs font-black tracking-wider text-cyan-900 md:inline">CUSTOMER PREVIEW</span>
                            <a class="gz-btn gz-btn-cyan" href="{{ route('admin.storefront.exit') }}">Back to workspace</a>
                        @elseif (auth()->user()->isStaff())
                            <a class="gz-btn gz-btn-cyan" href="{{ route('admin.dashboard') }}">Workspace</a>
                        @else
                            <a class="text-gray-600 hover:text-cyan-800" href="{{ route('account.dashboard') }}">My account</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-100" type="submit">Log out</button>
                        </form>
                    @else
                        <a class="font-bold text-gray-700 hover:text-cyan-800" href="{{ route('login') }}">Log in</a>
                        <a class="gz-btn gz-btn-primary" href="{{ route('register') }}">Create account</a>
                    @endauth
                </div>
<<<<<<< HEAD
=======
                <button class="rounded-lg border border-gray-200 p-2 text-gray-700 sm:hidden" type="button" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false" aria-label="Open navigation menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="hidden pb-3 sm:hidden" id="mobile-menu" data-menu-panel>
                <div class="flex flex-col gap-1 border-t border-gray-100 pt-3">
                    <a class="gz-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    @auth
                        @if (! auth()->user()->isStaff() || $isPreviewingStorefront)
                            <a class="gz-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                            <a class="gz-nav-link {{ request()->routeIs('services*') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                            <a class="gz-nav-link {{ request()->routeIs('cart') ? 'active' : '' }}" href="{{ route('cart') }}">Cart ({{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }})</a>
                        @endif
                        @if ($isPreviewingStorefront)
                            <a class="gz-nav-link" href="{{ route('admin.storefront.exit') }}">Back to workspace</a>
                        @elseif (auth()->user()->isStaff())
                            <a class="gz-nav-link" href="{{ route('admin.dashboard') }}">Workspace</a>
                        @else
                            <a class="gz-nav-link" href="{{ route('account.dashboard') }}">My account</a>
                        @endif
                        <form class="pt-2" method="POST" action="{{ route('logout') }}">@csrf
                            <button class="gz-nav-link w-full text-left" type="submit">Log out</button>
                        </form>
                    @else
                        <a class="gz-nav-link" href="{{ route('login') }}">Log in</a>
                        <a class="gz-nav-link" href="{{ route('register') }}">Create account</a>
                    @endauth
                </div>
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-7 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <span class="font-extrabold tracking-wide text-gray-800">GRAPHIC ZONE <span class="text-cyan-700">●</span> PRINTING SERVICES</span>
            <span>Bring your ideas to print.</span>
        </div>
    </footer>
</body>
</html>
