<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescripcionFormato extends Model
{
    use HasFactory;

    protected $table = 'D_DESCRIPCION_FORMATOS';

    protected $primaryKey = 'IDDESC_FORM';

    protected $fillable = [  
                            'TIPO_FORMATO', 
                            'IDPADRE_F',                            
                            'DESCRIPCION_F', 
                            'FLAG',                
                        ];

    public $timestamps = true;
}
