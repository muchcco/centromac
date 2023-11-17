<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;

    protected $table = 'm_entidad';

    protected $primaryKey = 'IDENTIDAD';

    protected $fillable = [  
                            'NOMBRE_ENTIDAD', 
                            'RUC_ENTIDAD',                            
                            'IDNOVO', 
                            'ABREV_ENTIDAD', 
                            'NOMBRE_NOVO',
                            'FLAG'                          
                        ];

    public $timestamps = false;
}
