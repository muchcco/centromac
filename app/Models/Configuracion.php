<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'CONFIGURACION_SIST';

    protected $primaryKey = 'IDCONFIGURACION';

    protected $fillable = [ 'PARAMETRO', 'DESCRIPCION', 'FLAG', 'VALOR', 'MENSAJE'];

    public $timestamps = false;
}
