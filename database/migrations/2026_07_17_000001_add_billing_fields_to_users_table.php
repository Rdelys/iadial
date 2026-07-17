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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            $table->string('sector')->nullable()->after('company_name');
            $table->string('address')->nullable()->after('sector');
            $table->string('city')->nullable()->after('address');
            $table->string('plan')->nullable()->after('city');
            $table->string('plan_label')->nullable()->after('plan');
            $table->string('subscription_status')->default('inactive')->after('plan_label');
            $table->timestamp('subscribed_at')->nullable()->after('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'company_name',
                'sector',
                'address',
                'city',
                'plan',
                'plan_label',
                'subscription_status',
                'subscribed_at',
            ]);
        });
    }
};