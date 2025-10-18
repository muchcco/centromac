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
        'fecha_inicio',
        'hora_inicio',
        'fecha_fin',
        'hora_fin',
        'estado',
        'observado',
        'retroalimentacion',
        'observado_por',
        'fecha_observado',
        'corregido',
        'corregido_por',
        'fecha_corregido',
    ];

    // 🔹 Relaciones existentes
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

    // 🔹 Usuario que observó
    public function usuarioObservador()
    {
        return $this->belongsTo(User::class, 'observado_por', 'id');
    }

    // 🔹 Usuario que corrigió
    public function usuarioCorregidor()
    {
        return $this->belongsTo(User::class, 'corregido_por', 'id');
    }
}
