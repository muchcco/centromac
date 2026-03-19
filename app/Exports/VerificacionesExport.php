<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VerificacionesExport implements FromView, ShouldAutoSize, WithTitle, WithDrawings, WithColumnFormatting
{
    protected $verificaciones;
    protected $fechaInicio;
    protected $fechaFin;
    protected $totalRegistros;

    public function __construct($verificaciones, $fechaInicio, $fechaFin, $totalRegistros)
    {
        $this->verificaciones = $verificaciones;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->totalRegistros = $totalRegistros;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('verificaciones.exports.excel', [
            'verificaciones' => $this->verificaciones,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'totalRegistros' => $this->totalRegistros,
        ]);
    }

    public function title(): string
    {
        return 'Verificaciones';
    }

    // 🔥 FORMATO REAL (CLAVE)
    public function columnFormats(): array
    {
        return [
            'C' => 'dd-mm-yyyy', 
            'D' => 'hh:mm',      
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo MAC');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('A2');

        return [$drawing];
    }
}
