<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'amount_eur')) {
                $table->decimal('amount_eur', 12, 2)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('payments', 'amount_mga')) {
                $table->decimal('amount_mga', 12, 2)->nullable()->after('amount_eur');
            }
            if (!Schema::hasColumn('payments', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 4)->nullable()->after('amount_mga');
            }
        });

        // Si l'ancienne colonne 'amount' (en MGA) existe encore,
        // on récupère sa valeur dans amount_mga avant de la supprimer.
        if (Schema::hasColumn('payments', 'amount')) {
            DB::table('payments')->whereNull('amount_mga')->update([
                'amount_mga' => DB::raw('amount'),
            ]);

            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }

        // La devise affichée au client passe en EUR par défaut.
        if (Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('currency')->default('EUR')->change();
            });

            DB::table('payments')->update(['currency' => 'EUR']);
        }

        // amount_eur devient obligatoire une fois les anciennes lignes traitées
        // (laissé nullable pour rester compatible avec d'éventuelles lignes historiques sans équivalent EUR).
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('phone');
            }
        });

        DB::table('payments')->update([
            'amount' => DB::raw('COALESCE(amount_mga, 0)'),
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['amount_eur', 'amount_mga', 'exchange_rate']);
            $table->string('currency')->default('MGA')->change();
        });
    }
};