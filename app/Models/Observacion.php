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
        'idcentro_mac',
        'responsable',
        'servicio_involucrado',
        'descripcion',
        'descripcion_accion',
        'fecha_observacion',
        'fecha_solucion',
        'estado',
        'archivo',
        'observado',
        'retroalimentacion',
        'observado_por',
        'fecha_observado',
        'corregido',
        'corregido_por',
        'fecha_corregido',
    ];

    // 🔹 ENTIDAD relacionada
    public function entidad()
    {
        return $this->belongsTo(Entidad::class, 'identidad', 'IDENTIDAD');
    }

    // 🔹 Tipo de Interrupción / Incumplimiento
    public function tipoIntObs()
    {
        return $this->belongsTo(TipoIntObs::class, 'id_tipo_int_obs', 'id_tipo_int_obs');
    }

    // 🔹 Centro MAC
    public function centroMac()
    {
        return $this->belongsTo(Mac::class, 'idcentro_mac', 'idcentro_mac');
    }

    // 🔹 Usuario responsable (quien registró)
    public function responsableUsuario()
    {
        return $this->belongsTo(User::class, 'responsable', 'id');
    }

    // 🔹 Usuario que observó
    public function usuarioObservador()
    {
        return $this->belongsTo(User::class, 'observado_por', 'id');
    }

    // 🔹 Usuario que corrigió
    public function usuarioCorrector()
    {
        return $this->belongsTo(User::class, 'corregido_por', 'id');
    }
}
