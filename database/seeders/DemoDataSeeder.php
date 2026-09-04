<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Currency;
use App\Models\CurrencyPurchases;
use App\Models\Exchangerate;
use App\Models\Movement;
use App\Services\CashRegisterService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Jeu de données de démonstration pour tester l'application en local :
 * devises, clients, comptes, taux de change, mouvements (dépôts/retraits),
 * achats/ventes de devises, et caisse générale (soldes + historique)
 * cohérents entre eux (les mouvements passent par les mêmes règles que
 * les contrôleurs : solde avant/après, caisse générale, etc.).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $currencies = $this->seedCurrencies();
            $this->seedExchangeRates($currencies);
            $clients = $this->seedClients();
            $accounts = $this->seedAccounts($clients, $currencies);
            $this->seedMovements($accounts, $currencies);
            $this->seedCurrencyPurchases($currencies);
        });
    }

    private function seedCurrencies(): array
    {
        $definitions = [
            ['code' => 'XOF', 'name' => 'Franc CFA (BCEAO)'],
            ['code' => 'USD', 'name' => 'Dollar américain'],
            ['code' => 'EUR', 'name' => 'Euro'],
            ['code' => 'NGN', 'name' => 'Naira nigérian'],
        ];

        $currencies = [];
        foreach ($definitions as $def) {
            $currencies[$def['code']] = Currency::firstOrCreate(['code' => $def['code']], $def);
        }

        return $currencies;
    }

    private function seedExchangeRates(array $c): void
    {
        $rates = [
            ['from' => 'USD', 'to' => 'XOF', 'rate' => 700],
            ['from' => 'EUR', 'to' => 'XOF', 'rate' => 655],
            ['from' => 'USD', 'to' => 'NGN', 'rate' => 1500],
        ];

        foreach ($rates as $r) {
            Exchangerate::firstOrCreate([
                'from_currency_id' => $c[$r['from']]->id,
                'to_currency_id' => $c[$r['to']]->id,
            ], [
                'rate' => $r['rate'],
            ]);
        }
    }

    private function seedClients(): array
    {
        $definitions = [
            ['nom' => 'Dossou', 'prenom' => 'Marc', 'phone' => '+22997000001', 'email' => 'marc.dossou@example.com', 'npiece' => 'CI0001'],
            ['nom' => 'Ahouansou', 'prenom' => 'Fabienne', 'phone' => '+22997000002', 'email' => 'fabienne.a@example.com', 'npiece' => 'CI0002'],
            ['nom' => 'Koffi', 'prenom' => 'Steve', 'phone' => '+22997000003', 'email' => null, 'npiece' => 'CI0003'],
        ];

        return array_map(fn ($def) => Client::firstOrCreate(['npiece' => $def['npiece']], $def), $definitions);
    }

    private function seedAccounts(array $clients, array $c): array
    {
        $accounts = [];

        // Chaque client a un compte XOF, et le premier a en plus un compte USD.
        foreach ($clients as $index => $client) {
            $accounts[] = Account::firstOrCreate([
                'code' => 'CPT-' . str_pad($index * 2 + 1, 4, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'currency_id' => $c['XOF']->id,
            ], [
                'balance' => 0,
            ]);
        }

        $accounts[] = Account::firstOrCreate([
            'code' => 'CPT-0002',
            'client_id' => $clients[0]->id,
            'currency_id' => $c['USD']->id,
        ], [
            'balance' => 0,
        ]);

        return $accounts;
    }

    /**
     * Quelques mouvements réalistes (dépôts/retraits), avec et sans conversion,
     * qui alimentent aussi la caisse générale (comme le ferait MovementController).
     */
    private function seedMovements(array $accounts, array $c): void
    {
        [$accountClient1Xof, $accountClient2Xof, $accountClient3Xof, $accountClient1Usd] = $accounts;

        $movements = [
            // Dépôt simple, pas de conversion
            ['account' => $accountClient1Xof, 'type' => 'deposit', 'amount' => 150000, 'currency' => $c['XOF'], 'rate' => null, 'direction' => null, 'by' => 'Caissier Test'],
            // Dépôt en USD sur un compte USD, pas de conversion
            ['account' => $accountClient1Usd, 'type' => 'deposit', 'amount' => 500, 'currency' => $c['USD'], 'rate' => null, 'direction' => null, 'by' => 'Caissier Test'],
            // Dépôt de XOF apporté par le client, converti sur un compte USD (division)
            ['account' => $accountClient1Usd, 'type' => 'deposit', 'amount' => 70000, 'currency' => $c['XOF'], 'rate' => 700, 'direction' => 'divide', 'by' => 'Caissier Test'],
            // Dépôt client 2, pas de conversion
            ['account' => $accountClient2Xof, 'type' => 'deposit', 'amount' => 80000, 'currency' => $c['XOF'], 'rate' => null, 'direction' => null, 'by' => 'Super Admin'],
            // Retrait client 2
            ['account' => $accountClient2Xof, 'type' => 'withdraw', 'amount' => 20000, 'currency' => $c['XOF'], 'rate' => null, 'direction' => null, 'by' => 'Super Admin'],
            // Dépôt client 3 en USD converti en XOF (multiplication)
            ['account' => $accountClient3Xof, 'type' => 'deposit', 'amount' => 100, 'currency' => $c['USD'], 'rate' => 700, 'direction' => 'multiply', 'by' => 'Caissier Test'],
        ];

        foreach ($movements as $m) {
            $account = $m['account'];
            $amount = (float) $m['amount'];
            $finalAmount = $amount;

            if ($m['rate']) {
                $finalAmount = $m['direction'] === 'multiply'
                    ? $amount * $m['rate']
                    : $amount / $m['rate'];
            }

            $balanceBefore = (float) $account->balance;

            if ($m['type'] === 'deposit') {
                $account->increment('balance', $finalAmount);
            } else {
                $account->decrement('balance', $finalAmount);
            }

            $movement = Movement::create([
                'account_id' => $account->id,
                'type' => $m['type'],
                'amount' => $amount,
                'rate' => $m['rate'],
                'rate_direction' => $m['direction'],
                'final_amount' => $finalAmount,
                'currency_id' => $m['currency']->id,
                'performed_by' => $m['by'],
                'balance_before' => $balanceBefore,
                'balance_after' => $account->fresh()->balance,
            ]);

            CashRegisterService::record(
                $m['currency']->id,
                $m['type'] === 'deposit' ? 'client_deposit' : 'client_withdraw',
                $m['type'] === 'deposit' ? 'in' : 'out',
                $amount,
                $movement,
                'Mouvement #' . $movement->id . ' - compte ' . $account->code
            );
        }
    }

    /**
     * Un achat et une vente de devise, pour tester la caisse générale et le point 4/7.
     */
    private function seedCurrencyPurchases(array $c): void
    {
        $purchase = CurrencyPurchases::create([
            'currency_id' => $c['USD']->id,
            'type' => 'achat',
            'supplier' => 'Fournisseur Sika Change',
            'amount_purchased' => 1000,
            'rate' => 690,
            'rate_direction' => 'multiply',
            'payment_currency_id' => $c['XOF']->id,
            'total_paid' => 690000,
        ]);
        CashRegisterService::record($c['XOF']->id, 'purchase_out', 'out', 690000, $purchase, 'Achat #' . $purchase->id);
        CashRegisterService::record($c['USD']->id, 'purchase_in', 'in', 1000, $purchase, 'Achat #' . $purchase->id);

        $sale = CurrencyPurchases::create([
            'currency_id' => $c['USD']->id,
            'type' => 'vente',
            'supplier' => 'Client de passage',
            'amount_purchased' => 300,
            'rate' => 705,
            'rate_direction' => 'multiply',
            'payment_currency_id' => $c['XOF']->id,
            'total_paid' => 211500,
        ]);
        CashRegisterService::record($c['XOF']->id, 'sale_in', 'in', 211500, $sale, 'Vente #' . $sale->id);
        CashRegisterService::record($c['USD']->id, 'sale_out', 'out', 300, $sale, 'Vente #' . $sale->id);
    }
}
