<form class="mt-6 space-y-4" method="POST" action="{{ route('register.store') }}">
    @csrf

    <div>
        <label class="block text-sm font-medium" for="name">Name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
        @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="email">Email</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="password">Password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="password_confirmation">Confirm password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
    </div>

    <button class="w-full rounded bg-emerald-800 px-4 py-2 font-medium text-white hover:bg-emerald-900" type="submit">Create account</button>
</form>

<p class="mt-6 text-sm">Already have an account? <a class="font-medium text-emerald-800 underline" href="{{ route('login') }}">Log in</a></p>