@extends('layouts.admin')

@section('title', 'Edit account | Graphic Zone')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('admin.account.dashboard') }}">Back to my account</a>
        <p class="mt-4 text-sm font-semibold uppercase text-cyan-800">Account settings</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight">Personal details</h1>

        @if (session('status'))<p class="gz-panel mt-5 border-l-4 border-cyan-500 px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif

<<<<<<< HEAD
        <form class="mt-6 space-y-5 border-y border-gray-200 py-6" method="POST" action="{{ route('admin.account.update') }}" onsubmit="return window.confirm('Save these profile changes?')">
=======
        <form class="mt-6 space-y-5 border-y border-gray-200 py-6" method="POST" action="{{ route('admin.account.update') }}" onsubmit="return window.confirm('Save your account details?')">
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
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
<<<<<<< HEAD
                <label class="block text-sm font-medium" for="phone">Phone number <span class="font-normal text-gray-500">(digits only)</span></label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="number" inputmode="numeric" min="1000000" max="999999999999999" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                <p class="mt-1 text-xs text-gray-500">Use 7 to 15 digits, without spaces or symbols.</p>
                @error('phone')<p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
=======
                <label class="block text-sm font-medium" for="phone">Phone number</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{7,15}" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="639175550101">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
            </div>
            <button class="gz-btn gz-btn-primary" type="submit">Save profile</button>
        </form>

        <h2 class="mt-10 text-xl font-semibold" id="change-password">Change password</h2>
        <form class="mt-5 space-y-5 border-y border-gray-200 py-6" method="POST" action="{{ route('admin.account.password.update') }}" onsubmit="return window.confirm('Change your password now?')">
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
