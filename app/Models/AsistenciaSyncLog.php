<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaSyncLog extends Model
{
    protected $table      = 'asistencia_sync_logs';
    public    $timestamps = true;
    const     UPDATED_AT  = null; // la tabla sólo tiene created_at

    protected $fillable = [
        'token_id', 'id_mac', 'total_recibidos', 'total_insertados',
        'total_duplicados', 'status', 'mensaje', 'ip_origen',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function token()
    {
        return $this->belongsTo(AsistenciaApiToken::class, 'token_id');
    }
}
