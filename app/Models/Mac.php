<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Mac extends Model
{
    use HasFactory;

    protected $table = 'm_centro_mac';

    protected $primaryKey = 'IDCENTRO_MAC';

    protected $fillable = [  
                            'UBICACION', 
                            'DIRECCION_MAC',
                            'UBICACION_ANT',                            
                            'NOMBRE_MAC', 
                            'FECHA_APERTURA', 
                            'FECHA_INAGURACION',
                            'FOTO_RUTA',
                            'FLAG',
                            'FECHA_CREACION'                        
                        ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id', 'IDCENTRO_MAC');
    }
}
