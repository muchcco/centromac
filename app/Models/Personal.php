<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Asistencia;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'm_personal';

    protected $primaryKey = 'IDPERSONAL';

    protected $fillable = [  
                            'NOMBRE', 
                            'APE_PAT',                            
                            'APE_MAT', 
                            'IDTIPO_DOC', 
                            'NUM_DOC',
                            'SEXO',
                            'IDMAC',
                            'IDENTIDAD',
                            'DIRECCION',
                            'IDDISTRITO_NAC',
                            'FECH_NACIMIENTO',
                            'IDDISTRITO',
                            'TELEFONO',
                            'CELULAR',
                            'CORREO',
                            'GRUPO_SANGUINEO',
                            'FOTO_RUTA',
                            'E_NOMAPE',
                            'E_TELEFONO',
                            'E_CELULAR',
                            'ESTADO_CIVIL',
                            'DF_N_HIJOS',
                            'PD_FECHA_INGRESO',
                            'PD_PUESTO_TRABAJO',
                            'PD_TIEMPO_PTRABAJO',
                            'PD_CENTRO_ATENCION',
                            'PD_CODIGO_IDENTIFICACION',
                            'DLP_FECHA_INGRESO',
                            'DLP_PUESTO_TRABAJO',
                            'DLP_TIEMPO_PTRABAJO',
                            'DLP_AREA_TRABAJO',
                            'DLP_JEFE_INMEDIATO',
                            'DLP_CARGO',
                            'DLP_TELEFONO',
                            'TVL_ID',
                            'TVL_OTRO',
                            'GI_ID',
                            'GI_OTRO',
                            'GI_CARRERA',
                            'GI_DESDE',
                            'GI_HASTA',
                            'FLAG'
                        ];

    public $timestamps = true;

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'NUM_DOC', 'NUM_DOC');
    }
}
