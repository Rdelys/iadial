<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('iarecep_tests', function (Blueprint $table) {
        $table->text('about')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('iarecep_tests', function (Blueprint $table) {
        $table->text('about')->nullable(false)->change();
    });
}
};
