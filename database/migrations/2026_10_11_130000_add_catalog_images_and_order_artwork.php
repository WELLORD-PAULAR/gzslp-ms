<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('image_path')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('artwork_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('artwork_path');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};