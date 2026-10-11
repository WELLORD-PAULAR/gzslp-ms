@extends('layouts.app')

@section('title', 'Your account | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-medium text-gray-500">CUSTOMER ACCOUNT</p>
        <h1 class="mt-2 text-3xl font-semibold">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-3 text-gray-600">Manage your account and keep track of your print orders.</p>
        @if (session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
        @if (! auth()->user()->hasVerifiedEmail())
            <div class="mt-5 border-l-4 border-amber-600 bg-white px-4 py-3 text-sm">
                <p>Your email address is not verified.</p>
                <form class="mt-2" method="POST" action="{{ route('verification.send') }}">@csrf<button class="font-medium underline" type="submit">Resend verification email</button></form>
            </div>
        @endif
        <dl class="mt-7 grid max-w-xl gap-3 border-y border-gray-200 py-5 sm:grid-cols-2">
            <div><dt class="text-sm text-gray-500">Email</dt><dd class="mt-1 font-medium">{{ auth()->user()->email }}</dd></div>
            <div><dt class="text-sm text-gray-500">Phone</dt><dd class="mt-1 font-medium">{{ auth()->user()->phone ?: 'Not provided' }}</dd></div>
        </dl>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="inline-flex rounded border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-white" href="{{ route('account.edit') }}">Edit account</a>
            <a class="inline-flex rounded border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-white" href="{{ route('orders.index') }}">View your orders</a>
        </div>
    </section>
@endsection