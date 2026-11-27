<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');
    }

    public function down(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('gateway');
            $table->string('method');
            $table->string('currency', 3);
            $table->decimal('amount', 10, 2);
            $table->string('status')->index();
            $table->string('reference')->nullable()->unique();
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }
};
