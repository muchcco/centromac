<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    use HasFactory;

    // Si el nombre de la tabla no es el plural del nombre del modelo
    protected $table = 'feriados'; // Ajusta el nombre de la tabla según corresponda

    // Si la tabla no tiene los campos 'created_at' y 'updated_at'
    public $timestamps = false;

    // Los campos que son asignables en masa
    protected $fillable = [
        'name',
        'fecha',
        'id_centromac',
    ];

    // Los campos que deben ser convertidos a tipo de datos de fecha
    protected $dates = [
        'fecha',
    ];

    // Opcional: Definir relaciones si es necesario
    // Ejemplo de relación con el modelo Mac
    public function mac()
    {
        return $this->belongsTo(Mac::class, 'id_centromac');
    }
}
