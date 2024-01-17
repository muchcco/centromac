<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLibroFelicitacion extends Model
{
    use HasFactory;

    protected $table = 'F_LIBRO_FELICITACIONES';

    protected $primaryKey = 'IDLIBRO_FELICITACION';

    protected $fillable = [  
                            'IDPER_REGISTRA', 
                            'IDCENTRO_MAC',                            
                            'CORRELATVIO', 
                            'AÑO',
                            'MES',
                            'R_FECHA',
                            'R_NOMBRE',
                            'R_APE_PAT',
                            'R_APE_MAT',
                            'IDTIPO_DOC',
                            'R_NUM_DOC',
                            'R_CORREO',
                            'R_DESCRIPCION',
                            'IDENTIDAD',
                            'IDPERSONAL',
                            'R_ARCHIVO_NOM',
                            'R_ARCHIVO_RUT',
                            'FLAG'
                        ];

    public $timestamps = true;
}
