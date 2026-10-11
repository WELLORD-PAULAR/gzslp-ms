@extends('layouts.app')

@section('title', 'Reset password | Print Studio')

@section('content')
    <section class="mx-auto max-w-md">
        <a class="text-sm underline" href="{{ route('login') }}">Back to login</a>
        <h1 class="mt-4 text-2xl font-semibold">Reset your password</h1>
        <p class="mt-2 text-sm text-gray-600">Enter your account email and we will send a reset link.</p>
        @if (session('status'))<p class="mt-4 text-sm text-emerald-800" role="status">{{ session('status') }}</p>@endif
        <form class="mt-6 space-y-4" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div><label class="block text-sm font-medium" for="email">Email</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white" type="submit">Send reset link</button>
        </form>
    </section>
@endsection