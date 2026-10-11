@extends('layouts.app')

@section('title', 'Edit account | Print Studio')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('account.dashboard') }}">Back to account</a>
        <p class="mt-4 text-sm font-semibold uppercase text-emerald-800">Account settings</p>
        <h1 class="mt-2 text-3xl font-semibold">Personal details</h1>

        <form class="mt-6 space-y-5 border-y border-gray-200 py-6" method="POST" action="{{ route('account.update') }}">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium" for="name">Name</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required maxlength="255">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="email">Email</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required maxlength="255">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="phone">Phone number</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" maxlength="25">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <button class="rounded bg-emerald-800 px-4 py-2 text-sm font-medium text-white" type="submit">Save profile</button>
        </form>

        <h2 class="mt-10 text-xl font-semibold">Change password</h2>
        <form class="mt-5 space-y-5 border-y border-gray-200 py-6" method="POST" action="{{ route('account.password.update') }}">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium" for="current_password">Current password</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                @error('current_password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="password">New password</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="password_confirmation">Confirm new password</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="rounded border border-gray-300 px-4 py-2 text-sm font-medium" type="submit">Update password</button>
        </form>
    </section>
@endsection