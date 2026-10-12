<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class ProductVariantController extends Controller
{
    public function index(Product $product): View
    {
        $variants = $product->variants()->withCount('supplies')->orderByDesc('is_default')->orderBy('name')->get();

        return view('admin.products.variants.index', compact('product', 'variants'));
    }

    public function create(Product $product): View
    {
        return view('admin.products.variants.create', [
            'product' => $product,
            'variant' => new ProductVariant(['options' => []]),
            'inventoryItems' => InventoryItem::where('is_active', true)->orderBy('name')->get(),
            'optionNames' => $this->optionNames($product),
        ]);
    }

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $options = $this->cleanOptions($data['options'] ?? []);
        $supplies = $this->cleanSupplies($data['supplies'] ?? []);

        $variant = $product->variants()->create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?: null,
            'options' => $options,
            'price' => $data['price'],
            'is_default' => false,
            'is_active' => true,
        ]);
        $variant->supplies()->sync($supplies);

        return redirect()->route('admin.products.variants.index', $product)->with('status', 'Product variant created.');
    }

    public function edit(Product $product, ProductVariant $productVariant): View
    {
        $this->ensureVariantBelongsToProduct($product, $productVariant);

        return view('admin.products.variants.edit', [
            'product' => $product,
            'variant' => $productVariant->load('supplies'),
            'inventoryItems' => InventoryItem::where('is_active', true)->orderBy('name')->get(),
            'optionNames' => $this->optionNames($product),
        ]);
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $productVariant): RedirectResponse
    {
        $this->ensureVariantBelongsToProduct($product, $productVariant);
        $data = $request->validated();
        $expectedVersion = (int) $data['lock_version'];
        $data['sku'] = $data['sku'] ?? null;
        $options = $this->cleanOptions($data['options'] ?? []);
        $supplies = $this->cleanSupplies($data['supplies'] ?? []);

        $updated = ProductVariant::query()
            ->whereKey($productVariant->id)
            ->where('product_id', $product->id)
            ->where('lock_version', $expectedVersion)
            ->update([
                'name' => $data['name'],
                'sku' => $data['sku'] ?: null,
                'options' => json_encode($options),
                'price' => $data['price'],
                'is_active' => $request->boolean('is_active'),
                'lock_version' => $expectedVersion + 1,
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            throw ValidationException::withMessages([
                'conflict' => 'This variant was changed by another staff member. Reload it and reapply your changes.',
            ]);
        }

        DB::table('product_variant_supplies')->where('product_variant_id', $productVariant->id)->delete();
        $productVariant->supplies()->sync($supplies);

        if ($productVariant->is_default) {
            $product->update(['price' => $data['price']]);
        }

        return redirect()->route('admin.products.variants.index', $product)->with('status', 'Product variant updated.');
    }

    private function optionNames(Product $product): array
    {
        return match ($product->category) {
            'shirt' => ['size' => 'Size', 'color' => 'Color', 'material' => 'Fabric', 'print_placement' => 'Print placement'],
            'mug' => ['capacity' => 'Capacity', 'color' => 'Color', 'style' => 'Style'],
            'tarpaulin' => ['dimensions' => 'Dimensions', 'thickness' => 'Thickness', 'finish' => 'Finish', 'hem_grommets' => 'Hem / grommet option'],
            'paper' => ['size' => 'Size', 'weight' => 'Weight', 'finish' => 'Finish'],
            default => ['size' => 'Size', 'color' => 'Color', 'material' => 'Material', 'finish' => 'Finish'],
        };
    }

    private function cleanOptions(array $options): array
    {
        return collect($options)
            ->mapWithKeys(fn ($value, $key) => [trim((string) $key) => trim((string) $value)])
            ->filter(fn (string $value): bool => $value !== '')
            ->all();
    }

    private function cleanSupplies(array $supplies): array
    {
        return collect($supplies)
            ->filter(fn ($quantity): bool => $quantity !== null && $quantity !== '')
            ->mapWithKeys(fn ($quantity, $inventoryItemId) => [
                (int) $inventoryItemId => ['quantity_per_unit' => number_format((float) $quantity, 3, '.', '')],
            ])
            ->all();
    }

    private function ensureVariantBelongsToProduct(Product $product, ProductVariant $productVariant): void
    {
        abort_unless($productVariant->product_id === $product->id, 404);
    }
}