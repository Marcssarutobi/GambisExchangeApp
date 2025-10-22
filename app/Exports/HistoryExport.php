<?php

namespace App\Exports;

use App\Models\Movement;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class HistoryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    protected $month;
    protected $accountName;

    public function __construct($month, $accountName)
    {
        $this->month = $month;
        $this->accountName = $accountName;
    }

    public function collection()
    {
        $movements = Movement::whereMonth('created_at', Carbon::parse($this->month)->month)
            ->whereYear('created_at', Carbon::parse($this->month)->year)
            ->with(['account.client', 'account.currency', 'currency'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $movements->map(function ($data) {
            return [
                'Description'      => $data->performed_by ?? '-',
                'Type'             => ucfirst($data->type),
                'Montant'          => $data->amount . ' ' . ($data->currency->code ?? ''),
                'Taux'             => $data->rate ?? '-',
                'Montant final'    => $data->final_amount . ' ' . ($data->account->currency->code ?? ''),
                'Solde avant'      => $data->balance_before . ' ' . ($data->account->currency->code ?? ''),
                'Solde après'      => $data->balance_after . ' ' . ($data->account->currency->code ?? ''),
                'Date de création' => Carbon::parse($data->created_at)->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Description',
            'Type',
            'Montant',
            'Taux',
            'Montant final',
            'Solde avant',
            'Solde après',
            'Date de création',
        ];
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function styles(Worksheet $sheet)
    {
        // 🔹 Fusionner le titre
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Historique du mois de ' . Carbon::parse($this->month)->translatedFormat('F Y') . ' de ' . $this->accountName);

        // 🔹 Style du titre
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '2F5597']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // 🔹 Style des en-têtes
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '305496']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // 🔹 Bordures et lignes du tableau
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A3:H' . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'AAAAAA']]],
            'alignment' => ['vertical' => 'center'],
        ]);

        // 🔹 Colonnes de montants : C (Montant), E (Montant final), F (Solde avant), G (Solde après)
        $columns = ['C', 'E', 'F', 'G'];
        foreach ($columns as $col) {
            $sheet->getStyle($col . '4:' . $col . $highestRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.##'); // Séparateur de milliers + décimales seulement si non nulles
        }

        // 🔹 Lignes et solde négatif en rouge + gras pour F et G
        for ($row = 4; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);

            foreach (['F','G'] as $col) {
                $value = $sheet->getCell($col.$row)->getValue();
                if ($value < 0) {
                    $sheet->getStyle($col.$row)->getFont()->getColor()->setRGB('FF0000'); // rouge
                    $sheet->getStyle($col.$row)->getFont()->setBold(true); // gras
                }
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Historique';
    }
}
