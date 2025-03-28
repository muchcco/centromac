<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'm_observacion';
    protected $primaryKey = 'id_observacion';
    public $timestamps = true;

    protected $fillable = [
        'identidad',
        'id_tipo_int_obs',
        'idcentro_mac', // ✅ debe coincidir con la BD
        'responsable',
        'servicio_involucrado',
        'descripcion',
        'descripcion_accion',
        'fecha_observacion',
        'fecha_solucion',
        'estado',
        'archivo',
    ];


    public function entidad()
    {
        return $this->belongsTo(Entidad::class, 'identidad', 'IDENTIDAD');
    }

    public function tipoIntObs()
    {
        return $this->belongsTo(TipoIntObs::class, 'id_tipo_int_obs', 'id_tipo_int_obs');
    }

    public function centroMac()
    {
        return $this->belongsTo(Mac::class, 'idcentro_mac', 'idcentro_mac');
    }

    public function responsableUsuario()
    {
        return $this->belongsTo(User::class, 'responsable', 'id');
    }
}
