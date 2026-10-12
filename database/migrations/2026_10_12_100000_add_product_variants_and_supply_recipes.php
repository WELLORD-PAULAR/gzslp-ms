<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('paper')->index();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->json('options')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id']);
            $table->unique(['cart_id', 'product_variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('variant_name')->nullable();
            $table->json('variant_options')->nullable();
        });

        foreach (DB::table('products')->orderBy('id')->get() as $product) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $product->id,
                'name' => 'Standard',
                'options' => json_encode(new stdClass),
                'price' => $product->price,
                'is_default' => true,
                'is_active' => $product->is_active,
                'lock_version' => 0,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]);

            DB::table('cart_items')->where('product_id', $product->id)->update(['product_variant_id' => $variantId]);
            DB::table('order_items')->where('product_id', $product->id)->update([
                'product_variant_id' => $variantId,
                'variant_name' => 'Standard',
                'variant_options' => json_encode(new stdClass),
            ]);
        }

        Schema::create('product_variant_supplies', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_per_unit', 12, 3);
            $table->primary(['product_variant_id', 'inventory_item_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('inventory_consumed_at')->nullable();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('inventory_consumed_at');
        });

        Schema::dropIfExists('product_variant_supplies');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn(['variant_name', 'variant_options']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};