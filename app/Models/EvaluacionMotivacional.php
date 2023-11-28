<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionMotivacional extends Model
{
    use HasFactory;

    protected $table = 'F_EVAL_MOTIVACIONAL';

    protected $primaryKey = 'IDEEVAL_MOTIVACIONAL';

    protected $fillable = [  
                            'IDPERSONAL', 
                            'IDENTIDAD',                            
                            'IDCENTRO_MAC', 
                            'PROACTIVIDAD', 
                            'CALIDAD_SERVICIO',
                            'COMPROMISO'                          ,
                            'VESTIMENTA',
                            'TOTAL_P',
                            'MES',
                            'AÑO',
                            'FLAG',
                            'IDPERSONA_REGISTRA',                            
                        ];

    public $timestamps = false;
}
