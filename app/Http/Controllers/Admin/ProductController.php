<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.products.create', ['product' => new Product]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_path'] = $request->file('image')?->store('products', 'public');

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $expectedVersion = (int) $data['lock_version'];
        unset($data['lock_version']);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $newImagePath = $request->file('image')?->store('products', 'public');

        if ($newImagePath !== null) {
            $data['image_path'] = $newImagePath;
        }

        $updated = Product::query()
            ->whereKey($product->id)
            ->where('lock_version', $expectedVersion)
            ->update([...$data, 'lock_version' => $expectedVersion + 1, 'updated_at' => now()]);

        if ($updated !== 1) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw ValidationException::withMessages([
                'conflict' => 'This product was changed by another staff member. Reload it and reapply your changes.',
            ]);
        }

        if ($newImagePath !== null && $product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }
}
