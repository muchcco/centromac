<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'M_ALMACEN';

    protected $primaryKey = 'IDALMACEN';

    protected $fillable = [  
                            'IDCENTRO_MAC', 
                            'OC',                            
                            'COD_SBN', 
                            'COD_PRONSACE', 
                            'COD_INTERNO_PCM',
                            'FECHA_OC',
                            'PROVEEDOR',
                            'DESCRIPCION',
                            'MARCA',  
                            'MODELO',
                            'SERIE_MEDIDA',
                            'COLOR',
                            'UBICACION_EQUIPOS',
                            'CANTIDAD',         
                            'FLAG',                 
                        ];

    public $timestamps = true;
}
