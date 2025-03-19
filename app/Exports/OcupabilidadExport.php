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

class OcupabilidadExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle
{
    protected $nombreMac;
    protected $dias;
    protected $fecha_año;
    protected $fecha_mes;
    protected $mesNombre;

    // Cambié el constructor para recibir solo 4 parámetros
    function __construct($nombreMac, $dias, $fecha_año, $fecha_mes, $mesNombre)
    {
        $this->nombreMac = $nombreMac;
        $this->dias = $dias;
        $this->fecha_año = $fecha_año;
        $this->fecha_mes = $fecha_mes;
        $this->mesNombre = $mesNombre;
    }

    public function view(): View
    {
        return view('reporte.ocupabilidad.export_excel', [
            'nombreMac' => $this->nombreMac,
            'dias' => $this->dias,
            'fecha_año' => $this->fecha_año,
            'fecha_mes' => $this->fecha_mes,
            'mesNombre' => $this->mesNombre, // Se pasa el nombre del mes
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
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A3');

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        // Centrar el texto en todas las celdas
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilos para los encabezados
        $sheet->getStyle('A7:G7')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD'], // Fondo azul claro
            ],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Texto blanco
            ],
        ]);
    }

    public function title(): string
    {
        return 'Reporte Ocupabilidad - ' . $this->nombreMac;
    }
}
