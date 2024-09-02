<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerante extends Model
{
    use HasFactory;

    protected $table = 'm_itinerante';
    protected $primaryKey = 'ID'; // Define la clave primaria
    public $timestamps = false;

    protected $fillable = [
        'IDCENTRO_MAC',
        'NUM_DOC',
        'IDMODULO',
        'fechainicio',
        'fechafin'
    ];
    protected $dates = [
        'fechainicio',
        'fechafin'
    ];
    // Relación con el modelo CentroMac
    public function centroMac()
    {
        return $this->belongsTo(MAC::class, 'IDCENTRO_MAC', 'IDCENTRO_MAC');
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
