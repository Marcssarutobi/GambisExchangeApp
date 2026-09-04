<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique de la caisse générale : chaque entrée/sortie de trésorerie, avec son origine
 * (dépôt client, retrait client, achat de devise, vente de devise, ajustement manuel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'client_deposit',   // dépôt d'un client (entrée)
                'client_withdraw',  // retrait d'un client (sortie)
                'purchase_in',      // achat de devise : entrée de la devise achetée
                'purchase_out',     // achat de devise : sortie de la devise de paiement
                'sale_in',          // vente de devise : entrée de la devise de paiement reçue
                'sale_out',         // vente de devise : sortie de la devise vendue
                'adjustment',       // ajustement manuel (écart de comptage physique)
            ]);
            $table->decimal('amount', 20, 2);
            $table->decimal('balance_before', 20, 2);
            $table->decimal('balance_after', 20, 2);
            $table->string('reference_type')->nullable(); // ex: App\Models\Movement, App\Models\CurrencyPurchases
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
