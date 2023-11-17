<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'm_asistencia';

    protected $primaryKey = 'IDASISTENCIA';

    protected $fillable = [  
                            'IDTIPO_ASISTENCIA', 
                            'NUM_DOC',                            
                            'IDCENTRO_MAC', 
                            'MES', 
                            'AÑO',
                            'FECHA',
                            'HORA',
                            'FECHA_BIOMETRICO',
                            'NUM_BIOMETRICO',
                            'CORRELATIVO',
                            'CORRELATIVO_DIA'                            
                        ];

    public $timestamps = false;
}
