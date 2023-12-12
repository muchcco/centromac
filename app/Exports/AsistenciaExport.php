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

class AsistenciaExport implements FromView, WithDefaultStyles, ShouldAutoSize,  WithDrawings, WithStyles, WithTitle
{
    // use Exportable;

    protected $query;

    protected $datos_persona;

    protected $nombreMES;

    protected $hora_1;
    protected $hora_2;
    protected $hora_3;
    protected $hora_4;

    function __construct($query, $datos_persona, $nombreMES, $hora_1, $hora_2, $hora_3, $hora_4) {
        $this->query = $query;
        $this->datos_persona = $datos_persona;
        $this->nombreMES = $nombreMES;
        $this->hora_1 = $hora_1;
        $this->hora_2 = $hora_2;
        $this->hora_3 = $hora_3;
        $this->hora_4 = $hora_4;
    }
    
    public function view(): View
    {
        return view('asistencia.asistencia_excel', [
            'query' => $this->query,
            'datos_persona' => $this->datos_persona,
            'nombreMES' => $this->nombreMES,
            'hora_1' => $this->hora_1,
            'hora_2' => $this->hora_2,
            'hora_3' => $this->hora_3,
            'hora_4' => $this->hora_4,
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
        return $this->datos_persona->NOMBREU;
    }
}
