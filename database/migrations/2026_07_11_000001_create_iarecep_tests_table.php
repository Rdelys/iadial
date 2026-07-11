<?php
// database/migrations/2026_07_11_000001_create_iarecep_tests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iarecep_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->index(); // identifiant du visiteur (pas de compte)
            $table->string('company_name');
            $table->string('full_name');
            $table->string('email');
            $table->string('sector')->nullable();
            $table->text('about'); // description entreprise pour le prompt IA
            $table->enum('mode', ['text', 'vocal'])->default('text');
            $table->enum('status', ['in_progress', 'closed'])->default('in_progress');
            $table->timestamps();

            $table->index(['token', 'status']);
        });

        Schema::create('iarecep_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iarecep_test_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iarecep_messages');
        Schema::dropIfExists('iarecep_tests');
    }
};