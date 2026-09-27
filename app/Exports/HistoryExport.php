<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\Movement;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Mêmes colonnes que le relevé PDF (Date, Réf., Description, Débit, Crédit, Solde). Quand l'export
 * porte sur un seul compte ($account fourni), une ligne "Solde à l'ouverture du compte" est ajoutée
 * en haut et une ligne "Total de la période" en bas, comme sur le PDF.
 */
class HistoryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    // Cellule vide "professionnelle" : un tiret plutôt qu'une case vide, comme sur un relevé bancaire.
    protected const EMPTY = '–';

    protected $query;
    protected $periodLabel;
    protected $accountName;
    protected ?Account $account;
    protected ?float $accountOpeningBalance;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query Requête déjà filtrée (mois, ou plage de dates + compte)
     * @param string $periodLabel Libellé de la période affiché dans le titre du fichier
     * @param string $accountName
     * @param Account|null $account Compte concerné, uniquement si l'export porte sur un seul compte
     * @param float|null $accountOpeningBalance Solde du compte à sa création (indépendant de la période)
     */
    public function __construct($query, $periodLabel, $accountName, ?Account $account = null, ?float $accountOpeningBalance = null)
    {
        $this->query = $query;
        $this->periodLabel = $periodLabel;
        $this->accountName = $accountName;
        $this->account = $account;
        $this->accountOpeningBalance = $accountOpeningBalance;
    }

    protected function movements()
    {
        return (clone $this->query)
            ->with(['account.client', 'account.currency', 'currency', 'counterpartAccount.currency', 'counterpartAccount.client'])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /** Le compte est-il connu ? Si oui, on peut afficher le solde d'ouverture et les totaux. */
    protected function hasAccountContext(): bool
    {
        return $this->account !== null && $this->accountOpeningBalance !== null;
    }

    public function collection()
    {
        $rows = collect();

        if ($this->hasAccountContext()) {
            $currency = $this->account->currency->code ?? '';
            $rows->push([
                'Date'        => '',
                'Réf.'        => '',
                'Description' => "Solde à l'ouverture du compte",
                'Débit'       => self::EMPTY,
                'Crédit'      => self::EMPTY,
                'Solde'       => number_format($this->accountOpeningBalance, 0, ',', ' ') . ' ' . $currency,
            ]);
        }

        $movements = $this->movements();

        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($movements as $data) {
            $accountCurrency = $data->account->currency->code ?? '';
            $isDebit = $data->type === 'withdraw';
            $isCredit = $data->type === 'deposit';

            if ($isDebit) $debitTotal += (float) $data->final_amount;
            if ($isCredit) $creditTotal += (float) $data->final_amount;

            $rows->push([
                'Date'        => Carbon::parse($data->created_at)->format('d/m/Y H:i'),
                'Réf.'        => 'MVT-' . $data->id,
                'Description' => $this->description($data),
                'Débit'       => $isDebit
                    ? number_format($data->final_amount, 0, ',', ' ') . ' ' . $accountCurrency
                    : self::EMPTY,
                'Crédit'      => $isCredit
                    ? number_format($data->final_amount, 0, ',', ' ') . ' ' . $accountCurrency
                    : self::EMPTY,
                'Solde'       => number_format($data->balance_after, 0, ',', ' ') . ' ' . $accountCurrency,
            ]);
        }

        if ($this->hasAccountContext()) {
            $currency = $this->account->currency->code ?? '';
            $closing = $this->accountOpeningBalance + $creditTotal - $debitTotal;

            $rows->push([
                'Date'        => '',
                'Réf.'        => '',
                'Description' => 'Total de la période',
                'Débit'       => number_format($debitTotal, 0, ',', ' ') . ' ' . $currency,
                'Crédit'      => number_format($creditTotal, 0, ',', ' ') . ' ' . $currency,
                'Solde'       => number_format($closing, 0, ',', ' ') . ' ' . $currency,
            ]);
        }

        return $rows;
    }

    /**
     * Description affichée sur plusieurs lignes dans la cellule (comme la colonne Description
     * du PDF) : qui a effectué l'opération, puis, pour un transfert, vers/depuis quel compte et
     * le détail de la conversion s'il y en a une.
     */
    protected function description(Movement $data): string
    {
        $lines = [$data->performed_by ?? ($data->type === 'deposit' ? 'Dépôt' : 'Retrait')];

        if ($data->transfer_ref) {
            $other = $data->counterpartAccount;
            $name = trim(($other?->client?->nom ?? '') . ' ' . ($other?->client?->prenom ?? ''));
            $target = trim(implode(' — ', array_filter([$other?->code, $name])));
            $lines[] = ($data->type === 'withdraw' ? 'Transfert vers ' : 'Transfert depuis ') . $target;

            if ($data->rate && $data->rate_direction) {
                $enteredCode = $data->currency->code ?? '';
                $ownCode = $data->account->currency->code ?? '';
                $symbol = $data->rate_direction === 'divide' ? '÷' : '×';

                if ($enteredCode && $ownCode && $enteredCode !== $ownCode) {
                    $received = (float) $data->final_amount;
                    $targetCode = $ownCode;
                } else {
                    $received = $data->rate_direction === 'divide'
                        ? $data->amount / $data->rate
                        : $data->amount * $data->rate;
                    $received = round($received, 2);
                    $targetCode = $other?->currency?->code ?? '';
                }

                $lines[] = number_format($data->amount, 2, ',', ' ') . " {$enteredCode} {$symbol} "
                    . number_format($data->rate, 2, ',', ' ') . ' = '
                    . number_format($received, 2, ',', ' ') . " {$targetCode}";
            }
        }

        return implode("\n", $lines);
    }

    public function headings(): array
    {
        return ['Date', 'Réf.', 'Description', 'Débit', 'Crédit', 'Solde'];
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function styles(Worksheet $sheet)
    {
        // 🔹 Fusionner le titre
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Historique ' . $this->periodLabel . ' de ' . $this->accountName);

        // 🔹 Style du titre
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '2F5597']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // 🔹 Style des en-têtes
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '305496']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // 🔹 Bordures de tout le tableau
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A3:F' . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'AAAAAA']]],
            'alignment' => ['vertical' => 'center'],
        ]);

        // 🔹 Colonne Description : retour à la ligne (transfert + conversion sur plusieurs lignes)
        $sheet->getStyle('C4:C' . $highestRow)->getAlignment()->setWrapText(true);

        // 🔹 Colonnes de montants alignées à droite (comme sur un relevé), tableau plus lisible
        $sheet->getStyle('D4:F' . $highestRow)->getAlignment()->setHorizontal('right');

        $hasContext = $this->hasAccountContext();
        $movementsCollection = $this->movements();
        $firstMovementRow = 4 + ($hasContext ? 1 : 0);

        // 🔹 Ligne "Solde à l'ouverture du compte" (première ligne de données, si connue)
        if ($hasContext) {
            $sheet->mergeCells('A4:E4');
            $sheet->getStyle('A4:F4')->applyFromArray([
                'font' => ['bold' => true, 'italic' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'EEF2F7']],
            ]);
            // Débit/Crédit non applicables sur cette ligne : le tiret est recentré
            $sheet->getStyle('D4:E4')->getAlignment()->setHorizontal('center');
        }

        // 🔹 Ligne "Total de la période" (dernière ligne de données, si connue)
        if ($hasContext) {
            $totalRow = $highestRow;
            $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
            $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'EEF2F7']],
                'borders' => ['top' => ['borderStyle' => 'medium']],
            ]);
        }

        // 🔹 Solde négatif en rouge + gras (colonne F), en se basant sur les vraies valeurs des
        // mouvements plutôt que sur le texte déjà formaté de la cellule. Le tiret des colonnes
        // Débit/Crédit non applicables est recentré (au lieu de rester "collé" à droite).
        foreach ($movementsCollection as $index => $data) {
            $row = $firstMovementRow + $index;
            $sheet->getRowDimension($row)->setRowHeight(20);

            if ($data->type !== 'withdraw') {
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal('center');
            }
            if ($data->type !== 'deposit') {
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal('center');
            }

            if ((float) $data->balance_after < 0) {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('FF0000');
                $sheet->getStyle('F' . $row)->getFont()->setBold(true);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Historique';
    }
}
