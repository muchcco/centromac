<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Verificacion;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class VerificacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Verificacion::query();
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Filtrar solo por user_id
        $query->where('user_id', $user->id); // El user_id en verificacions

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        }

        $verificaciones = $query->orderBy('Fecha', 'desc')->get();

        return view('verificaciones.index', compact('verificaciones'));
    }


    public function create()
    {
        return view('verificaciones.create');
    }

    public function show($fecha)
    {
        // Convertir la fecha a un objeto Carbon
        if (!Carbon::hasFormat($fecha, 'Y-m-d')) {
            return redirect()->back()->with('error', 'Formato de fecha no válidoAAAAAAA.');
        }

        // Convertir la fecha a un objeto Carbon
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $verificaciones = Verificacion::with('user')->whereDate('Fecha', $fechaCarbon)->get(); // Cargar la relación user

        // Mapear los nombres completos de los campos
        $campos = [
            'ModuloDeRecepcion' => 'Modulo de Recepcion',
            'OrdenadoresDeFila' => 'Ordenadores de Fila',
            'SillasDeOrientadores' => 'Sillas de Orientadores',
            'Ticketera' => 'Ticketera (Sistema de Colas)',
            'LectorDeCodBarras' => 'Lector de Codigo de Barras',
            'ServicioDeTelefonia1800' => 'Servicio de Telefonia 1800',
            'InsumoRecepcion' => 'Insumos y Materiales Zona Recepción',
            'SillaRuedas' => 'Sillas de Ruedas',
            'TvZonaAtencion' => 'Televisores en Zona de Atencion',
            'SillasEspera' => 'Sillas de Espera',
            'SillaAsesor' => 'Sillas de Asesor',
            'SillasAtencion' => 'Sillas de Atencion al Ciudadano',
            'ModuloAtencion' => 'Modulo de Atencion',
            'PcAsesores' => 'Computadoras de Asesores',
            'ImpresorasZonaAtencion' => 'Impresoras en Zonas de Atencion',
            'InsumoMateriales' => 'Insumos y Materiales Zona Atencion',
            'ModuloOficina' => 'Modulo de Oficina',
            'SillaOficina' => 'Sillas de Oficina',
            'InsumoOficina' => 'Insumos y Material para Oficina',
            'SistemaIluminaria' => 'Sistema de Iluminacion',
            'OrdenLimpieza' => 'Orden y Limpieza',
            'Senialeticas' => 'Señaleticas',
            'EquipoAireAcondicionado' => 'Equipos de Aire Acondicionado',
            'ServiciosHigienicos' => 'Servicios Higienicos',
            'Comedor' => 'Comedor',
            'Internet' => 'Internet',
            'SistemasColas' => 'Sistema de Colas',
            'SistemaDeCitas' => 'Sistema de Citas',
            'SistemaAudio' => 'Sistema de Audio',
            'SistemaVideovigilancia' => 'Sistema de VideoVigilancia',
            'CorreoElectronico' => 'Correo Electronico',
            'ActiveDirectory' => 'Active Directory',
            'FileServer' => 'File Server',
            'Antivirus' => 'Antivirus',
        ];

        // Inicializar arrays para las observaciones
        $observacionesApertura = [];
        $observacionesRelevo = [];
        $observacionesCierre = [];

        foreach ($verificaciones as $verificacion) {
            // Procesar observaciones
            $observacionesArray = explode("\n", $verificacion->Observaciones);
            foreach ($observacionesArray as $observacion) {
                if (preg_match('/-Observación de (\w+): (.+)/', $observacion, $matches)) {
                    $campo = $matches[1];
                    $textoObservacion = $matches[2];

                    // Clasificar las observaciones por el tipo de Apertura/Cierre
                    switch ($verificacion->AperturaCierre) {
                        case 0: // Apertura
                            $observacionesApertura[$campo] = $textoObservacion;
                            break;
                        case 1: // Relevo
                            $observacionesRelevo[$campo] = $textoObservacion;
                            break;
                        case 2: // Cierre
                            $observacionesCierre[$campo] = $textoObservacion;
                            break;
                    }
                }
            }
        }

        // Retornar la vista con los datos
        return view('verificaciones.show', compact('verificaciones', 'campos', 'fechaCarbon', 'observacionesApertura', 'observacionesRelevo', 'observacionesCierre'));
    }


    public function store(Request $request)
    {
        // Validaciones iniciales
        $request->validate([
            'AperturaCierre' => 'required|in:0,1,2', // Cambiado para permitir 0, 1 y 2
            'Fecha' => 'required|date',
            'ModuloDeRecepcion' => 'required|boolean',
            'Fecha' => [
                Rule::unique('m_verificacion', 'Fecha')->where(function ($query) use ($request) {
                    return $query->where('Fecha', $request->Fecha)
                        ->where('AperturaCierre', $request->AperturaCierre);
                }),
            ],
        ], [
            'Fecha.unique' => 'Ya existe una verificación para esta fecha y tipo de apertura/cierre.',
            'AperturaCierre.in' => 'El campo Apertura/Cierre debe ser 0 (Apertura), 1 (Relevo) o 2 (Cierre).', // Mensaje de error personalizado
        ]);

        try {
            // Crear y llenar el modelo de Verificación
            $verificacion = new Verificacion();
            $verificacion->fill($request->all());

            // Agregar el user_id
            $verificacion->user_id = auth()->user()->id; // Asegúrate de que este es el campo correcto en tu tabla

            // Recoger y concatenar observaciones
            $observaciones = '';
            $campos = [
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
                'SillaAsesor',
            ];

            foreach ($campos as $campo) {
                $observacion = $request->input('observaciones_' . $campo);
                if (!empty($observacion)) {
                    $observaciones .= "-Observación de " . $campo . ": " . $observacion . "\n";
                }
            }

            // Asignar las observaciones al modelo
            $verificacion->Observaciones = $observaciones;

            // Guardar el modelo
            $verificacion->save();

            // Redirigir con un mensaje de éxito
            return redirect()->route('verificaciones.index')->with('success', 'Verificación creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo crear la verificación. Error: ' . $e->getMessage());
        }
    }
    public function edit(Request $request)
    {
        // Obtener la fecha y el estado de Apertura/Cierre del request
        $fecha = $request->input('Fecha');
        $aperturaCierre = $request->input('AperturaCierre');

        // Obtener la verificación correspondiente a la fecha y estado
        $verificacion = Verificacion::where('Fecha', $fecha)
            ->where('AperturaCierre', $aperturaCierre)
            ->first();

        // Si no se encuentra la verificación, manejarlo según tu lógica
        if (!$verificacion) {
            return redirect()->route('verificaciones.index')->with('error', 'No se encontraron verificaciones para esta fecha y estado.');
        }

        // Obtener las verificaciones del día
        $verificacionesDelDia = Verificacion::where('Fecha', $fecha)->get();

        // Separar observaciones por campo
        $observaciones = [];
        $observacionesArray = explode("\n", $verificacion->Observaciones);

        foreach ($observacionesArray as $observacion) {
            if (preg_match('/-Observación de (\w+): (.+)/', $observacion, $matches)) {
                $campo = $matches[1]; // El nombre del campo
                $textoObservacion = $matches[2]; // El texto de la observación
                $observaciones[$campo] = $textoObservacion; // Asignar la observación al campo correspondiente
            }
        }

        // Retornar la vista con los datos y las observaciones separadas
        return view('verificaciones.edit', compact('verificacion', 'verificacionesDelDia', 'observaciones'));
    }

    public function update(Request $request, Verificacion $verificacion)
    {
        try {
            // Convertir la fecha a un objeto Carbon
            $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $request->Fecha . ' 00:00:00'); // Asegúrate de incluir la hora
            if (!$fecha) {
                return redirect()->back()->with('error', 'Formato de fecha no válido.');
            }

            // Recoger y concatenar observaciones
            $observaciones = '';
            $campos = [
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
                'SillaAsesor',
            ];
            foreach ($campos as $campo) {
                $observacion = $request->input('observaciones_' . $campo);
                if (!empty($observacion)) {
                    $observaciones .= "-Observación de " . $campo . ": " . $observacion . "\n";
                }
            }

            // Asignar los datos del formulario al modelo
            $verificacion->fill($request->except(['observaciones_ModuloDeRecepcion', /* otros campos de observación */]));

            // Asegúrate de que AperturaCierre sea un entero
            $verificacion->AperturaCierre = (int)$request->AperturaCierre;

            // Asignar las observaciones al modelo
            $verificacion->Observaciones = $observaciones;

            // Actualizar la fecha en el modelo
            $verificacion->Fecha = $fecha; // Asegúrate de asignar la fecha correctamente

            // Guardar los cambios
            $verificacion->save();

            // Redirigir con un mensaje de éxito
            return redirect()->route('verificaciones.index')->with('success', 'Verificación actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo actualizar la verificación. Error: ' . $e->getMessage());
        }
    }

    public function destroy(Verificacion $verificacion)
    {
        $verificacion->delete();
        return redirect()->route('verificaciones.index')
            ->with('success', 'Verificación eliminada exitosamente.');
    }
    public function contingencia()
    {
        // Obtener todas las verificaciones ordenadas por fecha
        $verificaciones = Verificacion::orderBy('Fecha')->get();

        // Obtener la lista de campos
        $campos = [
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
            'SillaAsesor', // Asegúrate de incluir "SillaAsesor" si es necesario
        ];

        // Organizar las verificaciones por fecha y tipo (Apertura, Relevo, Cierre)
        $tablaContingencia = [];
        foreach ($verificaciones as $verificacion) {
            $fecha = Carbon::parse($verificacion->Fecha)->format('Y-m-d'); // Formatear la fecha
            $tipo = $verificacion->AperturaCierre; // Mantener el valor original (0, 1 o 2)

            // Inicializar el array para la fecha si no existe
            if (!isset($tablaContingencia[$fecha])) {
                $tablaContingencia[$fecha] = ['Apertura' => [], 'Relevo' => [], 'Cierre' => []]; // Agregar Relevo aquí
            }

            // Transformar los valores booleanos a 'Sí' o 'No'
            $verificacionData = $verificacion->only($campos);
            foreach ($verificacionData as $key => $value) {
                $verificacionData[$key] = $value ? 'Sí' : 'No';
            }

            // Asignar los datos según el tipo de verificación
            if ($tipo == 0) { // Apertura
                $tablaContingencia[$fecha]['Apertura'] = $verificacionData;
            } elseif ($tipo == 1) { // Relevo
                $tablaContingencia[$fecha]['Relevo'] = $verificacionData;
            } elseif ($tipo == 2) { // Cierre
                $tablaContingencia[$fecha]['Cierre'] = $verificacionData;
            }
        }

        // Retornar la vista con los datos
        return view('verificaciones.contingencia', compact('tablaContingencia', 'campos'));
    }

    public function filtrar(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // Obtener las verificaciones dentro del rango de fechas
        $verificaciones = Verificacion::whereBetween('Fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->orderBy('Fecha')
            ->get();

        // Obtener la lista de campos
        $campos = [
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
        ];

        // Organizar las verificaciones por fecha y tipo (Apertura/Cierre)
        $tablaContingencia = [];
        foreach ($verificaciones as $verificacion) {
            $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $verificacion->Fecha)->format('Y-m-d'); // Formatear la fecha
            $tipo = $verificacion->AperturaCierre ? 'Apertura' : 'Cierre';
            if (!isset($tablaContingencia[$fecha])) {
                $tablaContingencia[$fecha] = ['Apertura' => [], 'Cierre' => []];
            }
            // Transformar los valores booleanos a 'Sí' o 'No'
            $verificacionData = $verificacion->only($campos);
            foreach ($verificacionData as $key => $value) {
                $verificacionData[$key] = $value ? 'Sí' : 'No';
            }
            $tablaContingencia[$fecha][$tipo] = $verificacionData;
        }
        // Retornar la vista con los datos filtrados
        return view('verificaciones.contingencia', compact('tablaContingencia', 'campos'));
    }


    public function observaciones(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        // Si no se proporcionan fechas, obtener los últimos 20 registros
        if (!$request->has('fecha_inicio') || !$request->has('fecha_fin')) {
            $verificaciones = Verificacion::orderBy('Fecha', 'desc')
                ->take(20)
                ->get();
        } else {
            // Obtener las verificaciones dentro del rango de fechas
            $verificaciones = Verificacion::whereBetween('Fecha', [$request->fecha_inicio, $request->fecha_fin])
                ->orderBy('Fecha')
                ->get();
        }

        // Obtener la lista de campos
        $campos = [
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
        ];

        $verificacionesInfo = [];
        foreach ($verificaciones as $verificacion) {
            $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $verificacion->Fecha)->format('Y-m-d');

            // Ajuste para tipo de ejecución
            switch ($verificacion->AperturaCierre) {
                case 0:
                    $tipoEjecucion = 'Apertura';
                    break;
                case 1:
                    $tipoEjecucion = 'Relevo';
                    break;
                case 2:
                    $tipoEjecucion = 'Cierre';
                    break;
                default:
                    $tipoEjecucion = 'Desconocido'; // Manejo de casos no esperados
            }

            // Generar hora aleatoria para apertura y cierre
            $hora = $this->generarHoraAleatoria($verificacion->AperturaCierre);

            $observaciones = $verificacion->Observaciones; // Asumiendo que hay un campo Observaciones

            // Calcular el porcentaje de 'Sí'
            $totalCampos = count($campos);
            $camposSi = count(array_filter($verificacion->only($campos), function ($value) {
                return $value == 1;
            }));
            $porcentajeSi = ($camposSi / $totalCampos) * 100;

            $verificacionesInfo[] = [
                'tipoEjecucion' => $tipoEjecucion,
                'observaciones' => $observaciones,
                'fecha' => $fecha,
                'hora' => $hora,
                'porcentajeSi' => round($porcentajeSi, 2),
                'responsable' => $verificacion->user->name, // Suponiendo que el nombre está en el modelo User
            ];
        }

        return view('verificaciones.observaciones', compact('verificacionesInfo'));
    }
    private function generarHoraAleatoria($aperturaCierre)
{
    switch ($aperturaCierre) {
        case 0: // Apertura
            $min = strtotime("08:15");
            $max = strtotime("08:30");
            break;
        case 1: // Relevo
            $min = strtotime("13:30");
            $max = strtotime("13:45"); // Puedes ajustar este rango si es necesario
            break;
        case 2: // Cierre
            $min = strtotime("17:00");
            $max = strtotime("17:15");
            break;
        default:
            return null; // Manejo de errores, si el estado no es válido
    }
    
    $randTime = rand($min, $max);
    return date('H:i', $randTime);
}

}
