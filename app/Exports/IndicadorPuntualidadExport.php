<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class IndicadorPuntualidadExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle
{
    use Exportable;

    protected $mesNombre;
    protected $nombreMac;
    protected $final;
    protected $modulos;
    protected $numeroDias;
    protected $fecha_año;
    protected $fecha_mes;
    protected $feriados;
    protected $diasCerrados;

    function __construct(
        $mesNombre,
        $nombreMac,
        $final,
        $modulos,
        $numeroDias,
        $fecha_año,
        $fecha_mes,
        $diasCerrados,
        $feriados
    ) {
        $this->mesNombre = $mesNombre;
        $this->nombreMac = $nombreMac;
        $this->final = $final;
        $this->modulos = $modulos;
        $this->numeroDias = $numeroDias;
        $this->fecha_año = $fecha_año;
        $this->fecha_mes = $fecha_mes;
        $this->diasCerrados = $diasCerrados;
        $this->feriados = $feriados;
    }

    public function view(): View
    {
        return view('indicador.puntualidad.export_excel', [
            'mesNombre' => $this->mesNombre,
            'nombreMac' => $this->nombreMac,
            'final' => $this->final,
            'modulos' => $this->modulos,
            'numeroDias' => $this->numeroDias,
            'fecha_año' => $this->fecha_año,
            'fecha_mes' => $this->fecha_mes,
            'feriados' => $this->feriados,
            'diasCerrados' => $this->diasCerrados
        ]);
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Centro MAC');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A3');

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        $numeroDias = $this->numeroDias;

        $colFinal = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numeroDias + 3);

        $filaHeader = 7;

        $sheet->getStyle("A{$filaHeader}:{$colFinal}{$filaHeader}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B22B4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Ajuste texto entidad
        $sheet->getStyle('B:B')->getAlignment()->setWrapText(true);

        // Ancho columnas
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(40);
    }

    public function title(): string
    {
        return $this->nombreMac;
    }
}
