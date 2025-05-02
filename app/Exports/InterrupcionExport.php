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
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InterrupcionExport implements FromView, WithDefaultStyles, ShouldAutoSize, WithDrawings, WithStyles, WithTitle
{
    protected $interrupcion;
    protected $nombreMac;
    protected $nombreMes;

    public function __construct($interrupcion, $nombreMac, $nombreMes)
    {
        $this->interrupcion = $interrupcion;
        $this->nombreMac = $nombreMac;
        $this->nombreMes = $nombreMes;
    }


    public function view(): View
    {
        return view('interrupcion.exports.excel', [
            'interrupcion' => $this->interrupcion,
            'nombreMac' => $this->nombreMac,
            'nombreMes' => $this->nombreMes // <-- ¡Aquí lo pasas!
        ]);
    }

    public function defaultStyles($defaultStyle)
    {
        return $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID);
    }

    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo MAC');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg')); // ✅ Asegúrate de que exista
        $drawing->setCoordinates('A2');
    
        // 👉 Redimensionar proporcionalmente
        $drawing->setWidth(100);  // ajusta el ancho deseado
        $drawing->setHeight(60);  // ajusta la altura deseada
    
        // Alineación vertical superior (opcional)
        $drawing->setOffsetY(5);
        $drawing->setOffsetX(5);
    
        return [$drawing];
    }
    

    public function styles(Worksheet $sheet)
    {
        // Determina el rango de celdas con datos (ajusta según la cantidad de filas esperadas)
        $lastRow = count($this->interrupcion) + 8; // Por los encabezados, empieza en fila 9 aprox.

        $range = 'A9:I' . $lastRow;

        // Estilo adicional para el encabezado
        $sheet->getStyle('A18:I18')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
    }

    public function title(): string
    {
        return 'Interrupcion ' . $this->nombreMac;
    }
}
