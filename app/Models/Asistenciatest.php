<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistenciatest extends Model
{
    use HasFactory;

    // Especifica el nombre de la tabla si no sigue el formato de pluralización de Laravel
    protected $table = 'asistenciatest';

    // Definimos los campos que se pueden asignar masivamente
    protected $fillable = [
        'correlativo',
        'id',
        'DNI',
        'marcacion',
    ];

    // Desactivar las marcas de tiempo si no las usas en tu tabla
    public $timestamps = false;

    // Si tu tabla tiene una clave primaria personalizada
    protected $primaryKey = 'correlativo';

    // Si la clave primaria no es un incremento automático
    public $incrementing = false;
    protected $keyType = 'string';
}