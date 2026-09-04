<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correction du bug de calcul des taux (point 5) : le montant final était toujours calculé
 * par une multiplication, quel que soit le sens réel de la conversion. Le taux reste saisi
 * manuellement (il varie et c'est voulu), mais l'agent doit désormais préciser explicitement
 * le sens à appliquer : multiplier ou diviser.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->enum('rate_direction', ['multiply', 'divide'])->nullable()->after('rate');
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('rate_direction');
        });
    }
};
