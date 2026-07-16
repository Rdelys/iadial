<?php
// database/migrations/2026_07_15_140000_create_iarecep_visits_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iarecep_visits', function (Blueprint $table) {
            $table->id();
            $table->string('path')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iarecep_visits');
    }
};