<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaAsignacionExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $rows;
    protected $summary;
    protected $nameMac;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($rows, array $summary, string $nameMac, string $fechaInicio, string $fechaFin)
    {
        $this->rows = $rows;
        $this->summary = $summary;
        $this->nameMac = $nameMac;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function view(): View
    {
        return view('asistencia.asignacion.export_excel', [
            'rows' => $this->rows,
            'summary' => $this->summary,
            'nameMac' => $this->nameMac,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A5:R5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '132842'],
            ],
        ]);
    }

    public function title(): string
    {
        return 'Horas compensables';
    }
}
