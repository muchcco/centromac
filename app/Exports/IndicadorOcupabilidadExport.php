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
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;

class IndicadorOcupabilidadExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle
{
    protected $query;
    protected $fecha_año;
    protected $fecha_mes;
    protected $name_mac;
    protected $nombre_mes;
    protected $daysInMonth;
    protected $feriados;

    function __construct($query, $fecha_año, $fecha_mes, $name_mac, $nombre_mes, $daysInMonth, $feriados)
    {
        $this->query = $query;
        $this->fecha_año = $fecha_año;
        $this->fecha_mes = $fecha_mes;
        $this->name_mac = $name_mac;
        $this->nombre_mes = $nombre_mes;
        $this->daysInMonth = $daysInMonth;
        $this->feriados = $feriados;
    }

    public function view(): View
    {
        return view('indicador.ocupabilidad.export_excel', [
            'query' => $this->query,
            'fecha_año' => $this->fecha_año,
            'fecha_mes' => $this->fecha_mes,
            'name_mac' => $this->name_mac,
            'nombre_mes' => $this->nombre_mes,
            'daysInMonth' => $this->daysInMonth,
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
        $drawing->setDescription('Logo del Centro MAC');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg')); // Asegúrate de que la ruta es correcta
        $drawing->setHeight(50);
        $drawing->setCoordinates('A3');

        return [$drawing];     
    }

    public function styles(Worksheet $sheet)
    {
        // Centrar el texto en las celdas
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Ajustar los encabezados
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'], // Fondo azul
            ],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Texto blanco
            ],
        ]);
    }

    public function title(): string
    {
        return $this->name_mac;
    }
}
