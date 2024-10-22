<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verificacion extends Model
{
    use HasFactory;

    protected $table = 'm_verificacion'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'ModuloDeRecepcion',
        'OrdenadoresDeFila',
        'SillasDeOrientadores',
        'Ticketera',
        'LectorDeCodBarras',
        'ServicioDeTelefonia1800',
        'InsumoRecepcion',
        'SillaRuedas',
        'TvZonaAtencion',
        'SillasEspera',
        'SillasAtencion',
        'ModuloAtencion',
        'PcAsesores',
        'ImpresorasZonaAtencion',
        'InsumoMateriales',
        'ModuloOficina',
        'SillaOficina',
        'InsumoOficina',
        'SistemaIluminaria',
        'OrdenLimpieza',
        'Senialeticas',
        'EquipoAireAcondicionado',
        'ServiciosHigienicos',
        'Comedor',
        'Internet',
        'SistemasColas',
        'SistemaDeCitas',
        'SistemaAudio',
        'SistemaVideovigilancia',
        'CorreoElectronico',
        'ActiveDirectory',
        'FileServer',
        'Antivirus',
        'Observaciones',
        'AperturaCierre',
        'Fecha',
        'user_id', // Agregar el campo user_id
        'SillaAsesor', // Agregar aquí
    ];

    protected $casts = [
        'ModuloDeRecepcion' => 'boolean',
        'OrdenadoresDeFila' => 'boolean',
        'SillasDeOrientadores' => 'boolean',
        'Ticketera' => 'boolean',
        'LectorDeCodBarras' => 'boolean',
        'ServicioDeTelefonia1800' => 'boolean',
        'InsumoRecepcion' => 'boolean',
        'SillaRuedas' => 'boolean',
        'TvZonaAtencion' => 'boolean',
        'SillasEspera' => 'boolean',
        'SillasAtencion' => 'boolean',
        'ModuloAtencion' => 'boolean',
        'PcAsesores' => 'boolean',
        'ImpresorasZonaAtencion' => 'boolean',
        'InsumoMateriales' => 'boolean',
        'ModuloOficina' => 'boolean',
        'SillaOficina' => 'boolean',
        'InsumoOficina' => 'boolean',
        'SistemaIluminaria' => 'boolean',
        'OrdenLimpieza' => 'boolean',
        'Senialeticas' => 'boolean',
        'EquipoAireAcondicionado' => 'boolean',
        'ServiciosHigienicos' => 'boolean',
        'Comedor' => 'boolean',
        'Internet' => 'boolean',
        'SistemasColas' => 'boolean',
        'SistemaDeCitas' => 'boolean',
        'SistemaAudio' => 'boolean',
        'SistemaVideovigilancia' => 'boolean',
        'CorreoElectronico' => 'boolean',
        'ActiveDirectory' => 'boolean',
        'FileServer' => 'boolean',
        'Antivirus' => 'boolean',
        'SillaAsesor' => 'boolean', // Agregar aquí
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Cambia 'user_id' por el nombre correcto si es diferente
    }
}
