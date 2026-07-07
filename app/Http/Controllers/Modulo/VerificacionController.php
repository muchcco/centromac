<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Verificacion;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Exports\VerificacionesExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class VerificacionController extends Controller
{
    private function camposChecklist()
    {
        return [
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
            'SillaAsesor'
        ];
    }

    private function generarObservaciones($request)
    {
        $obs = '';
        foreach ($this->camposChecklist() as $campo) {
            $o = $request->input('observaciones_' . $campo);
            if (!empty($o)) $obs .= "-Observación de {$campo}: {$o}\n";
        }
        return $obs;
    }

    public function index(Request $request)
    {
        $query = Verificacion::where('id_centromac', auth()->user()->idcentro_mac);

        if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
            $query->whereBetween('Fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $data = $query
            ->orderByDesc('Fecha')
            ->get() // ⚠️ IMPORTANTE: NO paginate aquí
            ->groupBy('Fecha');

        return view('verificaciones.index', compact('data'));
    }
    public function verificarFecha(Request $request)
    {
        $fecha = $request->fecha;

        $registros = Verificacion::whereDate('Fecha', $fecha)
            ->where('id_centromac', auth()->user()->idcentro_mac)
            ->pluck('AperturaCierre')
            ->toArray();

        return response()->json([
            'apertura' => in_array(0, $registros),
            'relevo'   => in_array(1, $registros),
            'cierre'   => in_array(2, $registros),
        ]);
    }
    public function create(Request $request)
    {
        $fecha = $request->fecha ?? now()->format('Y-m-d');

        $idmac = auth()->user()->idcentro_mac;

        $registros = Verificacion::whereDate('Fecha', $fecha)
            ->where('id_centromac', $idmac)
            ->pluck('AperturaCierre')
            ->toArray();

        $centroMac = DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', $idmac)
            ->value('NOMBRE_MAC'); // 👈 SOLO TRAE EL NOMBRE

        return view('verificaciones.create', [
            'fecha' => $fecha,
            'yaApertura' => in_array(0, $registros),
            'yaRelevo' => in_array(1, $registros),
            'yaCierre' => in_array(2, $registros),
            'nombreMac' => $centroMac // 🔥 NUEVO
        ]);
    }
    public function store(Request $request)
    {
        try {
            $fecha = Carbon::parse($request->Fecha)->startOfDay();
            $request->validate([
                'AperturaCierre' => 'required|in:0,1,2',
                'Fecha' => 'required|date|before_or_equal:today',
                'ModuloDeRecepcion' => 'required|boolean'
            ]);
            $idmac = auth()->user()->idcentro_mac;
            $existe = Verificacion::where('id_centromac', $idmac)
                ->where('AperturaCierre', $request->AperturaCierre)
                ->whereDate('Fecha', $fecha)
                ->exists();

            if ($existe) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'Fecha' => $this->mensajeTipo($request->AperturaCierre)
                    ]);
            }
            $v = new Verificacion();
            $v->fill($request->all());
            $v->Fecha = $fecha;
            $v->hora_registro = now();
            $v->user_id = auth()->id();
            $v->id_centromac = $idmac;
            $v->Observaciones = $this->generarObservaciones($request);

            $v->save();

            return redirect()
                ->route('verificaciones.index')
                ->with('success', 'Verificación registrada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'Fecha' => $this->mensajeTipo($request->AperturaCierre)
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'Error BD: ' . $e->getMessage());
        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    private function mensajeTipo($tipo)
    {
        return match ((int)$tipo) {
            0 => 'La APERTURA ya fue registrada para esta fecha.',
            1 => 'El RELEVO ya fue registrado para esta fecha.',
            2 => 'El CIERRE ya fue registrado para esta fecha.',
            default => 'Registro duplicado.'
        };
    }
    public function show($fecha)
    {
        // 🔴 Validar formato
        if (!Carbon::hasFormat($fecha, 'Y-m-d')) {
            return back()->with('error', 'Formato inválido');
        }

        $fechaCarbon = Carbon::parse($fecha);

        // 🔥 Traer verificaciones
        $verificaciones = Verificacion::with('user')
            ->whereDate('Fecha', $fechaCarbon)
            ->where('id_centromac', auth()->user()->idcentro_mac)
            ->get();

        // 🔥 Separar por tipo (clave para la vista)
        $verificacionesInfo = [
            'apertura' => null,
            'relevo'   => null,
            'cierre'   => null,
        ];

        foreach ($verificaciones as $v) {
            if ($v->AperturaCierre == 0) $verificacionesInfo['apertura'] = $v;
            if ($v->AperturaCierre == 1) $verificacionesInfo['relevo']   = $v;
            if ($v->AperturaCierre == 2) $verificacionesInfo['cierre']   = $v;
        }

        // 🔥 Observaciones separadas
        $observacionesApertura = [];
        $observacionesRelevo   = [];
        $observacionesCierre   = [];

        foreach ($verificaciones as $v) {

            if (!$v->Observaciones) continue;

            foreach (explode("\n", $v->Observaciones) as $obs) {

                if (preg_match('/-Observación de (\w+): (.+)/', $obs, $m)) {

                    if ($v->AperturaCierre == 0) {
                        $observacionesApertura[$m[1]] = $m[2];
                    }

                    if ($v->AperturaCierre == 1) {
                        $observacionesRelevo[$m[1]] = $m[2];
                    }

                    if ($v->AperturaCierre == 2) {
                        $observacionesCierre[$m[1]] = $m[2];
                    }
                }
            }
        }

        // 🔥 Centro MAC (tu vista lo usa)
        $centroMac = DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', auth()->user()->idcentro_mac)
            ->first();

        // 🔥 USAR TU FUNCIÓN (SIN DUPLICAR)
        $campos = [
            'ModuloDeRecepcion' => 'Modulo de Recepción',
            'OrdenadoresDeFila' => 'Ordenadores de Fila',
            'SillasDeOrientadores' => 'Sillas de Orientadores',
            'Ticketera' => 'Ticketera',
            'LectorDeCodBarras' => 'Lector de Código de Barras',
            'ServicioDeTelefonia1800' => 'Telefonía 1800',
            'InsumoRecepcion' => 'Insumos Recepción',
            'SillaRuedas' => 'Silla de Ruedas',
            'TvZonaAtencion' => 'TV Zona Atención',
            'SillasEspera' => 'Sillas de Espera',
            'SillaAsesor' => 'Silla de Asesor',
            'SillasAtencion' => 'Sillas de Atención',
            'ModuloAtencion' => 'Módulo de Atención',
            'PcAsesores' => 'PC Asesores',
            'ImpresorasZonaAtencion' => 'Impresoras',
            'InsumoMateriales' => 'Insumos Materiales',
            'ModuloOficina' => 'Módulo Oficina',
            'SillaOficina' => 'Silla Oficina',
            'InsumoOficina' => 'Insumo Oficina',
            'SistemaIluminaria' => 'Iluminación',
            'OrdenLimpieza' => 'Orden y Limpieza',
            'Senialeticas' => 'Señaléticas',
            'EquipoAireAcondicionado' => 'Aire Acondicionado',
            'ServiciosHigienicos' => 'Servicios Higiénicos',
            'Comedor' => 'Comedor',
            'Internet' => 'Internet',
            'SistemasColas' => 'Sistema de Colas',
            'SistemaDeCitas' => 'Sistema de Citas',
            'SistemaAudio' => 'Sistema Audio',
            'SistemaVideovigilancia' => 'Videovigilancia',
            'CorreoElectronico' => 'Correo',
            'ActiveDirectory' => 'Active Directory',
            'FileServer' => 'File Server',
            'Antivirus' => 'Antivirus',
        ];

        return view('verificaciones.show', compact(
            'verificaciones',
            'verificacionesInfo', // 🔥 evita errores
            'fechaCarbon',
            'observacionesApertura',
            'observacionesRelevo',
            'observacionesCierre',
            'centroMac',
            'campos'
        ));
    }

    public function edit(Request $request)
    {
        $idmac = auth()->user()->idcentro_mac;

        $v = Verificacion::whereDate('Fecha', $request->Fecha)
            ->where('AperturaCierre', $request->AperturaCierre)
            ->where('id_centromac', $idmac)
            ->firstOrFail();

        $nombreMac = DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', $idmac)
            ->value('NOMBRE_MAC');

        $observaciones = [];

        if ($v->Observaciones) {
            foreach (explode("\n", $v->Observaciones) as $obs) {

                if (preg_match('/-Observación de (\w+): (.+)/', $obs, $m)) {
                    $observaciones[$m[1]] = $m[2];
                }
            }
        }

        return view('verificaciones.edit', [
            'verificacion' => $v,
            'observaciones' => $observaciones,
            'nombreMac' => $nombreMac // 🔥 NUEVO
        ]);
    }

    public function update(Request $request, Verificacion $verificacion)
    {
        try {
            $verificacion->fill($request->all());
            $verificacion->AperturaCierre = (int)$request->AperturaCierre;
            $verificacion->Fecha = Carbon::parse($request->Fecha)->startOfDay();
            $verificacion->Observaciones = $this->generarObservaciones($request);
            $verificacion->save();

            return redirect()->route('verificaciones.index')->with('success', 'Actualizado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Verificacion $verificacion)
    {
        $verificacion->delete();
        return back()->with('success', 'Eliminado.');
    }

    public function contingencia(Request $request)
    {
        $userAuth = auth()->user();

        $mes = $request->mes ?? now()->format('Y-m');

        if ($userAuth->hasRole(['Administrador', 'Moderador'])) {
            $idmac = $request->input('idmac') ?: $userAuth->idcentro_mac;

            $centrosMac = DB::table('m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->orderBy('NOMBRE_MAC')
                ->get();

            $puedeElegirMac = true;
        } else {
            $idmac = $userAuth->idcentro_mac;

            $centrosMac = DB::table('m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->where('IDCENTRO_MAC', $idmac)
                ->get();

            $puedeElegirMac = false;
        }

        $name_mac = DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', $idmac)
            ->value('NOMBRE_MAC') ?? 'Centro MAC';

        $year = substr($mes, 0, 4);
        $month = substr($mes, 5, 2);

        $verificaciones = Verificacion::where('id_centromac', $idmac)
            ->whereYear('Fecha', $year)
            ->whereMonth('Fecha', $month)
            ->get();

        $campos = $this->camposChecklist();

        $tabla = [];

        foreach ($verificaciones as $v) {
            $dia = (int) Carbon::parse($v->Fecha)->format('d');

            if (!isset($tabla[$dia])) {
                $tabla[$dia] = [
                    'Apertura' => [],
                    'Relevo' => [],
                    'Cierre' => []
                ];
            }

            $data = collect($campos)->mapWithKeys(function ($campo) use ($v) {
                return [$campo => (int) $v->$campo];
            })->toArray();

            if ($v->AperturaCierre == 0) {
                $tabla[$dia]['Apertura'] = $data;
            }

            if ($v->AperturaCierre == 1) {
                $tabla[$dia]['Relevo'] = $data;
            }

            if ($v->AperturaCierre == 2) {
                $tabla[$dia]['Cierre'] = $data;
            }
        }

        $matriz = [];

        foreach ($campos as $campo) {
            foreach (range(1, 31) as $dia) {
                $a = $tabla[$dia]['Apertura'][$campo] ?? null;
                $c = $tabla[$dia]['Cierre'][$campo] ?? null;

                if ($a === 1 && $c === 1) {
                    $valor = '✅';
                } elseif ($a !== 1 && $c === 1) {
                    $valor = '❌A';
                } elseif ($a === 1 && $c !== 1) {
                    $valor = '❌C';
                } elseif ($a !== null || $c !== null) {
                    $valor = '❌';
                } else {
                    $valor = '-';
                }

                $matriz[$campo][$dia] = $valor;
            }
        }

        return view('verificaciones.contingencia', compact(
            'matriz',
            'mes',
            'campos',
            'idmac',
            'name_mac',
            'centrosMac',
            'puedeElegirMac'
        ));
    }
    public function detalleContingenciaDia(Request $request)
    {
        $userAuth = auth()->user();

        $request->validate([
            'mes' => 'required|date_format:Y-m',
            'dia' => 'required|integer|min:1|max:31',
            'idmac' => 'nullable|integer',
        ]);

        if ($userAuth->hasRole(['Administrador', 'Moderador'])) {
            $idmac = $request->input('idmac') ?: $userAuth->idcentro_mac;
        } else {
            $idmac = $userAuth->idcentro_mac;
        }

        try {
            $fecha = Carbon::createFromFormat(
                'Y-m-d',
                $request->mes . '-' . str_pad($request->dia, 2, '0', STR_PAD_LEFT)
            );

            if (
                $fecha->format('Y-m') !== $request->mes ||
                (int) $fecha->format('d') !== (int) $request->dia
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha seleccionada no es válida.'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha seleccionada no es válida.'
            ], 422);
        }

        $campos = $this->camposChecklist();

        $registros = Verificacion::with('user')
            ->where('id_centromac', $idmac)
            ->whereDate('Fecha', $fecha->format('Y-m-d'))
            ->orderBy('AperturaCierre')
            ->get();

        $name_mac = DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', $idmac)
            ->value('NOMBRE_MAC') ?? 'Centro MAC';

        $html = view('verificaciones.modals.detalle_contingencia_dia', compact(
            'registros',
            'campos',
            'fecha',
            'name_mac'
        ))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    public function observaciones(Request $request)
    {
        $query = Verificacion::with('user')
            ->where('id_centromac', auth()->user()->idcentro_mac);

        // 🔍 FILTRO FECHAS
        if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
            $query->whereBetween('Fecha', [$request->fecha_inicio, $request->fecha_fin]);
        } else {
            $query->latest('Fecha')->limit(20);
        }

        $verificaciones = $query->get();

        $verificacionesInfo = $verificaciones->map(function ($v) {

            // 🔹 Tipo ejecución
            $tipo = ['Apertura', 'Relevo', 'Cierre'][$v->AperturaCierre] ?? 'X';

            // 🔹 Hora
            $hora = $v->hora_registro
                ? Carbon::parse($v->hora_registro)->format('H:i')
                : '-';

            // 🔥 CAMPOS CHECKLIST
            $campos = collect($v->only($this->camposChecklist()));

            $total = $campos->count();

            // ✅ SOLO contar los que son SI (1)
            $ok = $campos->filter(fn($x) => (int)$x === 1)->count();

            // 🔥 PORCENTAJE REAL
            $porcentaje = $total > 0
                ? round(($ok * 100) / $total, 1)
                : 0;

            // 🔥 OBSERVACIONES BONITAS
            $obs = $this->formatearObservaciones($v->Observaciones);

            return [
                'tipoEjecucion' => $tipo,
                'observaciones' => $obs,
                'fecha' => Carbon::parse($v->Fecha)->format('Y-m-d'),
                'hora' => $hora,
                'porcentajeSi' => $porcentaje,
                'responsable' => $v->user->name ?? '-'
            ];
        });

        return view('verificaciones.observaciones', compact('verificacionesInfo'));
    }
    private function formatearObservaciones($texto)
    {
        if (!$texto) return '-';

        return collect(explode("\n", $texto))
            ->filter()
            ->map(function ($linea) {

                if (preg_match('/-Observación de (\w+): (.+)/', $linea, $m)) {

                    // 🔥 Separar palabras
                    $campo = preg_replace('/([a-z])([A-Z])/', '$1 $2', $m[1]);

                    // 🔥 minúsculas tipo "de"
                    $campo = preg_replace('/\bDe\b/', 'de', $campo);

                    $valor = $m[2];

                    return "{$campo}: {$valor}";
                }

                return $linea;
            })
            ->implode("\n");
    }

    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $idmac = auth()->user()->idcentro_mac;

        $verificaciones = Verificacion::with('user')
            ->whereBetween('Fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->where('id_centromac', $idmac)

            // 🔥 ORDEN PERFECTO
            ->orderBy('Fecha', 'asc')
            ->orderBy('hora_registro', 'asc')
            ->orderByRaw("FIELD(AperturaCierre, 0,1,2)")

            ->get()
            ->map(function ($v) {

                // 🔹 Tipo ejecución
                $tipo = ['Apertura', 'Relevo', 'Cierre'][$v->AperturaCierre] ?? 'X';

                // 🔥 FECHA REAL (EXCEL)
                $fecha = Date::PHPToExcel(
                    \Carbon\Carbon::parse($v->Fecha)
                );

                // 🔥 HORA REAL (SOLO HORA)
                $hora = $v->hora_registro
                    ? Date::PHPToExcel(
                        \Carbon\Carbon::parse($v->hora_registro)
                    )
                    : null;

                // 🔥 CALCULO %
                $campos = collect($v->only($this->camposChecklist()));
                $total = $campos->count();

                $ok = $campos->filter(fn($x) => (int)$x === 1)->count();

                $porcentaje = $total > 0
                    ? round(($ok * 100) / $total, 1)
                    : 0;

                // 🔥 OBSERVACIONES BONITAS
                $obs = $this->formatearObservaciones($v->Observaciones);

                return [
                    'tipoEjecucion' => $tipo,
                    'observaciones' => $obs ?: '-',
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'porcentajeSi' => $porcentaje,
                    'responsable' => $v->user->name ?? '-'
                ];
            });

        $total = $verificaciones->count();

        return Excel::download(
            new VerificacionesExport(
                $verificaciones,
                $request->fecha_inicio,
                $request->fecha_fin,
                $total
            ),
            'verificaciones_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    public function up_time(Request $request)
    {
        $fecha = Carbon::parse($request->fecha)->format('Y-m-d');
        $idmac = auth()->user()->idcentro_mac;

        $inicio = DB::table('m_verificacion')
            ->whereDate('Fecha', $fecha)
            ->where('id_centromac', $idmac)
            ->where('AperturaCierre', 0)
            ->first();

        $fin = DB::table('m_verificacion')
            ->whereDate('Fecha', $fecha)
            ->where('id_centromac', $idmac)
            ->where('AperturaCierre', 2)
            ->first();

        // 🔥 FORMATEO
        if ($inicio && $inicio->hora_registro) {
            $inicio->hora_formateada = Carbon::parse($inicio->hora_registro)->format('H:i');
            $inicio->fecha_formateada = Carbon::parse($inicio->hora_registro)->format('Y-m-d');
        }

        if ($fin && $fin->hora_registro) {
            $fin->hora_formateada = Carbon::parse($fin->hora_registro)->format('H:i');
            $fin->fecha_formateada = Carbon::parse($fin->hora_registro)->format('Y-m-d');
        }

        return response()->json([
            'html' => view('verificaciones.modals.up_time', compact('inicio', 'fin'))->render()
        ]);
    }
    public function update_time(Request $request)
    {
        $request->validate([
            'id_inicio' => 'required',
            'id_fin' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        DB::table('m_verificacion')->where('id', $request->id_inicio)
            ->update(['hora_registro' => $request->fecha_inicio . ' ' . $request->hora_inicio]);

        DB::table('m_verificacion')->where('id', $request->id_fin)
            ->update(['hora_registro' => $request->fecha_fin . ' ' . $request->hora_fin]);

        return response()->json(['success' => true]);
    }
    public function monitoreo(Request $request)
    {
        $userAuth = auth()->user();

        if (!$userAuth->hasRole(['Administrador', 'Moderador'])) {
            abort(403, 'No tiene permisos para acceder al monitoreo.');
        }

        $request->validate([
            'mes' => 'nullable|date_format:Y-m',
        ]);

        $mes = $request->input('mes', now()->format('Y-m'));

        try {
            $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        } catch (\Exception $e) {
            $inicioMes = now()->startOfMonth();
            $mes = $inicioMes->format('Y-m');
        }

        $finMes = $inicioMes->copy()->endOfMonth();
        $dias = range(1, $finMes->daysInMonth);
        $hoy = Carbon::today();

        $macs = DB::table('m_centro_mac')
            ->select(
                'IDCENTRO_MAC as idmac',
                'NOMBRE_MAC as name_mac'
            )
            ->orderBy('NOMBRE_MAC')
            ->get();

        $registros = Verificacion::select(
            'id_centromac',
            'Fecha',
            'AperturaCierre'
        )
            ->whereBetween('Fecha', [
                $inicioMes->format('Y-m-d'),
                $finMes->format('Y-m-d')
            ])
            ->get();

        $feriados = DB::table('feriados')
            ->select('id', 'name', 'fecha', 'id_centromac')
            ->whereBetween('fecha', [
                $inicioMes->format('Y-m-d'),
                $finMes->format('Y-m-d')
            ])
            ->get();
        $feriadosGenerales = [];
        $feriadosPorMac = [];

        foreach ($feriados as $feriado) {
            if (!empty($feriado->fecha)) {
                $fechas = [
                    Carbon::parse($feriado->fecha)->format('Y-m-d')
                ];
            } elseif (!empty($feriado->fecha_inicio) && !empty($feriado->fecha_fin)) {
                $inicioFeriado = Carbon::parse($feriado->fecha_inicio);
                $finFeriado = Carbon::parse($feriado->fecha_fin);

                if ($inicioFeriado->lt($inicioMes)) {
                    $inicioFeriado = $inicioMes->copy();
                }

                if ($finFeriado->gt($finMes)) {
                    $finFeriado = $finMes->copy();
                }

                $fechas = [];

                while ($inicioFeriado->lte($finFeriado)) {
                    $fechas[] = $inicioFeriado->format('Y-m-d');
                    $inicioFeriado->addDay();
                }
            } else {
                continue;
            }

            foreach ($fechas as $fechaFeriado) {
                if (is_null($feriado->id_centromac)) {
                    $feriadosGenerales[$fechaFeriado] = $feriado->name;
                } else {
                    $feriadosPorMac[(int) $feriado->id_centromac][$fechaFeriado] = $feriado->name;
                }
            }
        }

        $matriz = [];

        foreach ($macs as $mac) {
            foreach ($dias as $dia) {
                $fechaDia = $inicioMes->copy()->day($dia);
                $fechaFormato = $fechaDia->format('Y-m-d');

                $esDomingo = $fechaDia->isSunday();

                $feriadoGeneral = $feriadosGenerales[$fechaFormato] ?? null;
                $feriadoMac = $feriadosPorMac[$mac->idmac][$fechaFormato] ?? null;

                $nombreFeriado = $feriadoMac ?? $feriadoGeneral;

                $esFeriado = !is_null($nombreFeriado);
                $esFuturo = $fechaDia->gt($hoy);

                $evaluar = !$esDomingo && !$esFeriado && !$esFuturo;

                $matriz[$mac->idmac][$dia] = [
                    'apertura' => false,
                    'relevo' => false,
                    'cierre' => false,
                    'evaluar' => $evaluar,
                    'es_domingo' => $esDomingo,
                    'es_feriado' => $esFeriado,
                    'es_futuro' => $esFuturo,
                    'feriado_nombre' => $nombreFeriado,
                ];
            }
        }

        foreach ($registros as $registro) {
            $idmac = (int) $registro->id_centromac;
            $dia = (int) Carbon::parse($registro->Fecha)->format('d');

            if (!isset($matriz[$idmac][$dia])) {
                continue;
            }

            if ((int) $registro->AperturaCierre === 0) {
                $matriz[$idmac][$dia]['apertura'] = true;
            }

            if ((int) $registro->AperturaCierre === 1) {
                $matriz[$idmac][$dia]['relevo'] = true;
            }

            if ((int) $registro->AperturaCierre === 2) {
                $matriz[$idmac][$dia]['cierre'] = true;
            }
        }

        $totalMacs = $macs->count();

        $resumen = [
            'completos' => 0,
            'faltaApertura' => 0,
            'faltaCierre' => 0,
            'sinRegistro' => 0,
            'feriados' => 0,
        ];

        foreach ($macs as $mac) {
            foreach ($dias as $dia) {
                $celda = $matriz[$mac->idmac][$dia];

                if ($celda['es_feriado']) {
                    $resumen['feriados']++;
                    continue;
                }

                if (!$celda['evaluar']) {
                    continue;
                }

                $apertura = $celda['apertura'];
                $cierre = $celda['cierre'];

                if ($apertura && $cierre) {
                    $resumen['completos']++;
                } elseif (!$apertura && $cierre) {
                    $resumen['faltaApertura']++;
                } elseif ($apertura && !$cierre) {
                    $resumen['faltaCierre']++;
                } else {
                    $resumen['sinRegistro']++;
                }
            }
        }

        return view('verificaciones.monitoreo', compact(
            'mes',
            'inicioMes',
            'finMes',
            'dias',
            'macs',
            'matriz',
            'totalMacs',
            'resumen'
        ));
    }
}
