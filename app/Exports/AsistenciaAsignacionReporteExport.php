<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaAsignacionReporteExport implements FromView, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    protected Collection $rows;
    protected string $nameMac;
    protected string $fechaInicio;
    protected string $fechaFin;

    public function __construct($rows, string $nameMac, string $fechaInicio, string $fechaFin)
    {
        $this->rows = collect($rows);
        $this->nameMac = $nameMac;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function view(): View
    {
        return view('asistencia.asignacion.reporte_asistencias.export_excel', [
            'rows' => $this->rows,
            'nameMac' => $this->nameMac,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'fechaInicioTexto' => $this->fechaLarga($this->fechaInicio),
            'fechaFinTexto' => $this->fechaLarga($this->fechaFin),
        ]);
    }

    private function fechaLarga(string $fecha): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Setiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $date = \Carbon\Carbon::parse($fecha);

        return $date->format('d') . ' de ' . $meses[$date->month] . ' ' . $date->format('Y');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3.6,
            'B' => 1.5,
            'C' => 15.9,
            'D' => 15.9,
            'E' => 30.3,
            'F' => 12.9,
            'G' => 14,
            'H' => 19.8,
            'I' => 8.9,
            'J' => 9.9,
            'K' => 14,
            'L' => 15,
            'M' => 14,
            'N' => 14,
            'O' => 12.9,
            'P' => 12.9,
            'Q' => 34.6,
            'R' => 1.5,
            'S' => 3.6,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 8 + max($this->rows->count(), 1);

        $sheet->setShowGridlines(false);

        $sheet->getStyle('B2:R5')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ]);

        $sheet->mergeCells('F3:K4');
        $sheet->getStyle('F3:K4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '002060'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('L3:L4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        $sheet->getStyle('O3:Q4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '002060'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B7:R' . ($lastRow + 1))->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ]);

        $sheet->getStyle('D8:Q8')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '002060'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('D9:Q' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_DOTTED,
                    'color' => ['rgb' => '808080'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('D8:Q' . $lastRow)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle('K9:P' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D9:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I9:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('Q9:Q' . $lastRow)->getFont()->getColor()->setRGB('7030A0');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 8 + max($this->rows->count(), 1);
                $logoPath = public_path('imagen/mac_logo_export.jpg');

                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo MAC');
                    $drawing->setDescription('Logo Centro MAC');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(48);
                    $drawing->setCoordinates('B3');
                    $drawing->setOffsetX(8);
                    $drawing->setOffsetY(6);
                    $drawing->setWorksheet($sheet);
                }

                $sheet->freezePane('D9');
                $sheet->setAutoFilter('D8:Q' . $lastRow);
                $sheet->getColumnDimension('C')->setVisible(false);
                $sheet->getRowDimension(1)->setRowHeight(8);
                $sheet->getRowDimension(2)->setRowHeight(8);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(8);
                $sheet->getRowDimension(6)->setRowHeight(14);
                $sheet->getRowDimension(7)->setRowHeight(8);
                $sheet->getRowDimension(8)->setRowHeight(40);

                for ($row = 9; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(18);
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Control asistencia';
    }
}
