<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioMac extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'm_horario_mac';
    protected $primaryKey = 'idhorario'; // Cambia 'idhorario' por el nombre de tu columna clave primaria
    public $timestamps = false;

    // Definir las columnas que son asignables
    protected $fillable = [
        'idcentro_mac',
        'idmodulo',
        'horaingreso',
        'horasalida',
        'fechainicio',
        'fechafin'
    ];

    // Definir las relaciones con otras tablas
    public function centroMac()
    {
        return $this->belongsTo(Mac::class, 'idcentro_mac', 'idcentro_mac');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'idmodulo', 'idmodulo');
    }
}
