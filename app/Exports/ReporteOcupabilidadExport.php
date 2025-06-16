<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReporteOcupabilidadExport implements FromView
{
    protected $modulos, $dias, $numeroDias, $fecha_año, $fecha_mes;
    protected $mesNombre, $feriadosPorMac, $diasHabilesPorModulo;

    public function __construct($modulos, $dias, $numeroDias, $fecha_año, $fecha_mes, $mesNombre, $feriadosPorMac, $diasHabilesPorModulo)
    {
        $this->modulos              = $modulos;
        $this->dias                 = $dias;
        $this->numeroDias           = $numeroDias;
        $this->fecha_año            = $fecha_año;
        $this->fecha_mes            = $fecha_mes;
        $this->mesNombre            = $mesNombre;              
        $this->feriadosPorMac       = $feriadosPorMac;
        $this->diasHabilesPorModulo = $diasHabilesPorModulo;
    }

    public function view(): View
    {
        return view('reporte.ocupabilidad.export_excel', [
            'modulos'              => $this->modulos,
            'dias'                 => $this->dias,
            'numeroDias'           => $this->numeroDias,
            'fecha_año'            => $this->fecha_año,
            'fecha_mes'            => $this->fecha_mes,
            'mesNombre'            => $this->mesNombre,         
            'feriadosPorMac'       => $this->feriadosPorMac,
            'diasHabilesPorModulo' => $this->diasHabilesPorModulo,
        ]);
    }
}
