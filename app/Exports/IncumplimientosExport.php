<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncumplimientosExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle
{
    protected $incumplimientos;
    protected $nombreMac;
    protected $nombreMes;

    public function __construct($incumplimientos, $nombreMac, $nombreMes)
    {
        $this->incumplimientos = $incumplimientos;
        $this->nombreMac = $nombreMac;
        $this->nombreMes = $nombreMes;
    }

    public function view(): View
    {
        return view('incumplimiento.exports.excel', [
            'incumplimientos' => $this->incumplimientos,
            'nombreMac'       => $this->nombreMac,
            'nombreMes'       => $this->nombreMes
        ]);
    }

    public function defaultStyles($defaultStyle)
    {
        return $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo MAC');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg')); //  asegúrate de que exista
        $drawing->setCoordinates('A2');

        // Redimensionar proporcionalmente
        $drawing->setWidth(100);
        $drawing->setHeight(60);

        $drawing->setOffsetY(5);
        $drawing->setOffsetX(5);

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->incumplimientos) + 8;
        $range = 'A9:I' . $lastRow;

        // Cabecera → fila 18 como en observaciones
        $sheet->getStyle('A18:I18')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF9C0006'], // 🔴 Rojo para diferenciar incumplimientos
            ],
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
    }

    public function title(): string
    {
        return 'Incidentes ' . $this->nombreMac;
    }
}
