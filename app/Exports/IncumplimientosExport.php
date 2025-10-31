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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IncumplimientosExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle, WithEvents
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
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setCoordinates('A2');

        $drawing->setWidth(100);
        $drawing->setHeight(60);
        $drawing->setOffsetY(5);
        $drawing->setOffsetX(5);

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->incumplimientos) + 8;

        /* 🔹 Encabezado azul institucional */
        $sheet->getStyle('A14:I14')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E79'], // Azul MAC
            ],
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        /* 🔹 Bordes y alineación general */
        $range = 'A15:I' . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
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

    /**
     * 🔧 Ajuste de columnas, centrado general y justificación en descripciones
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 🔹 Centramos todo el contenido general
                $sheet->getStyle("A15:I{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // 🔹 Justificamos las columnas de descripción (D y E)
                $sheet->getStyle("D15:D{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_JUSTIFY)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                $sheet->getStyle("E15:E{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_JUSTIFY)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                // 🔹 Ajuste de anchos
                $sheet->getColumnDimension('D')->setWidth(80);
                $sheet->getColumnDimension('E')->setWidth(80);

                foreach (['A', 'B', 'C', 'F', 'G', 'H', 'I'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 25) {
                        $sheet->getColumnDimension($col)->setWidth(25);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Incidentes ' . $this->nombreMac;
    }
}
