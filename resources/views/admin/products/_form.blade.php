<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium" for="name">Product name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
        @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="description">Description</label>
        <textarea class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="description" name="description" rows="4" maxlength="10000">{{ old('description', $product->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="price">Price</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
        @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-medium" for="paper_size">Paper size</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_size" name="paper_size" value="{{ old('paper_size', $product->paper_size) }}" placeholder="A4, A5">
            @error('paper_size') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium" for="paper_weight">Paper weight</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_weight" name="paper_weight" value="{{ old('paper_weight', $product->paper_weight) }}" placeholder="150 gsm">
            @error('paper_weight') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium" for="paper_finish">Paper finish</label>
            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_finish" name="paper_finish" value="{{ old('paper_finish', $product->paper_finish) }}" placeholder="Matte, gloss">
            @error('paper_finish') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>
    <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>Visible in the customer catalog</label>
    @error('is_active') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
</div>