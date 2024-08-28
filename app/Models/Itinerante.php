<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerante extends Model
{
    use HasFactory;

    protected $table = 'm_itinerante';

    protected $primaryKey = ['IDCENTRO_MAC', 'NUM_DOC', 'IDMODULO']; // Llave primaria compuesta

    public $incrementing = false; // Deshabilitar incremento automático de la clave primaria

    protected $fillable = [
        'IDCENTRO_MAC',
        'NUM_DOC',
        'IDMODULO',
        'fechainicio',
        'fechafin'
    ];

    public $timestamps = false;

    // Relación con el modelo CentroMAC
    public function centroMac()
    {
        return $this->belongsTo(Mac::class, 'IDCENTRO_MAC', 'IDCENTRO_MAC');
    }

    // Relación con el modelo Personal
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'NUM_DOC', 'NUM_DOC');
    }

    // Relación con el modelo Modulo
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'IDMODULO', 'IDMODULO');
    }
}
