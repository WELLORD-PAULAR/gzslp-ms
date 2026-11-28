<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalize('users');
        $this->normalize('suppliers');

        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('phone')->nullable()->change();
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->unsignedBigInteger('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 25)->nullable()->change();
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->string('phone', 25)->nullable()->change();
        });
    }

    private function normalize(string $table): void
    {
        if (! Schema::hasColumn($table, 'phone')) {
            return;
        }

        DB::table($table)->whereNotNull('phone')->orderBy('id')->each(function (object $row) use ($table): void {
            $digits = preg_replace('/\D+/', '', (string) $row->phone);
            $value = $digits === '' ? null : $digits;

            DB::table($table)->where('id', $row->id)->update(['phone' => $value]);
        });
    }
};
