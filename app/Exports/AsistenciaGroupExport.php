<?php

namespace App\Exports;

use App\Models\Asistencia;
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
class AsistenciaGroupExport implements FromView, WithDefaultStyles, ShouldAutoSize,  WithDrawings, WithStyles, WithTitle
{
    protected $query;
    protected $nombreMES;
    protected $name_mac;
    protected $tipo_desc;
    protected $fecha_inicial;
    protected $fecha_fin;

    function __construct($query, $name_mac,  $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin) {
        $this->query = $query;
        $this->nombreMES = $nombreMES;
        $this->name_mac = $name_mac;
        $this->tipo_desc = $tipo_desc;
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_fin = $fecha_fin; 
    }
    
    public function view(): View
    {
        return view('asistencia.exportgroup_excel', [
            'query' => $this->query,
            'nombreMES' => $this->nombreMES,
            'name_mac' => $this->name_mac,
            'tipo_desc' => $this->tipo_desc,
            'fecha_inicial' => $this->fecha_inicial,
            'fecha_fin' => $this->fecha_fin,
        ]);
    }

    public function defaultStyles(Style $defaultStyle)
    {
        // Configure the default styles
        return $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID);
    
        // Configura el relleno de celda
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '000'], // Color negro
            ],
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');

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
        // return $this->datos_persona->NOMBREU;
        return $this->name_mac;
    }
}
