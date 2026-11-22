@extends('layouts.admin')

@section('title', 'My account | Graphic Zone')

@section('content')
    <section class="mb-8">
        <p class="gz-eyebrow">{{ $user->isAdmin() ? 'Administrator' : 'Employee' }} account</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Welcome, {{ $user->name }}!</h1>
        <p class="mt-2 text-gray-500">Your profile details and sign-in settings in one place.</p>
    </section>
    @if (session('status'))<p class="gz-panel mb-5 border-l-4 border-cyan-500 px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
    @if (! $user->hasVerifiedEmail() || ! $user->hasVerifiedPhone())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
            <p class="font-bold">Verify your contact details.</p>
            <p class="mt-1">Use the six-digit code we sent you to confirm each contact.</p>
            <a class="mt-2 inline-block font-bold underline" href="{{ route('verification.notice') }}">Enter your verification code →</a>
        </div>
    @endif
    <div class="grid gap-5 lg:grid-cols-[1fr_1.2fr]">
        <section class="gz-panel p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-black">Profile details</h2><span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-black text-cyan-900">MY PROFILE</span></div>
            <dl class="mt-6 space-y-5">
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Full name</dt><dd class="mt-1 font-bold">{{ $user->name }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Email address</dt><dd class="mt-1 font-bold">{{ $user->email }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Phone number</dt><dd class="mt-1 font-bold">{{ $user->phone ?: 'Not provided' }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Email verification</dt><dd class="mt-1 font-bold">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}</dd></div>
                <div><dt class="text-xs font-black uppercase tracking-wider text-gray-400">Phone verification</dt><dd class="mt-1 font-bold">{{ $user->hasVerifiedPhone() ? 'Verified' : ($user->phone ? 'Pending' : 'Not applicable') }}</dd></div>
            </dl>
            <a class="gz-btn gz-btn-outline mt-7 w-full" href="{{ route('admin.account.edit') }}">Edit account details</a>
        </section>
        <section class="gz-panel flex flex-col justify-between overflow-hidden p-6">
            <div>
                <p class="gz-eyebrow">Sign-in security</p>
                <h2 class="mt-2 text-2xl font-black">Keep your account secure.</h2>
                <p class="mt-3 max-w-md leading-7 text-gray-500">Update your password regularly to protect access to the workspace and shop operations.</p>
            </div>
            <div class="mt-8 rounded-xl bg-[#171923] p-5 text-white">
                <p class="text-xs font-black uppercase tracking-[.18em] text-cyan-300">Password</p>
                <p class="mt-2 text-lg font-extrabold">Change it whenever you need to.</p>
                <a class="gz-btn gz-btn-primary mt-4" href="{{ route('admin.account.edit') }}#change-password">Change password →</a>
            </div>
        </section>
    </div>
@endsection
