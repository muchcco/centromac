<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class MiAsistenciaController extends Controller
{
    private const ESTADO_PENDIENTE = 'PENDIENTE';
    private const ESTADO_APROBADO = 'APROBADO';
    private const ESTADO_RECHAZADO = 'RECHAZADO';
    private const MOTIVO_COMPENSACION = 'COMPENSACION DE HORAS / DIA';

    private function tienePermisoRevision(): bool
    {
        return auth()->user()->hasRole(['Administrador', 'Moderador']);
    }

    private function tablaConsumoDisponible(): bool
    {
        return Schema::hasTable('d_personal_asistencia_consumo');
    }

    private function tablaDetalleConsumoDisponible(): bool
    {
        return Schema::hasTable('d_personal_asistencia_consumo_det');
    }

    private function consumoTieneRangoFechas(): bool
    {
        return Schema::hasTable('d_personal_asistencia_consumo')
            && Schema::hasColumn('d_personal_asistencia_consumo', 'fecha_inicio_consumo')
            && Schema::hasColumn('d_personal_asistencia_consumo', 'fecha_fin_consumo');
    }

    private function motivosConsumo(): array
    {
        return [
            self::MOTIVO_COMPENSACION,
            'VACACIONES',
            'DESCANSO MEDICO',
            'PERMISO PERSONAL',
            'COMISION DE SERVICIO',
            'OTRO',
        ];
    }

    private function esMotivoCompensacion(?string $motivo): bool
    {
        return $motivo === self::MOTIVO_COMPENSACION;
    }

    private function idPersonalUsuario(): ?int
    {
        $user = auth()->user();

        return $user->idpersonal
            ?? $user->id_personal
            ?? $user->id_persona
            ?? null;
    }

    private function idMac(Request $request): int
    {
        $user = auth()->user();

        if ($this->tienePermisoRevision()) {
            return (int) ($request->input('mac') ?: $user->idcentro_mac);
        }

        return (int) $user->idcentro_mac;
    }

    private function macs()
    {
        $user = auth()->user();

        if ($this->tienePermisoRevision()) {
            return DB::table('m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->orderBy('NOMBRE_MAC')
                ->get();
        }

        return DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
            ->where('IDCENTRO_MAC', $user->idcentro_mac)
            ->get();
    }

    private function fechas(Request $request): array
    {
        $inicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $fin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        return [$inicio, $fin];
    }

    private function formatoMinutos(int $minutos): string
    {
        $minutos = max(0, $minutos);

        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }

    private function minutosJornadaCompleta(string $fecha, string $horaInicio, string $horaFin): int
    {
        $minutos = Carbon::parse($fecha . ' ' . $horaInicio)
            ->diffInMinutes(Carbon::parse($fecha . ' ' . $horaFin));

        return max(0, $minutos - 60);
    }

    private function periodoConsumo(Request $request): array
    {
        $fechaInicio = $request->input('fecha_inicio_consumo') ?: $request->input('fecha_consumo');
        $fechaFin = $request->input('fecha_fin_consumo') ?: $fechaInicio;

        $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
        $fechaFin = Carbon::parse($fechaFin)->format('Y-m-d');

        if ($fechaInicio > $fechaFin) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        return [$fechaInicio, $fechaFin];
    }

    private function calcularConsumoSolicitado(int $idmac, int $idpersonal, string $fechaInicio, string $fechaFin, Request $request): array
    {
        $tipo = $request->input('tipo_consumo');
        $minutosSolicitados = 0;
        $diasValidos = 0;
        $fechasNoProgramadas = [];
        $fechasFeriadas = [];
        $horaInicioRegistro = null;
        $horaFinRegistro = null;

        if ($tipo === 'HORAS' && $request->input('hora_inicio') >= $request->input('hora_fin')) {
            return [
                'success' => false,
                'message' => 'La hora fin debe ser mayor a la hora inicio.',
            ];
        }

        foreach (CarbonPeriod::create($fechaInicio, $fechaFin) as $dia) {
            $fecha = $dia->format('Y-m-d');

            if ($this->feriadoExiste($idmac, $fecha)) {
                $fechasFeriadas[] = $dia->format('d-m-Y');
                continue;
            }

            $horario = $this->horarioProgramadoParaFecha($idpersonal, $fecha);

            if (!$horario) {
                $fechasNoProgramadas[] = $dia->format('d-m-Y');
                continue;
            }

            if ($tipo === 'DIA') {
                $minutosSolicitados += $this->minutosJornadaCompleta($fecha, $horario->hora_ingreso, $horario->hora_salida);
                $horaInicioRegistro ??= substr($horario->hora_ingreso, 0, 5);
                $horaFinRegistro ??= substr($horario->hora_salida, 0, 5);
            } else {
                $horaInicioRegistro = $request->input('hora_inicio');
                $horaFinRegistro = $request->input('hora_fin');
                $minutosSolicitados += Carbon::parse($fecha . ' ' . $horaInicioRegistro)
                    ->diffInMinutes(Carbon::parse($fecha . ' ' . $horaFinRegistro));
            }

            $diasValidos++;
        }

        if ($diasValidos === 0) {
            return [
                'success' => false,
                'message' => 'El rango seleccionado no tiene dias programados para registrar la solicitud.',
            ];
        }

        return [
            'success' => true,
            'minutos' => $minutosSolicitados,
            'hora_inicio' => $horaInicioRegistro,
            'hora_fin' => $horaFinRegistro,
            'dias' => $diasValidos,
            'omitidos' => array_merge($fechasFeriadas, $fechasNoProgramadas),
        ];
    }

    private function persona(int $idpersonal)
    {
        return DB::table('m_personal as p')
            ->leftJoin('m_entidad as e', 'e.IDENTIDAD', '=', 'p.IDENTIDAD')
            ->leftJoin('m_centro_mac as cm', 'cm.IDCENTRO_MAC', '=', 'p.IDMAC')
            ->select(
                'p.IDPERSONAL',
                'p.NUM_DOC',
                'p.IDMAC',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(e.ABREV_ENTIDAD, e.NOMBRE_ENTIDAD, "-") as entidad'),
                DB::raw('COALESCE(cm.NOMBRE_MAC, "-") as nombre_mac')
            )
            ->where('p.IDPERSONAL', $idpersonal)
            ->first();
    }

    private function feriadoExiste(int $idmac, string $fecha): bool
    {
        if (!Schema::hasTable('feriados')) {
            return false;
        }

        return DB::table('feriados')
            ->whereDate('fecha', $fecha)
            ->where(function ($q) use ($idmac) {
                $q->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->exists();
    }

    private function reporteHorasCompensables(int $idmac, string $fechaInicio, string $fechaFin, int $idpersonal)
    {
        $usaDiasEspeciales = Schema::hasTable('d_personal_asistencia_dia');
        $ingresoProgramado = $usaDiasEspeciales
            ? 'COALESCE(dpd.hora_ingreso, dpa.hora_ingreso)'
            : 'dpa.hora_ingreso';
        $salidaProgramada = $usaDiasEspeciales
            ? 'COALESCE(dpd.hora_salida, dpa.hora_salida)'
            : 'dpa.hora_salida';

        $query = DB::table('d_personal_asistencia as dpa')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
            ->join('m_asistencia as a', function ($join) {
                $join->on('a.NUM_DOC', '=', 'p.NUM_DOC')
                    ->whereColumn('a.FECHA', '>=', 'dpa.fecha_inicio')
                    ->whereRaw('(dpa.sin_fin = 1 OR dpa.fecha_fin IS NULL OR a.FECHA <= dpa.fecha_fin)');
            })
            ->where('a.IDCENTRO_MAC', $idmac)
            ->where('p.IDPERSONAL', $idpersonal)
            ->whereBetween('a.FECHA', [$fechaInicio, $fechaFin])
            ->whereRaw('DAYOFWEEK(a.FECHA) <> 1');

        if (Schema::hasTable('feriados')) {
            $query->whereNotExists(function ($sq) use ($idmac) {
                $sq->select(DB::raw(1))
                    ->from('feriados as fer')
                    ->whereColumn('fer.fecha', 'a.FECHA')
                    ->where(function ($q) use ($idmac) {
                        $q->where('fer.id_centromac', $idmac)
                            ->orWhereNull('fer.id_centromac');
                    });
            });
        }

        if ($usaDiasEspeciales) {
            $query->leftJoin('d_personal_asistencia_dia as dpd', function ($join) {
                $join->on('dpd.id_asignacion', '=', 'dpa.id')
                    ->whereColumn('dpd.fecha', 'a.FECHA')
                    ->where('dpd.activo', 1);
            })->whereRaw('(DAYOFWEEK(a.FECHA) BETWEEN 2 AND 6 OR dpd.id IS NOT NULL)');
        } else {
            $query->whereRaw('DAYOFWEEK(a.FECHA) BETWEEN 2 AND 6');
        }

        $rows = $query
            ->select(
                'dpa.id as id_asignacion',
                'dpa.idpersonal',
                'p.NUM_DOC',
                'a.FECHA',
                DB::raw($ingresoProgramado . ' as ingreso_programado'),
                DB::raw($salidaProgramada . ' as salida_programada'),
                DB::raw('MIN(a.HORA) as asistencia_ingreso'),
                DB::raw('CASE WHEN COUNT(a.IDASISTENCIA) > 1 THEN MAX(a.HORA) ELSE NULL END as asistencia_salida'),
                DB::raw('COUNT(a.IDASISTENCIA) as total_marcaciones'),
                DB::raw('CASE WHEN COUNT(a.IDASISTENCIA) > 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, CONCAT(a.FECHA, " ", ' . $salidaProgramada . '), CONCAT(a.FECHA, " ", MAX(a.HORA))), 0) ELSE 0 END as minutos_extra')
            )
            ->groupBy(
                'dpa.id',
                'dpa.idpersonal',
                'dpa.hora_ingreso',
                'dpa.hora_salida',
                'p.NUM_DOC',
                'a.FECHA'
            );

        if ($usaDiasEspeciales) {
            $rows->groupBy('dpd.id', 'dpd.hora_ingreso', 'dpd.hora_salida');
        }

        return $rows
            ->orderBy('a.FECHA', 'desc')
            ->get()
            ->map(function ($row) {
                $row->minutos_extra = (int) $row->minutos_extra;
                $row->horas_extra = $this->formatoMinutos($row->minutos_extra);

                return $row;
            });
    }

    private function minutosGenerados(int $idmac, int $idpersonal): int
    {
        $inicio = DB::table('d_personal_asistencia')
            ->where('idpersonal', $idpersonal)
            ->min('fecha_inicio');

        if (!$inicio) {
            return 0;
        }

        return (int) $this->reporteHorasCompensables($idmac, $inicio, Carbon::now()->format('Y-m-d'), $idpersonal)
            ->sum('minutos_extra');
    }

    private function solicitudesUsuario(int $idpersonal)
    {
        if (!$this->tablaConsumoDisponible()) {
            return collect();
        }

        return DB::table('d_personal_asistencia_consumo as c')
            ->leftJoin('users as val', 'val.id', '=', 'c.idusuario_valida')
            ->select('c.*', DB::raw('COALESCE(val.name, "-") as validador'))
            ->where('c.idpersonal', $idpersonal)
            ->orderBy('c.created_at', 'desc')
            ->get();
    }

    private function solicitudesRevision(int $idmac)
    {
        if (!$this->tablaConsumoDisponible() || !$this->tienePermisoRevision()) {
            return collect();
        }

        return DB::table('d_personal_asistencia_consumo as c')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'c.idpersonal')
            ->leftJoin('m_entidad as e', 'e.IDENTIDAD', '=', 'p.IDENTIDAD')
            ->select(
                'c.*',
                'p.NUM_DOC',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(e.ABREV_ENTIDAD, e.NOMBRE_ENTIDAD, "-") as entidad')
            )
            ->where('c.idcentro_mac', $idmac)
            ->orderByRaw("FIELD(c.estado, 'PENDIENTE', 'APROBADO', 'RECHAZADO')")
            ->orderBy('c.created_at', 'desc')
            ->get();
    }

    private function detallesSolicitud($ids)
    {
        if (!$this->tablaDetalleConsumoDisponible()) {
            return collect();
        }

        $ids = collect($ids)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('d_personal_asistencia_consumo_det')
            ->whereIn('id_consumo', $ids->all())
            ->orderBy('fecha_origen')
            ->get()
            ->groupBy('id_consumo');
    }

    private function minutosSolicitadosNoRechazados(int $idpersonal): int
    {
        if (!$this->tablaConsumoDisponible()) {
            return 0;
        }

        return (int) DB::table('d_personal_asistencia_consumo')
            ->where('idpersonal', $idpersonal)
            ->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_APROBADO])
            ->where('motivo', self::MOTIVO_COMPENSACION)
            ->sum('minutos_solicitados');
    }

    private function minutosConsumidosPorFecha(int $idpersonal)
    {
        if (!$this->tablaConsumoDisponible() || !$this->tablaDetalleConsumoDisponible()) {
            return collect();
        }

        return DB::table('d_personal_asistencia_consumo_det as det')
            ->join('d_personal_asistencia_consumo as c', 'c.id', '=', 'det.id_consumo')
            ->where('c.idpersonal', $idpersonal)
            ->whereIn('c.estado', [self::ESTADO_PENDIENTE, self::ESTADO_APROBADO])
            ->where('c.motivo', self::MOTIVO_COMPENSACION)
            ->select('det.fecha_origen', DB::raw('SUM(det.minutos_usados) as minutos_usados'))
            ->groupBy('det.fecha_origen')
            ->pluck('minutos_usados', 'fecha_origen');
    }

    private function fuentesCompensacion(int $idmac, int $idpersonal, bool $soloDisponibles)
    {
        $inicioAsignacion = DB::table('d_personal_asistencia')
            ->where('idpersonal', $idpersonal)
            ->min('fecha_inicio');

        if (!$inicioAsignacion) {
            return collect();
        }

        $inicioLimite = Carbon::now()->subMonthsNoOverflow(3)->startOfDay()->format('Y-m-d');
        $inicio = max(Carbon::parse($inicioAsignacion)->format('Y-m-d'), $inicioLimite);
        $consumidos = $this->minutosConsumidosPorFecha($idpersonal);

        $fuentes = $this->reporteHorasCompensables($idmac, $inicio, Carbon::now()->format('Y-m-d'), $idpersonal)
            ->filter(fn($row) => $row->minutos_extra > 0)
            ->sortBy('FECHA')
            ->map(function ($row) use ($consumidos) {
                $fecha = Carbon::parse($row->FECHA)->format('Y-m-d');
                $row->fecha_origen = $fecha;
                $row->minutos_consumidos = (int) ($consumidos[$fecha] ?? 0);
                $row->minutos_disponibles = max(0, $row->minutos_extra - $row->minutos_consumidos);
                $row->horas_disponibles = $this->formatoMinutos($row->minutos_disponibles);
                $row->horas_consumidas = $this->formatoMinutos($row->minutos_consumidos);

                return $row;
            })
            ->values();

        if ($soloDisponibles) {
            return $fuentes
                ->filter(fn($row) => $row->minutos_disponibles > 0)
                ->values();
        }

        return $fuentes;
    }

    private function fuentesDisponibles(int $idmac, int $idpersonal)
    {
        return $this->fuentesCompensacion($idmac, $idpersonal, true);
    }

    private function horarioProgramadoParaFecha(int $idpersonal, string $fecha)
    {
        $diaSemana = Carbon::parse($fecha)->dayOfWeek;
        $query = DB::table('d_personal_asistencia as dpa')
            ->where('dpa.idpersonal', $idpersonal)
            ->where('dpa.fecha_inicio', '<=', $fecha)
            ->whereRaw('(dpa.sin_fin = 1 OR dpa.fecha_fin IS NULL OR dpa.fecha_fin >= ?)', [$fecha])
            ->orderBy('dpa.fecha_inicio', 'desc');

        if (Schema::hasTable('d_personal_asistencia_dia')) {
            $query->leftJoin('d_personal_asistencia_dia as dpd', function ($join) use ($fecha) {
                $join->on('dpd.id_asignacion', '=', 'dpa.id')
                    ->where('dpd.fecha', $fecha)
                    ->where('dpd.activo', 1);
            })
                ->select(
                    'dpa.id',
                    DB::raw('COALESCE(dpd.hora_ingreso, dpa.hora_ingreso) as hora_ingreso'),
                    DB::raw('COALESCE(dpd.hora_salida, dpa.hora_salida) as hora_salida'),
                    'dpd.id as id_dia_especial'
                );
        } else {
            $query->select('dpa.id', 'dpa.hora_ingreso', 'dpa.hora_salida', DB::raw('NULL as id_dia_especial'));
        }

        $horario = $query->first();

        if (!$horario) {
            return null;
        }

        if ($diaSemana === Carbon::SUNDAY) {
            return null;
        }

        if ($diaSemana === Carbon::SATURDAY && !$horario->id_dia_especial) {
            return null;
        }

        return $horario;
    }

    private function guardarArchivo(Request $request): ?string
    {
        if (!$request->hasFile('evidencia')) {
            return null;
        }

        $archivo = $request->file('evidencia');
        $ruta = 'miasistencia_evidencias';

        if (!is_dir(public_path($ruta))) {
            mkdir(public_path($ruta), 0755, true);
        }

        $nombre = 'consumo_' . auth()->id() . '_' . now()->format('Ymd_His') . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path($ruta), $nombre);

        return $ruta . '/' . $nombre;
    }

    public function index(Request $request)
    {
        $tablaDisponible = $this->tablaConsumoDisponible();
        $tablaDetalleDisponible = $this->tablaDetalleConsumoDisponible();
        $rangoFechasDisponible = $this->consumoTieneRangoFechas();
        $idpersonal = $this->idPersonalUsuario();
        $idmac = $this->idMac($request);
        [$fechaInicio, $fechaFin] = $this->fechas($request);
        $macs = $this->macs();
        $personal = $idpersonal ? $this->persona($idpersonal) : null;
        $rowsCompensables = $idpersonal
            ? $this->reporteHorasCompensables($idmac, $fechaInicio, $fechaFin, $idpersonal)
            : collect();
        $fuentesDisponibles = $idpersonal ? $this->fuentesDisponibles($idmac, $idpersonal) : collect();
        $fuentesCompensacion = $idpersonal ? $this->fuentesCompensacion($idmac, $idpersonal, false) : collect();
        $minutosGeneradosRango = (int) $rowsCompensables->sum('minutos_extra');
        $minutosGeneradosTotal = $idpersonal ? $this->minutosGenerados($idmac, $idpersonal) : 0;
        $minutosComprometidos = $idpersonal ? $this->minutosSolicitadosNoRechazados($idpersonal) : 0;
        $solicitudes = $idpersonal ? $this->solicitudesUsuario($idpersonal) : collect();
        $solicitudesRevision = $this->solicitudesRevision($idmac);
        $detalleSolicitudes = $this->detallesSolicitud(
            $solicitudes->pluck('id')->merge($solicitudesRevision->pluck('id'))
        );
        $saldoMinutos = $tablaDetalleDisponible
            ? (int) $fuentesDisponibles->sum('minutos_disponibles')
            : max(0, $minutosGeneradosTotal - $minutosComprometidos);

        $summary = [
            'generadas_rango' => $this->formatoMinutos($minutosGeneradosRango),
            'generadas_total' => $this->formatoMinutos($minutosGeneradosTotal),
            'comprometidas' => $this->formatoMinutos($minutosComprometidos),
            'saldo' => $this->formatoMinutos($saldoMinutos),
            'saldo_minutos' => $saldoMinutos,
        ];
        $motivosConsumo = $this->motivosConsumo();

        return view('asistencia.miasistencia.index', compact(
            'tablaDisponible',
            'tablaDetalleDisponible',
            'rangoFechasDisponible',
            'idmac',
            'macs',
            'fechaInicio',
            'fechaFin',
            'personal',
            'rowsCompensables',
            'fuentesDisponibles',
            'fuentesCompensacion',
            'summary',
            'solicitudes',
            'solicitudesRevision',
            'detalleSolicitudes',
            'motivosConsumo'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->tablaConsumoDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Falta crear la tabla d_personal_asistencia_consumo.',
            ], 409);
        }

        $debeConsumirHoras = $this->esMotivoCompensacion($request->input('motivo'));

        if ($debeConsumirHoras && !$this->tablaDetalleConsumoDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Falta crear la tabla d_personal_asistencia_consumo_det.',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'tipo_consumo' => 'required|in:HORAS,DIA',
            'fecha_inicio_consumo' => 'required|date',
            'fecha_fin_consumo' => 'required|date',
            'hora_inicio' => 'nullable|required_if:tipo_consumo,HORAS|date_format:H:i',
            'hora_fin' => 'nullable|required_if:tipo_consumo,HORAS|date_format:H:i',
            'fechas_origen' => 'nullable|array',
            'fechas_origen.*' => 'date_format:Y-m-d',
            'motivo' => 'required|string|in:' . implode(',', $this->motivosConsumo()),
            'observacion' => 'nullable|string|max:1500',
            'evidencia' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise los campos de la solicitud.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $debeConsumirHoras = $this->esMotivoCompensacion($request->input('motivo'));

        if ($debeConsumirHoras && collect($request->input('fechas_origen', []))->filter()->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Seleccione al menos un dia origen para consumir horas compensables.',
            ], 422);
        }

        $idpersonal = $this->idPersonalUsuario();
        $personal = $idpersonal ? $this->persona($idpersonal) : null;

        if (!$personal) {
            return response()->json([
                'success' => false,
                'message' => 'Su usuario no esta vinculado a un registro de personal.',
            ], 422);
        }

        $idmac = (int) auth()->user()->idcentro_mac;
        [$fechaInicioConsumo, $fechaFinConsumo] = $this->periodoConsumo($request);
        $calculoConsumo = $this->calcularConsumoSolicitado($idmac, $idpersonal, $fechaInicioConsumo, $fechaFinConsumo, $request);

        if (!$calculoConsumo['success']) {
            return response()->json([
                'success' => false,
                'message' => $calculoConsumo['message'],
            ], 422);
        }

        $minutosSolicitados = (int) $calculoConsumo['minutos'];
        $horaInicio = $calculoConsumo['hora_inicio'];
        $horaFin = $calculoConsumo['hora_fin'];

        $fuentesSeleccionadas = collect();

        if ($debeConsumirHoras) {
            $fechasOrigen = collect($request->input('fechas_origen', []))->unique()->values();
            $fuentesSeleccionadas = $this->fuentesDisponibles($idmac, $idpersonal)
                ->whereIn('fecha_origen', $fechasOrigen->all())
                ->values();
            $saldoSeleccionado = (int) $fuentesSeleccionadas->sum('minutos_disponibles');

            if ($minutosSolicitados > $saldoSeleccionado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas origen seleccionadas suman ' . $this->formatoMinutos($saldoSeleccionado) . ', pero la solicitud requiere ' . $this->formatoMinutos($minutosSolicitados) . '.',
                    'minutos_requeridos' => $minutosSolicitados,
                    'minutos_seleccionados' => $saldoSeleccionado,
                ], 422);
            }
        }

        $archivo = $this->guardarArchivo($request);

        DB::transaction(function () use ($idpersonal, $idmac, $fechaInicioConsumo, $fechaFinConsumo, $horaInicio, $horaFin, $minutosSolicitados, $request, $archivo, $fuentesSeleccionadas, $debeConsumirHoras) {
            $dataConsumo = [
                'idpersonal' => $idpersonal,
                'idcentro_mac' => $idmac,
                'fecha_solicitud' => Carbon::now()->format('Y-m-d'),
                'tipo_consumo' => $request->input('tipo_consumo'),
                'fecha_consumo' => $fechaInicioConsumo,
                'hora_inicio' => $horaInicio . ':00',
                'hora_fin' => $horaFin . ':00',
                'minutos_solicitados' => $minutosSolicitados,
                'motivo' => $request->input('motivo'),
                'archivo_evidencia' => $archivo,
                'estado' => self::ESTADO_PENDIENTE,
                'idusuario_solicita' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($this->consumoTieneRangoFechas()) {
                $dataConsumo['fecha_inicio_consumo'] = $fechaInicioConsumo;
                $dataConsumo['fecha_fin_consumo'] = $fechaFinConsumo;
            }

            if (Schema::hasColumn('d_personal_asistencia_consumo', 'observacion')) {
                $dataConsumo['observacion'] = $request->input('observacion');
            }

            $idConsumo = DB::table('d_personal_asistencia_consumo')->insertGetId($dataConsumo);

            if (!$debeConsumirHoras) {
                return;
            }

            $pendiente = $minutosSolicitados;
            foreach ($fuentesSeleccionadas as $fuente) {
                if ($pendiente <= 0) {
                    break;
                }

                $minutosUsados = min($pendiente, (int) $fuente->minutos_disponibles);

                DB::table('d_personal_asistencia_consumo_det')->insert([
                    'id_consumo' => $idConsumo,
                    'fecha_origen' => $fuente->fecha_origen,
                    'minutos_generados' => (int) $fuente->minutos_extra,
                    'minutos_usados' => $minutosUsados,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $pendiente -= $minutosUsados;
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Solicitud registrada correctamente.',
        ]);
    }

    public function validar(Request $request)
    {
        if (!$this->tablaConsumoDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Falta crear la tabla d_personal_asistencia_consumo.',
            ], 409);
        }

        if (!$this->tienePermisoRevision()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para validar solicitudes.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'estado' => 'required|in:APROBADO,RECHAZADO',
            'observacion_validador' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise la validacion solicitada.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $idmac = $this->idMac($request);
        $solicitud = DB::table('d_personal_asistencia_consumo')
            ->where('id', $request->input('id'))
            ->first();

        if (!$solicitud || (int) $solicitud->idcentro_mac !== $idmac) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la solicitud en el Centro MAC seleccionado.',
            ], 404);
        }

        DB::table('d_personal_asistencia_consumo')
            ->where('id', $solicitud->id)
            ->update([
                'estado' => $request->input('estado'),
                'observacion_validador' => $request->input('observacion_validador'),
                'idusuario_valida' => auth()->id(),
                'fecha_validacion' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente.',
        ]);
    }
}
