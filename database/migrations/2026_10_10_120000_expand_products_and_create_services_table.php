<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('paper_size', 80)->nullable();
            $table->string('paper_weight', 80)->nullable();
            $table->string('paper_finish', 80)->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'name',
                'description',
                'price',
                'paper_size',
                'paper_weight',
                'paper_finish',
                'is_active',
            ]);
        });
    }
};