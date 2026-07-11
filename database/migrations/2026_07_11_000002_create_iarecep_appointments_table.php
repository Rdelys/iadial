<?php
// database/migrations/2026_07_11_000002_create_iarecep_appointments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iarecep_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iarecep_test_id')->constrained()->cascadeOnDelete();
            $table->string('token')->index(); // sécurité supplémentaire, doit matcher le test
            $table->date('date');
            $table->time('time');
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');
            $table->timestamps();

            $table->index(['iarecep_test_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iarecep_appointments');
    }
};