@extends('layouts.admin')

@section('title', (auth()->user()->isAdmin() ? 'Admin' : 'Employee').' dashboard | Graphic Zone')

@section('content')
    <section class="mb-8">
        <p class="gz-eyebrow">{{ auth()->user()->isAdmin() ? 'Administrator workspace' : 'Employee workspace' }}</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ auth()->user()->isAdmin() ? 'Shop overview' : 'Welcome to your workspace' }}</h1>
                <p class="mt-2 text-gray-500">Good day, {{ auth()->user()->name }}. {{ auth()->user()->isAdmin() ? 'Manage your shop operations from one place.' : 'Your shop tools and daily tasks are ready.' }}</p>
            </div>
            <span class="rounded-full bg-cyan-100 px-4 py-2 text-xs font-black uppercase tracking-wider text-cyan-900">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Employee access' }}</span>
        </div>
    </section>
    <section class="gz-splash mb-8 overflow-hidden rounded-2xl bg-[#171923] p-6 text-white sm:p-8">
        <div class="relative z-10 max-w-2xl">
            <p class="text-xs font-black uppercase tracking-[.2em] text-cyan-300">Graphic Zone / Printing Services</p>
            <h2 class="mt-3 text-2xl font-black sm:text-3xl">Make every order a great impression.</h2>
            <p class="mt-2 text-sm leading-6 text-gray-300">Use the workspace shortcuts below to manage products, services, supplies, and customer orders.</p>
        </div>
    </section>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.products.index') }}"><span class="text-2xl">▧</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Products</h2><p class="mt-1 text-sm text-gray-500">Manage your printed product catalog.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open products →</span></a>
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.services.index') }}"><span class="text-2xl text-pink-600">✳</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Services</h2><p class="mt-1 text-sm text-gray-500">Maintain the services offered to customers.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open services →</span></a>
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.inventory.index') }}"><span class="text-2xl">▤</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Inventory</h2><p class="mt-1 text-sm text-gray-500">Track materials and stock movements.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open inventory →</span></a>
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.suppliers.index') }}"><span class="text-2xl">♧</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Suppliers</h2><p class="mt-1 text-sm text-gray-500">Keep supplier information organized.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open suppliers →</span></a>
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.orders.index') }}"><span class="text-2xl text-pink-600">▣</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Orders</h2><p class="mt-1 text-sm text-gray-500">{{ auth()->user()->isAdmin() ? 'Review and process customer orders.' : 'Review customer order details.' }}</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open orders →</span></a>
        <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.account.dashboard') }}"><span class="text-2xl text-cyan-700">◉</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">My account</h2><p class="mt-1 text-sm text-gray-500">Update your profile details and password.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Open account →</span></a>
        @can('access-admin')
            <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.reports.index') }}"><span class="text-2xl">▥</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Reports</h2><p class="mt-1 text-sm text-gray-500">Review shop activity and reporting.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">View reports →</span></a>
            <a class="gz-panel group p-5 hover:-translate-y-1 hover:shadow-lg" href="{{ route('admin.users.index') }}"><span class="text-2xl">♙</span><h2 class="mt-4 text-lg font-black group-hover:text-cyan-800">Accounts</h2><p class="mt-1 text-sm text-gray-500">Manage administrator, employee, and customer accounts.</p><span class="mt-4 inline-block text-sm font-extrabold text-cyan-800">Manage accounts →</span></a>
        @endcan
    </section>
@endsection
