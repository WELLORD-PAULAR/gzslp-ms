<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin | Print Studio')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <header class="bg-gray-900 text-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-4">
            <a class="font-semibold" href="{{ route('admin.dashboard') }}">Print Studio <span class="ml-2 text-xs font-normal text-gray-300">{{ auth()->user()->isAdmin() ? 'ADMIN' : 'EMPLOYEE' }}</span></a>
            <div class="flex gap-5 text-sm">
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.services.index') }}">Services</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                @can('access-admin')<a href="{{ route('admin.reports.index') }}">Reports</a>@endcan
                @can('access-admin')<a href="{{ route('admin.users.index') }}">Accounts</a>@endcan
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm underline" type="submit">Log out</button>
            </form>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
        @yield('content')
    </main>
</body>
</html>