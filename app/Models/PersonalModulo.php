<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalModulo extends Model
{
    use HasFactory;

    // Especificar el nombre de la tabla
    protected $table = 'm_personal_modulo';

    // Especificar los campos que se pueden llenar masivamente
    protected $fillable = [
        'num_doc',
        'idmodulo',
        'idcentro_mac',
        'fechainicio',
        'fechafin',
    ];

    // Definir si el modelo usa incrementing
    public $incrementing = true;

    // Si tu clave primaria es un número entero
    protected $keyType = 'int';

    // Si no usas marcas de tiempo (created_at y updated_at)
    public $timestamps = false;

    /**
     * Relación con el modelo MPersonal
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'num_doc', 'NUM_DOC');
    }

    /**
     * Relación con el modelo MModulo
     */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'idmodulo', 'IDMODULO');
    }

    /**
     * Relación con el modelo MCentroMac
     */
    public function centroMac()
    {
        return $this->belongsTo(MAC::class, 'idcentro_mac', 'IDCENTRO_MAC');
    }
}
