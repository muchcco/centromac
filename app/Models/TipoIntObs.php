<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoIntObs extends Model
{
    use HasFactory;

    protected $table = 'm_tipo_int_obs'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'id_tipo_int_obs'; // Clave primaria
    public $timestamps = true; // Manejo de created_at y updated_at

    protected $fillable = [
        'tipo',
        'tipo_obs',       // INTERRUPCION u OBSERVACION
        'numeracion',
        'nom_tipo_int_obs',
        'status',         // 1 = ACTIVO, 2 = INACTIVO
        'descripcion',    // Nuevo campo agregado
    ];
}
