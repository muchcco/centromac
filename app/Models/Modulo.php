<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'm_modulo'; // Especifica el nombre correcto de la tabla

    protected $primaryKey = 'IDMODULO'; // Define la clave primaria

    // Los campos que pueden ser asignados masivamente
    protected $fillable = [
        'IDCENTRO_MAC',
        'IDENTIDAD',
        'N_MODULO',
        'ESTADO',
        'FECHAINICIO',
        'FECHAFIN'
    ];

    // Campos de fecha que deberían ser convertidos a instancias de Carbon
    protected $dates = [
        'FECHAINICIO',
        'FECHAFIN',
        'CREATED_AT',
        'UPDATED_AT'
    ];

    // Si tienes relaciones, asegúrate de configurarlas aquí
  
    public function entidad()
{
    return $this->belongsTo(Entidad::class, 'IDENTIDAD');
}

    public function centroMac()
    {
        return $this->belongsTo(Mac::class, 'IDCENTRO_MAC');
    }
    public function personal()
    {
        return $this->hasMany(Personal::class, 'IDMODULO');
    }
    public function itinerantes()
    {
        return $this->hasMany(Itinerante::class, 'IDMODULO');
    }
}
