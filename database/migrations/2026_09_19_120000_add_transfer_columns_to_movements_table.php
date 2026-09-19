<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transfert de compte à compte : un transfert crée deux mouvements liés
 * (un retrait sur le compte source, un dépôt sur le compte destination).
 * - transfer_ref : référence commune aux deux mouvements (NULL = mouvement classique)
 * - counterpart_account_id : l'autre compte du transfert
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->string('transfer_ref', 40)->nullable()->index()->after('performed_by');
            $table->foreignId('counterpart_account_id')->nullable()->after('transfer_ref')
                ->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('counterpart_account_id');
            $table->dropColumn('transfer_ref');
        });
    }
};
