<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'D_ENTIDAD_SERVICIOS';

    protected $primaryKey = 'IDSERVICIOS';

    protected $fillable = [  
                            'NOMBRE_SERVICIO', 
                            'TIPO_SER',                            
                            'COSTO_SERV', 
                            'TRAMITE',
                            'ORIENTACION',
                            'REQUISITO_SERVICIO', 
                            'REQ_CITA',
                            'OBSERVACION',
                            'FLAG',                           
                        ];

    public $timestamps = true;
}
