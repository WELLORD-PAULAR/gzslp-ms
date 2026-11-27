<form class="mt-6 space-y-4" method="POST" action="{{ route('login.store') }}">
    @csrf

    <div>
        <label class="block text-sm font-medium" for="email">Email</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="password">Password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input name="remember" type="checkbox" value="1">
        Remember me
    </label>

    <button class="gz-btn gz-btn-cyan w-full" type="submit">Log in</button>
</form>

<p class="mt-6 text-sm">New here? <a class="font-semibold text-[#008da5] underline" href="{{ route('register') }}">Create an account</a></p>
<p class="mt-2 text-sm"><a class="text-gray-600 underline" href="{{ route('password.request') }}">Forgot your password?</a></p>