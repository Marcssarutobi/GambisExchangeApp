<?php

use App\Models\Movement;
use Illuminate\Database\Migrations\Migration;

/**
 * Les transferts déjà créés n'avaient le taux et son sens que sur le mouvement "dépôt".
 * On les recopie sur le mouvement "retrait" lié (même transfer_ref) pour l'affichage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Movement::whereNotNull('transfer_ref')
            ->where('type', 'deposit')
            ->whereNotNull('rate')
            ->get()
            ->each(function (Movement $in) {
                Movement::where('transfer_ref', $in->transfer_ref)
                    ->where('type', 'withdraw')
                    ->whereNull('rate')
                    ->update([
                        'rate'           => $in->rate,
                        'rate_direction' => $in->rate_direction,
                    ]);
            });
    }

    public function down(): void
    {
        // Rien à défaire : les données recopiées sont purement informatives.
    }
};
