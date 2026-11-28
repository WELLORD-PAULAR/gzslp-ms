<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium" for="name">Supplier name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $supplier->name) }}" required maxlength="255">
        @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="email">Email</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="email" name="email" type="email" value="{{ old('email', $supplier->email) }}" maxlength="255">
        @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="phone">Phone <span class="font-normal text-gray-500">(digits only)</span></label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{7,15}" value="{{ old('phone', $supplier->phone) }}" placeholder="639175550101">
        <p class="mt-1 text-xs text-gray-500">Use 7 to 15 digits, without spaces or symbols.</p>
        @error('phone') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="address">Address</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="address" name="address" value="{{ old('address', $supplier->address) }}" maxlength="255">
        @error('address') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    @if ($supplier->exists)
        <input name="is_active" type="hidden" value="0">
        <label class="flex items-center gap-2 text-sm">
            <input name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $supplier->is_active))>
            Supplier is active
        </label>
    @else
        <input name="is_active" type="hidden" value="1">
    @endif

    @error('is_active') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
</div>
