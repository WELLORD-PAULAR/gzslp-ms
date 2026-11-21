<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('pending')->index();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('quoted_total', 10, 2)->nullable();
            $table->timestamp('customer_accepted_at')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->unsignedInteger('quantity');
            $table->string('paper_size', 80)->nullable();
            $table->string('paper_weight', 80)->nullable();
            $table->string('paper_finish', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};