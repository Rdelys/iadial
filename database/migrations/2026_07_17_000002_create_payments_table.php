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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plan');
            $table->string('plan_label')->nullable();
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('sector')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('MGA');
            $table->string('reference')->unique();
            $table->string('notification_token')->nullable();
            $table->string('payment_link')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending'); // pending | success | failed
            $table->json('raw_notification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};