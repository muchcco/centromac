<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'M_ASIGNACION_BIEN';

    protected $primaryKey = 'IDASIGNACION';

    protected $fillable = [  
                            'IDASIGNACION', 
                            'IDCENTRO_MAC',                            
                            'IDPERSONAL', 
                            'IDALMACEN', 
                            'ESTADO_BIEN',
                            'OBSERVACION',
                            'FECHA_ENTREGA',
                            'FLAG',                 
                        ];

    public $timestamps = true;
}
