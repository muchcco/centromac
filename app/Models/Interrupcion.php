<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interrupcion extends Model
{
    protected $table = 'm_interrupcion';
    protected $primaryKey = 'id_interrupcion';
    public $timestamps = true;

    protected $fillable = [
        'identidad',
        'id_tipo_int_obs',
        'idcentro_mac',
        'responsable',
        'servicio_involucrado',
        'descripcion',
        'descripcion_accion',
        'accion_correctiva',
        'fecha_inicio',
        'hora_inicio',
        'fecha_fin',
        'hora_fin',
        'estado',
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
