<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy formatted numbers before the column becomes numeric.
        DB::table('users')->orderBy('id')->each(function (object $user): void {
            if ($user->phone === null) {
                return;
            }

            $digits = preg_replace('/\D+/', '', (string) $user->phone) ?: '';

            DB::table('users')->whereKey($user->id)->update([
                'phone' => strlen($digits) >= 7 && strlen($digits) <= 15 ? $digits : null,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 25)->nullable()->change();
        });
    }
};
