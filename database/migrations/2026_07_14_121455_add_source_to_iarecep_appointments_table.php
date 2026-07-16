<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iarecep_appointments', function (Blueprint $table) {
            $table->string('source')->default('trial')->after('token'); // trial | vapi
        });
    }

    public function down(): void
    {
        Schema::table('iarecep_appointments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};