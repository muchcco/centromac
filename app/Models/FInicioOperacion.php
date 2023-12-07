<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FInicioOperacion extends Model
{
    use HasFactory;

    protected $table = 'F_MAC_03_VER_OPERACION';

    protected $primaryKey = 'IDFMAC_02';

    protected $fillable = [  
                            'IDDESC_FORM', 
                            'CONFORMIDAD_I',                            
                            'CONFORMIDAD_F', 
                            'OBSERVACION_F02',                
                            'ZONA_OBSERV',
                            'DIA',
                            'MES',
                            'AÑO',
                            'FECHA',
                            'HORA',
                            'IDCENTRO_MAC',
                        ];

    public $timestamps = true;
}
