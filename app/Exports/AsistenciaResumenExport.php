<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaResumenExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $query;
    protected $name_mac;
    protected $nombreMES;

    public function __construct($query, $name_mac, $nombreMES)
    {
        $this->query = $query;
        $this->name_mac = $name_mac;
        $this->nombreMES = $nombreMES;
    }

    public function view(): View
    {
        return view('asistencia.asistencia_resumen_excel', [
            'query'      => $this->query,
            'name_mac'   => $this->name_mac,
            'nombreMES'  => $this->nombreMES,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados en negrita y fondo azul
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '0B22B4'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);
    }
}
