<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsigPersonal extends Model
{
    use HasFactory;

    protected $table = 'M_ASIG_PERSONAL';

    protected $primaryKey = 'IDASIG_PERSONAL';

    protected $fillable = [  
                            'IDPERSONAL', 
                            'IDESTADO_ASIG',                            
                            'FECHA_ENTREGA', 
                            'FECHA_DEVOLUCION',               
                        ];

    public $timestamps = true;
}
