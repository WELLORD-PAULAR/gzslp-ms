<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });
    }
};