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

use Maatwebsite\Excel\Concerns\WithTitle;

class SeviciosEntidadExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithStyles, WithTitle
{
    protected $entidad;
    protected $servicios;

    function __construct($entidad, $servicios) {
        $this->entidad = $entidad;
        $this->servicios = $servicios;
    }
    
    public function view(): View
    {
        return view('servicios.exportgroup_excel_pr', [
            'entidad' => $this->entidad,
            'servicios' => $this->servicios
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
                // 'startColor' => ['argb' => 'fff'], // Color negro
            ],
        ];
    }

   

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilo para centrar el texto en todas las celdas de la hoja
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                // 'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'fff'], // Color blanco (puedes ajustar el color según tus preferencias)
            ],
        ]);

        // Ajustar el formato específico de la columna E
        $sheet->getStyle('E')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Ajustar el texto dentro de las celdas para que se ajuste al tamaño de la celda
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);

        // También puedes aplicar estilos específicos a otras celdas o rangos según tus necesidades
        // $sheet->getStyle('A1')->applyFromArray([...]);

        // También puedes ajustar otros estilos, como bordes, fuentes, etc., según tus necesidades
        // $sheet->getStyle('A1:B2')->applyFromArray([...]);
    }

    public function title(): string
    {
        return $this->entidad->NOMBRE_ENTIDAD;
    }
}
