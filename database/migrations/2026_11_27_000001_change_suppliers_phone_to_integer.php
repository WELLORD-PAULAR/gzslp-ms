<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('suppliers')->orderBy('id')->each(function (object $supplier): void {
            if ($supplier->phone === null) {
                return;
            }

            $digits = preg_replace('/\D+/', '', (string) $supplier->phone) ?: '';

            DB::table('suppliers')->whereKey($supplier->id)->update([
                'phone' => strlen($digits) >= 7 && strlen($digits) <= 15 ? $digits : null,
            ]);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone', 25)->nullable()->change();
        });
    }
};
