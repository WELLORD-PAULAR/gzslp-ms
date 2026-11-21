<div class="space-y-6">
    @if($variant->exists)<input name="lock_version" type="hidden" value="{{ $variant->lock_version }}">@endif
    @error('conflict')<p class="border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
    <div><label class="block text-sm font-medium" for="name">Option name</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $variant->name) }}" required maxlength="255" placeholder="Large / Black">@error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium" for="sku">SKU <span class="font-normal text-gray-500">(optional)</span></label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="sku" name="sku" value="{{ old('sku', $variant->sku) }}" maxlength="255">@error('sku')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium" for="price">Price per finished unit</label><input class="mt-1 w-full max-w-sm rounded border border-gray-300 px-3 py-2" id="price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('price', $variant->price) }}" required>@error('price')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>

    <fieldset><legend class="text-sm font-medium">Customer-selectable details</legend><p class="mt-1 text-xs text-gray-500">Only fill options relevant to this product.</p><div class="mt-3 grid gap-4 sm:grid-cols-2">
        @foreach($optionNames as $key => $label)
            <div><label class="block text-sm text-gray-600" for="option-{{ $key }}">{{ $label }}</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ old('options.'.$key, $variant->options[$key] ?? '') }}" maxlength="80"></div>
        @endforeach
    </div>@error('options')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror @error('options.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</fieldset>

    <fieldset><legend class="text-sm font-medium">Raw supplies used per finished unit</legend><p class="mt-1 text-xs text-gray-500">For example, one blank shirt per shirt, or 0.5 meter of tarpaulin per printed unit.</p><div class="mt-3 divide-y divide-gray-200 border-y border-gray-200">
        @forelse($inventoryItems as $inventoryItem)
            <div class="grid gap-2 py-3 sm:grid-cols-[1fr_10rem] sm:items-center"><label class="text-sm" for="supply-{{ $inventoryItem->id }}">{{ $inventoryItem->name }} <span class="text-gray-500">({{ $inventoryItem->unit }}; on hand {{ number_format((float) $inventoryItem->quantity_on_hand, 3) }})</span></label><input class="w-full rounded border border-gray-300 px-3 py-2" id="supply-{{ $inventoryItem->id }}" name="supplies[{{ $inventoryItem->id }}]" type="number" min="0" max="999999999.999" step="0.001" value="{{ old('supplies.'.$inventoryItem->id, $variant->exists ? $variant->supplies->find($inventoryItem->id)?->pivot->quantity_per_unit : '') }}" placeholder="Not used"></div>
        @empty
            <p class="py-4 text-sm text-gray-500">No active supply items exist. Add supplies before defining a recipe.</p>
        @endforelse
    </div>@error('supplies')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror @error('supplies.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</fieldset>

    @if($variant->exists)
        <input name="is_active" type="hidden" value="0"><label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $variant->is_active))>Available for customers to order</label>
    @endif
</div>