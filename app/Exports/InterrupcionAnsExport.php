<?php
namespace App\Exports;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class InterrupcionAnsExport
{
    private $interrupciones;
    private $nombreMac;
    private $fechaInicio;
    private $fechaFin;
    public function __construct($interrupciones, $nombreMac, $fechaInicio = null, $fechaFin = null)
    {
        $this->interrupciones = collect($interrupciones);
        $this->nombreMac = $nombreMac;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }
    public function download()
    {
        $plantilla = storage_path('app/templates/PJ INTERRUPCIONES 2025.xlsx');
        if (!is_file($plantilla)) {
            abort(500, 'No se encontró la plantilla Excel de interrupciones.');
        }
        $libro = IOFactory::load($plantilla);
        $hoja = $libro->getSheetByName('RESUMEN') ?: $libro->getActiveSheet();
        $hoja->setAutoFilter('');
        $inicioDetalle = 17;
        $cantidad = $this->interrupciones->count();
        $ultimaFilaPlantilla = $hoja->getHighestRow();
        $ultimaFilaDatos = max($inicioDetalle, $inicioDetalle + $cantidad - 1);
        $ultimaFilaLimpiar = max($ultimaFilaPlantilla, $ultimaFilaDatos);
        foreach ($hoja->getMergeCells() as $rango) {
            if (preg_match('/^[A-Z]+(\d+):[A-Z]+\d+$/', $rango, $coincidencia) && (int) $coincidencia[1] >= $inicioDetalle) {
                $hoja->unmergeCells($rango);
            }
        }
        for ($filaLimpiar = $inicioDetalle; $filaLimpiar <= $ultimaFilaLimpiar; $filaLimpiar++) {
            for ($col = 1; $col <= 18; $col++) {
                $hoja->setCellValue(Coordinate::stringFromColumnIndex($col) . $filaLimpiar, null);
            }
            $hoja->getRowDimension($filaLimpiar)->setVisible(true);
            $hoja->getRowDimension($filaLimpiar)->setCollapsed(false);
            $hoja->getRowDimension($filaLimpiar)->setOutlineLevel(0);
        }
        $hoja->setShowGridLines(false);
        $hoja->setAutoFilter('');
        $horarios = DB::table('m_horario_mac')->where('activo', 1)->get()->groupBy('idcentro_mac');
        $feriados = DB::table('feriados')->get();
        $evaluadas = 0;
        $cumplen = 0;
        $superan = 0;
        $centros = [];
        $entidades = [];
        foreach ($this->interrupciones as $indice => $item) {
            $fila = $inicioDetalle + $indice;
            $centro = strtoupper((string) ($item->centroMac->nombre_mac ?? 'NO ASIGNADO'));
            $entidad = strtoupper((string) ($item->entidad->nombre_entidad ?? $item->entidad->ABREV_ENTIDAD ?? ''));
            $tipo = trim((string) ($item->tipoIntObs->nom_tipo_int_obs ?? ''));
            if ($centro !== 'NO ASIGNADO') $centros[$centro] = true;
            if ($entidad !== '') $entidades[$entidad] = true;
            $minutos = $this->minutosHabiles($item, $horarios, $feriados);
            $clasificacion = 'SIN CIERRE';
            if ($minutos !== null) {
                $evaluadas++;
                if ($minutos > 180) {
                    $superan++;
                    $clasificacion = 'MAYOR A 3 HORAS';
                } else {
                    $cumplen++;
                    $clasificacion = 'MENOR O IGUAL A 3 HORAS';
                }
            }
            $hoja->mergeCells("F{$fila}:H{$fila}");
            $hoja->mergeCells("I{$fila}:K{$fila}");
            $datos = [
                'A' => $indice + 1,
                'B' => $centro,
                'C' => strtoupper((string) ($item->entidad->nombre_entidad ?? $item->entidad->ABREV_ENTIDAD ?? '')),
                'D' => strtoupper((string) ($item->servicio_involucrado ?? '')),
                'E' => strtoupper($tipo),
                'F' => strtoupper((string) ($item->descripcion ?? '')),
                'I' => strtoupper((string) ($item->descripcion_accion ?? '')),
                'L' => $this->fechaExcel($item->fecha_inicio ?? null),
                'M' => $this->horaExcel($item->hora_inicio ?? null),
                'N' => $this->fechaExcel($item->fecha_fin ?? null),
                'O' => $this->horaExcel($item->hora_fin ?? null),
                'P' => strtoupper((string) ($item->estado ?? '')),
                'Q' => $minutos === null ? null : $minutos / 1440,
                'R' => $clasificacion,
            ];
            foreach ($datos as $columna => $valor) $hoja->setCellValue($columna . $fila, $valor);
            $hoja->getStyle("L{$fila}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $hoja->getStyle("N{$fila}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $hoja->getStyle("M{$fila}")->getNumberFormat()->setFormatCode('hh:mm:ss');
            $hoja->getStyle("O{$fila}")->getNumberFormat()->setFormatCode('hh:mm:ss');
            $hoja->getStyle("Q{$fila}")->getNumberFormat()->setFormatCode('[h]:mm:ss');
            $hoja->getStyle("A{$fila}:R{$fila}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("D{$fila}:K{$fila}")->getAlignment()->setWrapText(true);
            $hoja->getStyle("R{$fila}")->getAlignment()->setWrapText(true);
            $longitudDescripcion = mb_strlen((string) ($item->descripcion ?? ''));
            $longitudAccion = mb_strlen((string) ($item->descripcion_accion ?? ''));
            $longitudServicio = mb_strlen((string) ($item->servicio_involucrado ?? ''));
            $longitudTipificacion = mb_strlen((string) $tipo);
            $lineasDescripcion = (int) ceil($longitudDescripcion / 48);
            $lineasAccion = (int) ceil($longitudAccion / 48);
            $lineasServicio = (int) ceil($longitudServicio / 22);
            $lineasTipificacion = (int) ceil($longitudTipificacion / 25);
            $lineas = max(2, $lineasDescripcion, $lineasAccion, $lineasServicio, $lineasTipificacion);
            $hoja->getRowDimension($fila)->setRowHeight(max(36, $lineas * 17 + 12));
            $hoja->getRowDimension($fila)->setVisible(true);
            $hoja->getRowDimension($fila)->setCollapsed(false);
            $hoja->getStyle("A{$fila}:R{$fila}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("B{$fila}:E{$fila}")->getAlignment()->setWrapText(true);
            $hoja->getStyle("F{$fila}:K{$fila}")->getAlignment()->setWrapText(true);
            $hoja->getStyle("R{$fila}")->getAlignment()->setWrapText(true);
            $hoja->getStyle("A{$fila}:C{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("L{$fila}:R{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("D{$fila}:K{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $hoja->getStyle("A{$fila}:R{$fila}")->getFont()->setSize(10);
            $hoja->getStyle("A{$fila}:R{$fila}")->getFont()->getColor()->setRGB('172B4D');
            $hoja->getStyle("A{$fila}:R{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($indice % 2 === 0 ? 'FFFFFF' : 'F0F5FA');
            $hoja->getStyle("A{$fila}:R{$fila}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($minutos === null) {
                $hoja->getStyle("R{$fila}")->getFont()->getColor()->setRGB('A16207');
            } elseif ($minutos > 180) {
                $hoja->getStyle("R{$fila}")->getFont()->getColor()->setRGB('B91C1C');
            } else {
                $hoja->getStyle("R{$fila}")->getFont()->getColor()->setRGB('166534');
            }
        }
        if ($cantidad === 0) {
            foreach (range('A', 'R') as $columna) $hoja->setCellValue($columna . '17', null);
            $hoja->mergeCells('A17:R17');
            $hoja->setCellValue('A17', 'No se encontraron interrupciones para los filtros seleccionados.');
        }
        for ($filaExtra = $ultimaFilaDatos + 1; $filaExtra <= $ultimaFilaPlantilla; $filaExtra++) {
            $hoja->getRowDimension($filaExtra)->setRowHeight(-1);
            $hoja->getStyle("A{$filaExtra}:R{$filaExtra}")->getFill()->setFillType(Fill::FILL_NONE);
        }
        $rangoReal = $this->rangoFechas();
        $nombreEntidad = count($entidades) === 1 ? array_key_first($entidades) : (count($entidades) ? 'VARIAS ENTIDADES' : 'SIN REGISTROS');
        $centroEtiqueta = count($centros) === 1 ? array_key_first($centros) : ($this->nombreMac === 'TODOS LOS CENTROS MAC' ? 'Todos' : $this->nombreMac);
        $hoja->setCellValue('A6', 'REPORTE DE ANS DE INTERRUPCIONES');
        $hoja->setCellValue('A7', 'Entidad: ' . $nombreEntidad . '   |   Periodo: ' . $rangoReal);
        $hoja->setCellValue('B10', $cantidad);
        $hoja->setCellValue('E10', $cantidad > 0 ? $cumplen / $cantidad : 0);
        $hoja->getStyle('E10')->getNumberFormat()->setFormatCode('0.00%');
        $hoja->setCellValue('H10', $superan);
        $hoja->setCellValue('K10', count($centros));
        $hoja->setCellValue('B12', 'Registros de interrupciones');
        $hoja->setCellValue('E12', $cumplen . ' de ' . $cantidad . ' interrupciones dentro del ANS');
        $hoja->setCellValue('H12', 'Interrupciones que superaron las 3 horas');
        $hoja->setCellValue('K12', 'Centros evaluados');
        $hoja->setCellValue('A14', 'Entidad: ' . $nombreEntidad . "\nPeriodo: " . $rangoReal . "\nCentro MAC: " . $centroEtiqueta);
        $hoja->getStyle('A14')->getAlignment()->setWrapText(true);
        $hoja->getPageSetup()->setPrintArea('A1:R' . $ultimaFilaDatos);
        $base = 'Interrupciones_' . preg_replace('/[^\pL\pN._-]+/u', '_', $this->nombreMac) . '_' . date('Ymd_His') . '.xlsx';
        $archivo = storage_path('app/' . $base);
        (new Xlsx($libro))->save($archivo);
        $zip = new \ZipArchive();
        if ($zip->open($archivo) !== true) {
            throw new \RuntimeException('No se pudo verificar el archivo Excel generado.');
        }
        $hojaXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($hojaXml === false) {
            $zip->close();
            throw new \RuntimeException('No se encontró la hoja principal del Excel.');
        }
        $vistaValida = '<sheetViews><sheetView workbookViewId="0" showGridLines="0"><selection activeCell="A1" sqref="A1"/></sheetView></sheetViews>';
        $hojaXmlNormalizada = preg_replace('~<sheetViews\b[^>]*>.*?</sheetViews>~s', $vistaValida, $hojaXml, 1, $reemplazos);
        if ($reemplazos !== 1 || !$zip->addFromString('xl/worksheets/sheet1.xml', $hojaXmlNormalizada)) {
            $zip->close();
            throw new \RuntimeException('No se pudo normalizar la vista del reporte Excel.');
        }
        if (!$zip->close()) {
            throw new \RuntimeException('No se pudo finalizar el archivo Excel.');
        }
        $libro->disconnectWorksheets();
        return response()->download($archivo, $base)->deleteFileAfterSend(true);
    }
    private function rangoFechas(): string
    {
        $desde = $this->fechaInicio;
        $hasta = $this->fechaFin;
        if (!$desde) $desde = $this->interrupciones->min('fecha_inicio');
        if (!$hasta) $hasta = $this->interrupciones->max('fecha_inicio');
        if (!$desde || !$hasta) return 'Sin periodo';
        return Carbon::parse($desde)->format('d/m/Y') . ' al ' . Carbon::parse($hasta)->format('d/m/Y');
    }
    private function fechaExcel($valor)
    {
        return $valor ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(Carbon::parse($valor)->toDateTime()) : null;
    }
    private function horaExcel($valor)
    {
        if (!$valor) return null;
        $partes = explode(':', Carbon::parse($valor)->format('H:i:s'));
        return ((int) $partes[0] * 3600 + (int) $partes[1] * 60 + (int) $partes[2]) / 86400;
    }
    private function minutosHabiles($item, $horarios, $feriados): ?int
    {
        if (!$item->fecha_inicio || !$item->hora_inicio || !$item->fecha_fin || !$item->hora_fin) return null;
        $inicio = Carbon::parse($item->fecha_inicio . ' ' . $item->hora_inicio);
        $fin = Carbon::parse($item->fecha_fin . ' ' . $item->hora_fin);
        if ($fin->lte($inicio)) return null;
        $mac = $item->idcentro_mac;
        $horarioMac = $horarios->get($mac, collect());
        $diasFeriados = $feriados->filter(function ($f) use ($mac) {
            return !isset($f->id_centromac) || $f->id_centromac === null || (string) $f->id_centromac === (string) $mac;
        })->pluck('fecha')->map(fn ($f) => Carbon::parse($f)->toDateString())->all();
        $total = 0;
        for ($dia = $inicio->copy()->startOfDay(); $dia->lte($fin); $dia->addDay()) {
            if ($dia->dayOfWeekIso === 7 || in_array($dia->toDateString(), $diasFeriados, true)) continue;
            $horario = $horarioMac->firstWhere('dia_semana', $dia->dayOfWeekIso);
            if (!$horario) continue;
            $horaDesde = Carbon::parse($dia->toDateString() . ' ' . $horario->hora_inicio);
            $horaHasta = Carbon::parse($dia->toDateString() . ' ' . $horario->hora_fin);
            $desde = $horaDesde->greaterThan($inicio) ? $horaDesde : $inicio;
            $hasta = $horaHasta->lessThan($fin) ? $horaHasta : $fin;
            if ($hasta->greaterThan($desde)) $total += (int) floor($desde->diffInSeconds($hasta) / 60);
        }
        return $total;
    }
}
