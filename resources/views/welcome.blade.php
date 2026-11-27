@extends('layouts.app')

@section('title', 'Graphic Zone | Printing Services')

@section('content')
    <section class="gz-splash relative grid items-center gap-10 overflow-hidden rounded-[2rem] bg-[#171923] px-6 py-12 text-white shadow-xl sm:px-10 lg:grid-cols-[1.1fr_.9fr] lg:px-16 lg:py-16">
        <div class="relative z-10">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[.2em] text-cyan-200">
                <span class="gz-dot"></span> Your ideas. Our prints.
            </div>
            <h1 class="max-w-3xl text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                Make your ideas <span class="text-[#00d7ed]">stand out.</span>
                <span class="block text-[#ffe52b]">We'll print them.</span>
            </h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-gray-300 sm:text-lg">From everyday paper essentials to custom-made designs, bring your vision to life with Graphic Zone Printing Services.</p>
            <div class="mt-8 flex flex-wrap gap-4">
                @auth
                    <a class="gz-btn gz-btn-primary" href="{{ route('products.index') }}">Explore products <span aria-hidden="true">→</span></a>
                    <a class="gz-btn border border-white/25 bg-white/10 text-white hover:bg-white/15" href="{{ route('services') }}">View services</a>
                @else
                    <a class="gz-btn gz-btn-primary" href="{{ route('login') }}">Sign in to browse <span aria-hidden="true">→</span></a>
                    <a class="gz-btn border border-white/25 bg-white/10 text-white hover:bg-white/15" href="{{ route('register') }}">Create an account</a>
                    <p class="w-full text-sm text-gray-400">An account is required to browse the catalog, request quotes, and place orders.</p>
                @endauth
            </div>
            <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                <span><span class="text-cyan-300">●</span> Custom printing</span>
                <span><span class="text-[#ed168b]">●</span> Ready-made designs</span>
                <span><span class="text-[#ffe52b]">●</span> Made for your ideas</span>
            </div>
        </div>
        <div class="relative z-10 mx-auto w-full max-w-md">
            <div class="absolute -right-5 -top-5 h-24 w-24 rounded-full bg-[#ed168b] blur-2xl opacity-70"></div>
            <div class="absolute -bottom-5 -left-5 h-28 w-28 rounded-full bg-[#00cfe8] blur-2xl opacity-70"></div>
            <div class="relative rounded-[1.7rem] border border-white/15 bg-white p-3 shadow-2xl">
                <img class="aspect-square w-full rounded-[1.2rem] object-contain" src="{{ asset('images/graphic-zone-logo.jpg') }}" alt="Graphic Zone Printing Services">
                <div class="flex items-center justify-between px-3 py-3 text-gray-900">
                    <div><p class="text-xs font-black uppercase tracking-[.18em] text-gray-400">Graphic Zone</p><p class="mt-1 text-lg font-black">Print your creativity.</p></div>
                    <a class="rounded-full bg-[#ffe52b] px-3 py-2 text-xs font-black" href="{{ route('login') }}">SIGN IN</a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14 sm:py-16">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div><p class="gz-eyebrow">What can we make?</p><h2 class="mt-2 text-3xl font-black tracking-tight">Your project, your way.</h2></div>
            <p class="max-w-md text-sm leading-6 text-gray-500">Choose a ready-made product or bring your own artwork. We'll help turn the idea into something you can hold.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <article class="gz-panel relative overflow-hidden p-7 sm:p-9">
                <div class="absolute right-0 top-0 h-28 w-28 rounded-bl-[5rem] bg-cyan-100"></div>
                <p class="gz-eyebrow relative">01 / Custom</p>
                <h3 class="relative mt-3 text-2xl font-black">Bring your own design.</h3>
                <p class="relative mt-3 max-w-md leading-7 text-gray-600">Choose a paper product and provide your artwork or design brief for a personalized print.</p>
                <span class="mt-6 inline-flex rounded-full bg-cyan-50 px-4 py-2 text-sm font-extrabold text-cyan-800">Made around your idea</span>
            </article>
            <article class="gz-panel relative overflow-hidden p-7 sm:p-9">
                <div class="absolute right-0 top-0 h-28 w-28 rounded-bl-[5rem] bg-pink-100"></div>
                <p class="gz-eyebrow relative" style="color:var(--gz-magenta)">02 / Ready-made</p>
                <h3 class="relative mt-3 text-2xl font-black">Pick a design and print.</h3>
                <p class="relative mt-3 max-w-md leading-7 text-gray-600">Browse available designs and select the print options that fit your project.</p>
                <span class="mt-6 inline-flex rounded-full bg-pink-50 px-4 py-2 text-sm font-extrabold text-pink-800">Simple and convenient</span>
            </article>
        </div>
    </section>

    <section class="mb-8 rounded-2xl bg-[#ffe52b] px-6 py-8 sm:flex sm:items-center sm:justify-between sm:px-9">
        <div><p class="text-xs font-black uppercase tracking-[.18em] text-gray-700">Ready when you are</p><h2 class="mt-2 text-2xl font-black text-gray-900">Let's bring your next idea to life.</h2></div>
        @guest <a class="gz-btn mt-5 bg-[#171923] text-white hover:bg-gray-800 sm:mt-0" href="{{ route('register') }}">Join Graphic Zone <span aria-hidden="true">→</span></a> @endguest
        @auth <a class="gz-btn mt-5 bg-[#171923] text-white hover:bg-gray-800 sm:mt-0" href="{{ route('products.index') }}">Browse the catalog <span aria-hidden="true">→</span></a> @endauth
    </section>

    @isset($authModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-950/70 p-4" data-auth-overlay data-close-url="{{ route('home') }}">
            <a class="absolute inset-0" href="{{ route('home') }}" aria-label="Close dialog"></a>
            <section class="relative z-10 my-auto w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="auth-dialog-title" tabindex="-1">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="gz-eyebrow">Graphic Zone account</p><h2 class="mt-2 text-2xl font-black" id="auth-dialog-title">{{ $authModal === 'login' ? 'Welcome back' : 'Create your account' }}</h2></div>
                    <a class="rounded px-2 text-2xl leading-none text-gray-500 hover:bg-gray-100" href="{{ route('home') }}" aria-label="Close dialog">&times;</a>
                </div>
                @include('auth.'.$authModal)
            </section>
        </div>
    @endisset
@endsection
