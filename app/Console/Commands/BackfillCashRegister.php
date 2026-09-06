<?php

namespace App\Console\Commands;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CurrencyPurchases;
use App\Models\Movement;
use App\Services\CashRegisterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Synchronise la caisse générale avec les données déjà existantes dans le système
 * (comptes, mouvements, achats/ventes créés AVANT la mise en place de la caisse).
 *
 * Rejoue chaque dépôt/retrait/achat/vente déjà en base, dans l'ordre chronologique réel,
 * pour reconstruire les soldes de caisse et l'historique comme si la caisse générale
 * avait existé depuis le début.
 *
 * ⚠️ À exécuter UNE SEULE FOIS, avant que la caisse ne soit utilisée en conditions
 * réelles (sinon les nouveaux mouvements réels et les anciens mouvements rejoués se
 * mélangeraient dans le mauvais ordre). Si des mouvements de caisse existent déjà
 * (tests, etc.), la commande le signale et demande une confirmation explicite.
 *
 * Usage :
 *   php artisan cash-register:backfill
 *   php artisan cash-register:backfill --force   (sans confirmation)
 */
class BackfillCashRegister extends Command
{
    protected $signature = 'cash-register:backfill {--force : Ne pas demander de confirmation}';

    protected $description = 'Reconstruit la caisse générale à partir des mouvements et achats/ventes déjà existants en base';

    public function handle(): int
    {
        $existingCount = CashMovement::count();

        if ($existingCount > 0 && !$this->option('force')) {
            $this->warn("La caisse générale contient déjà {$existingCount} mouvement(s).");
            $this->warn("Relancer cette synchronisation maintenant mélangerait des mouvements réels avec des données rejouées, dans le mauvais ordre chronologique.");
            if (!$this->confirm('Voulez-vous vraiment continuer quand même ?', false)) {
                $this->info('Annulé.');
                return self::SUCCESS;
            }
        }

        $this->info('Récupération des mouvements et achats/ventes existants...');

        // On construit une liste unique d'événements, triés par date réelle (created_at),
        // pour rejouer l'historique dans le bon ordre.
        $events = [];

        foreach (Movement::orderBy('created_at')->get() as $movement) {
            $events[] = [
                'date' => $movement->created_at,
                'id' => $movement->id,
                'apply' => function () use ($movement) {
                    CashRegisterService::record(
                        $movement->currency_id,
                        $movement->type === 'deposit' ? 'client_deposit' : 'client_withdraw',
                        $movement->type === 'deposit' ? 'in' : 'out',
                        (float) $movement->amount,
                        $movement,
                        'Synchronisation - mouvement #' . $movement->id,
                        $movement->created_at
                    );
                },
            ];
        }

        foreach (CurrencyPurchases::orderBy('created_at')->get() as $purchase) {
            if (!$purchase->payment_currency_id) {
                continue; // pas de devise de paiement renseignée -> pas d'impact caisse possible
            }
            $events[] = [
                'date' => $purchase->created_at,
                'id' => $purchase->id,
                'apply' => function () use ($purchase) {
                    if ($purchase->type === 'achat') {
                        CashRegisterService::record($purchase->payment_currency_id, 'purchase_out', 'out', (float) $purchase->total_paid, $purchase, 'Synchronisation - achat #' . $purchase->id, $purchase->created_at);
                        CashRegisterService::record($purchase->currency_id, 'purchase_in', 'in', (float) $purchase->amount_purchased, $purchase, 'Synchronisation - achat #' . $purchase->id, $purchase->created_at);
                    } else {
                        CashRegisterService::record($purchase->payment_currency_id, 'sale_in', 'in', (float) $purchase->total_paid, $purchase, 'Synchronisation - vente #' . $purchase->id, $purchase->created_at);
                        CashRegisterService::record($purchase->currency_id, 'sale_out', 'out', (float) $purchase->amount_purchased, $purchase, 'Synchronisation - vente #' . $purchase->id, $purchase->created_at);
                    }
                },
            ];
        }

        // Tri chronologique global (les deux sources mélangées)
        usort($events, function ($a, $b) {
            return $a['date'] <=> $b['date'] ?: $a['id'] <=> $b['id'];
        });

        if (empty($events)) {
            $this->info('Aucun mouvement ou achat/vente existant à synchroniser.');
            return self::SUCCESS;
        }

        $this->info(count($events) . ' événement(s) à rejouer...');
        $bar = $this->output->createProgressBar(count($events));

        DB::transaction(function () use ($events, $bar) {
            foreach ($events as $event) {
                $event['apply']();
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Synchronisation terminée. Soldes de caisse reconstruits :');
        $this->table(
            ['Devise', 'Solde'],
            CashRegister::with('currency')->get()->map(fn ($r) => [
                $r->currency->code ?? '-',
                number_format((float) $r->balance, 2, ',', ' '),
            ])
        );

        return self::SUCCESS;
    }
}
