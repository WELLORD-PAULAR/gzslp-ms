<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('gateway')->index();
            $table->string('method');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('status')->default('pending')->index();
            $table->string('reference')->nullable()->unique();
            $table->string('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
