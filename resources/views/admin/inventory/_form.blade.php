<div class="space-y-5">
    @if ($item->exists)<input name="lock_version" type="hidden" value="{{ $item->lock_version }}">@endif
    @error('conflict')<p class="border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
    <div><label class="block text-sm font-medium" for="name">Supply name</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="name" name="name" value="{{ old('name', $item->name) }}" required maxlength="255">@error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium" for="sku">SKU <span class="font-normal text-gray-500">(optional)</span></label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="sku" name="sku" value="{{ old('sku', $item->sku) }}" maxlength="255">@error('sku')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div><label class="block text-sm font-medium" for="category">Supply catalog</label><select class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="category" name="category" required>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category', $item->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>@endforeach</select>@error('category')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
        <div><label class="block text-sm font-medium" for="unit">Unit</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="unit" name="unit" value="{{ old('unit', $item->unit) }}" placeholder="sheet, piece, meter" maxlength="32" required>@error('unit')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
        <div><label class="block text-sm font-medium" for="reorder_level">Low-stock threshold</label><input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="reorder_level" name="reorder_level" type="number" min="0" max="999999999.999" step="0.001" value="{{ old('reorder_level', $item->reorder_level ?? '0.000') }}" required>@error('reorder_level')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    </div>
    <fieldset>
        <legend class="text-sm font-medium">Suppliers</legend>
        @if($suppliers->isEmpty())<p class="mt-2 text-sm text-gray-500">No active suppliers. <a class="underline" href="{{ route('admin.suppliers.create') }}">Add a supplier</a>.</p>@else
            <div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($suppliers as $supplier)<label class="flex items-center gap-2 text-sm"><input name="supplier_ids[]" type="checkbox" value="{{ $supplier->id }}" @checked(collect(old('supplier_ids', $item->exists ? $item->suppliers->modelKeys() : []))->contains($supplier->id))>{{ $supplier->name }}</label>@endforeach</div>
        @endif
        @error('supplier_ids')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('supplier_ids.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </fieldset>
    @if($item->exists)
        <input name="is_active" type="hidden" value="0">
        <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $item->is_active))>Supply item is active</label>
    @else
        <input name="is_active" type="hidden" value="1">
        <p class="text-sm text-gray-600">New supply items start at zero stock. Use the receive-stock action after saving.</p>
    @endif
    @error('is_active')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
</div>