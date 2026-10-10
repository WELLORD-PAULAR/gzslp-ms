<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium" for="name">Service name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $service->name) }}" required maxlength="255">
        @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="description">Description</label>
        <textarea class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="description" name="description" rows="4" maxlength="10000">{{ old('description', $service->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="starting_price">Starting price (optional)</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="starting_price" name="starting_price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('starting_price', $service->starting_price) }}">
        @error('starting_price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $service->exists ? $service->is_active : true))>Visible to customers</label>
    @error('is_active') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
</div>