@extends('layouts.app')

@section('title', 'My account | Graphic Zone')

@section('content')
    <section class="mb-8">
        <p class="gz-eyebrow">Customer account</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Welcome, {{ auth()->user()->name }}!</h1>
        <p class="mt-2 text-gray-500">Your Graphic Zone account, profile, and print orders in one place.</p>
    </section>
    @if (session('status'))<p class="gz-panel mb-5 border-l-4 border-cyan-500 px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
    @if (! auth()->user()->hasVerifiedEmail())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
            <p class="font-bold">Your email address is not verified.</p>
            <form class="mt-2" method="POST" action="{{ route('verification.send') }}">@csrf<button class="font-bold underline" type="submit">Resend verification email</button></form>
        </div>
    @endif
    <div class="grid gap-5 lg:grid-cols-[1fr_1.2fr]">
        <section class="gz-panel p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-black">Profile details</h2><span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-black text-cyan-900">MY PROFILE</span></div>
            <dl class="mt-6 space-y-5">
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Full name</dt><dd class="mt-1 font-bold">{{ auth()->user()->name }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Email address</dt><dd class="mt-1 font-bold">{{ auth()->user()->email }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Phone number</dt><dd class="mt-1 font-bold">{{ auth()->user()->phone ?: 'Not provided' }}</dd></div>
            </dl>
            <a class="gz-btn gz-btn-outline mt-7 w-full" href="{{ route('account.edit') }}">Edit account details</a>
        </section>
        <section class="gz-panel flex flex-col justify-between overflow-hidden p-6">
            <div>
                <p class="gz-eyebrow">Your print journey</p>
                <h2 class="mt-2 text-2xl font-black">Keep your projects moving.</h2>
                <p class="mt-3 max-w-md leading-7 text-gray-500">Check the status of your orders, review order details, and stay up to date with your printing requests.</p>
            </div>
            <div class="mt-8 rounded-xl bg-[#171923] p-5 text-white">
                <p class="text-xs font-black uppercase tracking-[.18em] text-cyan-300">Order center</p>
                <p class="mt-2 text-lg font-extrabold">Your orders, all in one place.</p>
                <a class="gz-btn gz-btn-primary mt-4" href="{{ route('orders.index') }}">View my orders →</a>
            </div>
        </section>
    </div>
@endsection
