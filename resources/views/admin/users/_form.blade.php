<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium" for="name">Name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $managedUser->name) }}" required maxlength="255">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="email">Email</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required maxlength="255">
        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="phone">Phone number</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{7,15}" value="{{ old('phone', $managedUser->phone) }}" placeholder="639175550101">
        @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="role">Role</label>
        <select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="role" name="role" required>
            @foreach (['customer' => 'Customer', 'employee' => 'Employee', 'admin' => 'Admin'] as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $managedUser->role ?? 'customer') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <input name="is_active" type="hidden" value="0">
        <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $managedUser->exists ? $managedUser->is_active : true))>Account can sign in</label>
        @error('is_active')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="password">{{ $managedUser->exists ? 'New password (optional)' : 'Temporary password' }}</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" @required(! $managedUser->exists)>
        @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="password_confirmation">Confirm password</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $managedUser->exists)>
    </div>
</div>