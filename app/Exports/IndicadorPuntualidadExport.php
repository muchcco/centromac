<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
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
    protected $dias;
    protected $modulos;
    protected $numeroDias;
    protected $fecha_año;
    protected $fecha_mes;
    protected $feriados;

    function __construct($mesNombre, $nombreMac, $dias,  $modulos, $numeroDias, $fecha_año, $fecha_mes, $feriados)
    {
        $this->mesNombre = $mesNombre;
        $this->nombreMac = $nombreMac;
        $this->dias = $dias;
        $this->modulos = $modulos;
        $this->numeroDias = $numeroDias;
        $this->fecha_año = $fecha_año;
        $this->fecha_mes = $fecha_mes;
        $this->feriados = $feriados;
    }

    public function view(): View
    {
        return view('indicador.puntualidad.export_excel', [
            'mesNombre' => $this->mesNombre,
            'nombreMac' => $this->nombreMac,
            'dias' => $this->dias,
            'modulos' => $this->modulos,
            'numeroDias' => $this->numeroDias,
            'fecha_año' => $this->fecha_año,
            'fecha_mes' => $this->fecha_mes,
            'feriados' => $this->feriados,
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
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A3');

        return [$drawing];     
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilo para centrar el texto en todas las celdas de la hoja
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    public function title(): string
    {
        return $this->nombreMac;
    }
}
