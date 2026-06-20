<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaApiToken extends Model
{
    protected $table    = 'asistencia_api_tokens';
    protected $fillable = ['nombre', 'token_hash', 'id_mac', 'activo', 'ultimo_uso'];

    protected $casts = [
        'activo'     => 'boolean',
        'ultimo_uso' => 'datetime',
    ];

    public function syncLogs()
    {
        return $this->hasMany(AsistenciaSyncLog::class, 'token_id');
    }
}
