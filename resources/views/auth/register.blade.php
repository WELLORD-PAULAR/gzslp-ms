<form class="mt-6 space-y-4" method="POST" action="{{ route('register.store') }}">
    @csrf

    <div>
        <label class="block text-sm font-medium" for="name">Name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
        @error('name') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="email">Email</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="phone">Phone number <span class="font-normal text-gray-500">(optional)</span></label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{7,15}" value="{{ old('phone') }}" autocomplete="tel" placeholder="639175550101">
        <p class="mt-1 text-xs text-gray-500">Use 7 to 15 digits, without spaces or symbols.</p>
        @error('phone') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="password">Password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="password_confirmation">Confirm password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
    </div>

    <button class="gz-btn gz-btn-cyan w-full" type="submit">Create account</button>
</form>

<p class="mt-6 text-sm">Already have an account? <a class="font-semibold text-[#008da5] underline" href="{{ route('login') }}">Log in</a></p>
