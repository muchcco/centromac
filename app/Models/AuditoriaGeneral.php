<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaGeneral extends Model
{
    // Nombre de la tabla explícito, ya que no es plural
    protected $table = 'auditorias_generales';

    // Clave primaria
    protected $primaryKey = 'idAuditoria';

    // No usas timestamps automáticos (created_at, updated_at)
    public $timestamps = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'idUsuario',
        'modelo_afectado',
        'idRegistroAfectado',
        'accion',
        'valores_anteriores',
        'valores_nuevos',
        'fecha_accion',
        'ip_usuario',
        'descripcion',
        'tabla_id_nombre',
    ];

    // Si quieres convertir los JSON automáticamente a array
    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
    ];
}
