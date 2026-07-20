<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('vapi_public_key')->nullable()->after('subscription_status');
            $table->string('vapi_assistant_id')->nullable()->after('vapi_public_key');
            $table->string('booking_slug')->nullable()->unique()->after('vapi_assistant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vapi_public_key', 'vapi_assistant_id', 'booking_slug']);
        });
    }
};