<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioDiferenciado extends Model
{
    protected $table = 'm_horario_diferenciado';

    protected $primaryKey = 'idhorario_diferenciado';

    public $timestamps = true;

    protected $fillable = [
        'idcentro_mac',
        'identidad',
        'idmodulo',
        'fecha_inicio',
        'fecha_fin',
        'DiaSemana',
        'HoraIngreso',
        'HoraSalida',
        'Observaciones',
        'activo'
    ];
}