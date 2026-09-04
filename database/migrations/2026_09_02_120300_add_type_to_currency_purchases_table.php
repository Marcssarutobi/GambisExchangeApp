<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Points 4 & 7 : le module "Achats de devises" devient "Achat / Vente de devises".
 * - "type" distingue un achat (achat = on paie pour recevoir la devise) d'une vente
 *   (vente = on reçoit un paiement en échange de la devise vendue).
 * - "rate_direction" applique le même correctif que pour les mouvements (point 5) :
 *   le taux reste saisi manuellement, mais le sens du calcul (multiplier/diviser) est explicite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currency_purchases', function (Blueprint $table) {
            $table->enum('type', ['achat', 'vente'])->default('achat')->after('currency_id');
            $table->enum('rate_direction', ['multiply', 'divide'])->default('multiply')->after('rate');
        });

        // "supplier" (fournisseur) n'a de sens que pour un achat ; on la rend optionnelle pour
        // une vente. Fait en SQL brut pour éviter la dépendance à doctrine/dbal (Schema::change()).
        // ⚠️ Requête MySQL : à adapter si le projet tourne sur un autre SGBD (Postgres, SQLite...).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE currency_purchases MODIFY supplier VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('currency_purchases', function (Blueprint $table) {
            $table->dropColumn(['type', 'rate_direction']);
        });
    }
};
