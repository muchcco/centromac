<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class VerificacionesExport implements FromView, ShouldAutoSize, WithTitle
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

    /**
     * Método que retorna la vista para la exportación
     */
    public function view(): \Illuminate\Contracts\View\View
    {
        return view('verificaciones.exports.excel', [
            'verificaciones' => $this->verificaciones,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'totalRegistros' => $this->totalRegistros, // Pasamos el total de registros
        ]);
    }
    /**
     * Método que retorna la colección de datos para la exportación
     */
    public function collection()
    {
        return collect($this->verificaciones);
    }
    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return 'Verificaciones desde ' . $this->fechaInicio . ' hasta ' . $this->fechaFin;
    }

    /**
     * Método para agregar el logo al archivo Excel
     */
    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('imagen/mac_logo_export.jpg'));  // Ruta local de la imagen
        $drawing->setHeight(50);  // Ajusta la altura del logo
        // Si necesitas un ancho específico, puedes establecerlo aquí
        $drawing->setWidth(100);  // Ajusta el ancho del logo (opcional, para mantener la proporción)

        // Coloca el logo en la celda A2
        $drawing->setCoordinates('A2');

        // Si necesitas mover el logo dentro de la celda (ajustar según el diseño)
        // No es necesario si la celda tiene el tamaño adecuado
        $drawing->setOffsetX(10);  // Desplazamiento horizontal (ajustar según sea necesario)
        $drawing->setOffsetY(10);  // Desplazamiento vertical (ajustar según sea necesario)

        return [$drawing];
    }
}
