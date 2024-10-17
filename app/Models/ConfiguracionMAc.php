<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionMAc extends Model
{
    use HasFactory;

    // protected $connection = 'mysql2';

    protected $table = 'CONFIGURACION_MAC';

    protected $primaryKey = 'IDCONFIGURACION_MAC';

    protected $fillable = [ 'DESCRIPCION', 'IP', 'HOST', 'VALOR', 'COMENTARIO', 'IDCENTRO_MAC'];

    public $timestamps = false;
}
