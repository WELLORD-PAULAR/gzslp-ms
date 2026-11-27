@extends('layouts.app')

@section('title', 'Verify email | Graphic Zone')

@section('content')
    <section class="mx-auto max-w-xl">
        <p class="text-sm font-semibold uppercase text-[#008da5]">Account security</p>
        <h1 class="mt-2 text-3xl font-semibold">Verify your email</h1>
        <p class="mt-4 text-gray-600">We sent a verification link to {{ auth()->user()->email }}. Use that link to verify your account.</p>
        @if (session('status'))<p class="mt-4 text-sm text-[#008da5]" role="status">{{ session('status') }}</p>@endif
        <form class="mt-6" method="POST" action="{{ route('verification.send') }}">@csrf<button class="gz-btn gz-btn-cyan" type="submit">Resend verification email</button></form>
    </section>
@endsection