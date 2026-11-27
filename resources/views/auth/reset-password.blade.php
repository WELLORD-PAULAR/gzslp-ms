@extends('layouts.app')

@section('title', 'Choose a new password | Graphic Zone')

@section('content')
    <section class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold">Choose a new password</h1>
        <form class="mt-6 space-y-4" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input name="token" type="hidden" value="{{ $token }}">
            <div><label class="block text-sm font-medium" for="email">Email</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>@error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium" for="password">New password</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium" for="password_confirmation">Confirm new password</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            <button class="gz-btn gz-btn-cyan" type="submit">Reset password</button>
        </form>
    </section>
@endsection