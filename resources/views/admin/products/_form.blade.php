<div class="space-y-5">
    @if ($product->exists)
        <input name="lock_version" type="hidden" value="{{ $product->lock_version }}">
    @endif

    @error('conflict')
        <p class="border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
    @enderror

    <div>
        <label class="block text-sm font-medium" for="name">Product name</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
        @error('name')
            <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="category">Product catalog</label>
        <select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="category" name="category" required>
            @foreach (['paper' => 'Paper product', 'mug' => 'Mug', 'shirt' => 'Shirt / apparel', 'tarpaulin' => 'Tarpaulin', 'other' => 'Other / custom product'] as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $product->category ?? 'paper') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-sm text-gray-500">For mugs, shirts, and custom products, define sizes, colors, and other options after saving.</p>
        @error('category')
            <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="description">Description</label>
        <textarea class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="description" name="description" rows="4" maxlength="10000">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="image">
            Product image
            <span class="font-normal text-gray-500">(JPG, PNG or WebP, up to 5 MB)</span>
        </label>

        @if ($product->image_path)
            <img class="mt-3 aspect-[4/3] w-48 rounded border border-gray-200 object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="Current image for {{ $product->name }}">
        @endif

        <input class="mt-2 block w-full text-sm" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">

        <p class="mt-1 text-xs text-gray-500">If an upload is rejected, the exact reason appears below.</p>

        @error('image')
            <div class="mt-2 rounded border-l-4 border-red-700 bg-white px-3 py-2 text-sm text-red-800" role="alert">
                <p class="font-semibold">The image was not uploaded.</p>
                <p>{{ $message }}</p>
            </div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="price">Price</label>
        <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
        @error('price')
            <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="rounded border border-gray-200 p-4">
        <legend class="px-1 text-sm font-medium">Paper specifications <span class="font-normal text-gray-500">(optional; paper products only)</span></legend>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium" for="paper_size">Paper size</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_size" name="paper_size" value="{{ old('paper_size', $product->paper_size) }}" placeholder="A4, A5">
                @error('paper_size')
                    <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium" for="paper_weight">Paper weight</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_weight" name="paper_weight" value="{{ old('paper_weight', $product->paper_weight) }}" placeholder="150 gsm">
                @error('paper_weight')
                    <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium" for="paper_finish">Paper finish</label>
                <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="paper_finish" name="paper_finish" value="{{ old('paper_finish', $product->paper_finish) }}" placeholder="Matte, gloss">
                @error('paper_finish')
                    <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>

    <label class="flex items-center gap-2 text-sm">
        <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
        Visible in the customer catalog
    </label>

    @error('is_active')
        <p class="text-sm text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
