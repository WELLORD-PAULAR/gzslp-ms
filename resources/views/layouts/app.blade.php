<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Studio')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <nav aria-label="Main navigation" class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-5 gap-y-3 px-4 py-4 sm:px-6">
            <a class="shrink-0 text-lg font-semibold" href="{{ route('home') }}">Print Studio</a>
            <div class="order-3 flex basis-full justify-between gap-4 text-sm font-medium sm:order-none sm:flex-1 sm:basis-auto sm:justify-center sm:gap-6">
                <a class="{{ request()->routeIs('home') ? 'text-emerald-800' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                @auth
                    <a class="{{ request()->routeIs('products.*') ? 'text-emerald-800' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>Products</a>
                    <a class="{{ request()->routeIs('services') ? 'text-emerald-800' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('services') }}" @if(request()->routeIs('services')) aria-current="page" @endif>Services</a>
                    <a class="{{ request()->routeIs('cart') ? 'text-emerald-800' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('cart') }}" @if(request()->routeIs('cart')) aria-current="page" @endif>Cart <span class="ml-1 text-xs text-gray-500">0</span></a>
                @endauth
            </div>
            <div class="flex shrink-0 items-center gap-4 text-sm">
                @auth
                    <a class="text-gray-600 hover:text-gray-900" href="{{ route('account.dashboard') }}">Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="underline" type="submit">Log out</button>
                    </form>
                @else
                    <a class="underline" href="{{ route('login') }}">Log in</a>
                    <a class="underline" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-12">
        @yield('content')
    </main>
</body>
</html>