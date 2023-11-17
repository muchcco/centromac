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
                            'UBICACION_ANT',                            
                            'NOMBRE_MAC', 
                            'FECHA_APERTURA', 
                            'FECHA_INAGURACION',
                            'FOTO_RUTA',
                            'FLAG'                            
                        ];

    public $timestamps = true;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id', 'IDCENTRO_MAC');
    }
}
