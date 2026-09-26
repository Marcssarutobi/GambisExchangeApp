<?php

namespace App\Exports;

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
 * Mêmes colonnes que le relevé PDF (Date, Réf., Description, Débit, Crédit, Solde), pour que les
 * deux exports se ressemblent.
 */
class HistoryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    protected $query;
    protected $periodLabel;
    protected $accountName;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query Requête déjà filtrée (mois, ou plage de dates + compte)
     * @param string $periodLabel Libellé de la période affiché dans le titre du fichier
     * @param string $accountName
     */
    public function __construct($query, $periodLabel, $accountName)
    {
        $this->query = $query;
        $this->periodLabel = $periodLabel;
        $this->accountName = $accountName;
    }

    protected function movements()
    {
        return (clone $this->query)
            ->with(['account.client', 'account.currency', 'currency', 'counterpartAccount.currency', 'counterpartAccount.client'])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function collection()
    {
        return $this->movements()->map(function (Movement $data) {
            $accountCurrency = $data->account->currency->code ?? '';

            return [
                'Date'        => Carbon::parse($data->created_at)->format('d/m/Y H:i'),
                'Réf.'        => 'MVT-' . $data->id,
                'Description' => $this->description($data),
                'Débit'       => $data->type === 'withdraw'
                    ? number_format($data->final_amount, 0, ',', ' ') . ' ' . $accountCurrency
                    : '',
                'Crédit'      => $data->type === 'deposit'
                    ? number_format($data->final_amount, 0, ',', ' ') . ' ' . $accountCurrency
                    : '',
                'Solde'       => number_format($data->balance_after, 0, ',', ' ') . ' ' . $accountCurrency,
            ];
        });
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

        // 🔹 Bordures et lignes du tableau
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A3:F' . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'AAAAAA']]],
            'alignment' => ['vertical' => 'center'],
        ]);

        // 🔹 Colonne Description : retour à la ligne (transfert + conversion sur plusieurs lignes)
        $sheet->getStyle('C4:C' . $highestRow)->getAlignment()->setWrapText(true);

        // 🔹 Lignes et solde négatif en rouge + gras (colonne F), en se basant sur les vraies
        // valeurs des mouvements plutôt que sur le texte déjà formaté de la cellule.
        $movements = $this->movements();
        foreach ($movements as $index => $data) {
            $row = 4 + $index;
            $sheet->getRowDimension($row)->setRowHeight(20);

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
