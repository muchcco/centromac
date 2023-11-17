<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivoper extends Model
{
    use HasFactory;
    
    protected $table = 'A_PERSONAL';

    protected $primaryKey = 'IDARCHIVO_PERSONAL';

    protected $fillable = [  
                            'IDPERSONAL', 
                            'NOMBRE_RUTA',                            
                            'NOMBRE_ARCHIVO', 
                            'TIPO_DOC', 
                            'PESO_DOC',
                            'FORMATO_DOC',
                            'CORRELATIVO',
                            'FLAG',
                            'FEHCA_CREACION',                           
                        ];

    public $timestamps = false;
}
