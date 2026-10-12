<?php
namespace App\Exports;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ReporteOcupabilidadPuntualidadExport
{
    private array $detalle;
    private array $indicadores;
    private string $inicio;
    private string $fin;
    public function __construct(array $detalle, array $indicadores, string $inicio, string $fin)
    {
        $this->detalle = $detalle;
        $this->indicadores = $indicadores;
        $this->inicio = $inicio;
        $this->fin = $fin;
    }
    public function download()
    {
        $dir = storage_path('app/tmp');
        File::ensureDirectoryExists($dir);
        $nombre = 'ANS_Ocupabilidad_Puntualidad_' . $this->inicio . '_' . $this->fin . '.xlsx';
        $ruta = $dir . '/' . uniqid('op_', true) . '.xlsx';
        $this->saveToPath($ruta);
        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }
    public function saveToPath(string $ruta): void
    {
        $plantilla = storage_path('app/templates/PJ OCUPABILIDAD 2025_LIVIANA.xlsx');
        if (!is_file($plantilla)) throw new \RuntimeException('No existe la plantilla liviana del reporte ANS.');
        $libro = IOFactory::createReader('Xlsx')->load($plantilla);
        try {
            $hoja = $libro->getSheetByName('RESUMEN') ?: $libro->getActiveSheet();
            $hoja->setShowGridlines(false);
            $hoja->getSheetView()->setZoomScale(85);
            $entidades = [];
            $centros = [];
            foreach ($this->detalle as $fila) {
                $e = mb_strtoupper(trim((string)($fila['entidad'] ?? '')));
                $c = mb_strtoupper(trim((string)($fila['centro_mac'] ?? '')));
                if ($e !== '') $entidades[$e] = true;
                if ($c !== '') $centros[$c] = true;
            }
            $entidad = count($entidades) === 1 ? array_key_first($entidades) : (count($entidades) ? 'VARIAS ENTIDADES' : 'SIN REGISTROS');
            $centro = count($centros) === 1 ? array_key_first($centros) : (count($centros) ? 'VARIOS CENTROS MAC' : 'SIN REGISTROS');
            $periodo = Carbon::parse($this->inicio)->format('d/m/Y') . ' al ' . Carbon::parse($this->fin)->format('d/m/Y');
            $habiles = (int)($this->indicadores['dias_habiles'] ?? 0);
            $asistencias = (int)($this->indicadores['asistencias'] ?? 0);
            $puntuales = (int)($this->indicadores['puntuales'] ?? 0);
            $nCentros = (int)($this->indicadores['centros'] ?? count($centros));
            $hoja->setCellValue('A6', 'REPORTE DE ANS DE OCUPABILIDAD Y PUNTUALIDAD');
            $hoja->setCellValue('A7', 'Entidad: ' . $entidad . '   |   Periodo: ' . $periodo);
            $hoja->setCellValue('B10', $habiles);
            $hoja->setCellValue('E10', $habiles ? $asistencias / $habiles : 0);
            $hoja->setCellValue('H10', $asistencias ? $puntuales / $asistencias : 0);
            $hoja->setCellValue('K10', $nCentros);
            $hoja->getStyle('E10')->getNumberFormat()->setFormatCode('0.00%');
            $hoja->getStyle('H10')->getNumberFormat()->setFormatCode('0.00%');
            $hoja->setCellValue('B12', 'Jornadas hábiles evaluadas');
            $hoja->setCellValue('E12', $asistencias . ' de ' . $habiles . ' días hábiles');
            $hoja->setCellValue('H12', $puntuales . ' de ' . $asistencias . ' días marcados');
            $hoja->setCellValue('K12', 'Centros evaluados');
            $hoja->setCellValue('A14', 'Entidad: ' . $entidad . "\n" . 'Periodo: ' . $periodo . "\n" . 'Centro MAC: ' . $centro);
            $hoja->getStyle('A14')->getAlignment()->setWrapText(true);
            $hoja->setCellValue('A15', '▥  Detalle de Asistencia');
            $campos = ['N°','Centro MAC','Entidad','DNI','DATOS DEL COLABORADOR (APELLIDOS Y NOMBRES)','','','Cargo / Asesor(a)','Número módulo','Fecha','Día','Hora de Ingreso','Hora de Salida','Estado','Puntual','Feriado','Tipo Feriado','Motivo Feriado'];
            $hoja->fromArray($campos, null, 'A16');
            $datos = [];
            foreach ($this->detalle as $indice => $fila) {
                $datos[] = [
                    $indice + 1, $fila['centro_mac'] ?? '', $fila['entidad'] ?? '',
                    (string)($fila['dni'] ?? ''), (string)($fila['colaborador'] ?? ''), '', '',
                    $fila['cargo'] ?? '', $fila['modulo'] ?? '',
                    !empty($fila['fecha']) ? Date::PHPToExcel(Carbon::parse($fila['fecha'])->toDateTime()) : null,
                    $fila['dia'] ?? '', $fila['ingreso'] ?? '', $fila['salida'] ?? '',
                    $fila['estado'] ?? '', $fila['puntual'] ?? '', $fila['feriado'] ?? '',
                    $fila['tipo_feriado'] ?? '', $fila['motivo_feriado'] ?? ''
                ];
            }
            $ultima = max(17, 16 + count($datos));
            if ($datos) {
                $hoja->fromArray($datos, null, 'A17', true);
                $hoja->getStyle('A17:R' . $ultima)->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '172B4D']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B7C9D6']]]
                ]);
                $hoja->getStyle('J17:J' . $ultima)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $hoja->getStyle('D17:D' . $ultima)->getNumberFormat()->setFormatCode('@');
                $hoja->getStyle('E17:G' . $ultima)->getAlignment()->setWrapText(true);
                $hoja->getStyle('R17:R' . $ultima)->getAlignment()->setWrapText(true);
                $hoja->getStyle('A17:D' . $ultima)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $hoja->getStyle('H17:Q' . $ultima)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                foreach ($datos as $indice => $fila) {
                    $n = $indice + 17;
                    if ($n !== 17) $hoja->mergeCells('E' . $n . ':G' . $n);
                    $hoja->getCell('D' . $n)->setValueExplicit((string)$fila[3], DataType::TYPE_STRING);
                    $hoja->getRowDimension($n)->setRowHeight(23);
                    if ($indice % 2) $hoja->getStyle('A' . $n . ':R' . $n)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');
                    $est = mb_strtoupper(trim((string)$fila[13]));
                    $col = ['PUNTUAL' => '166534','TARDANZA' => 'A16207','FALTA' => 'B91C1C','FERIADO' => '2563A6','DOMINGO' => '64748B','SIN DATOS' => '64748B'][$est] ?? null;
                    if ($col) $hoja->getStyle('N' . $n)->getFont()->getColor()->setRGB($col);
                }
            } else {
                $hoja->setCellValue('A17', 'Sin registros para los filtros seleccionados.');
            }
            $hoja->getPageSetup()->setPrintArea('A1:R' . $ultima);
            $hoja->getPageSetup()->setOrientation('landscape');
            $hoja->getPageSetup()->setFitToWidth(1);
            $hoja->getPageSetup()->setFitToHeight(0);
            File::ensureDirectoryExists(dirname($ruta));
            $writer = new Xlsx($libro);
            $writer->setPreCalculateFormulas(false);
            $writer->save($ruta);
        } finally {
            $libro->disconnectWorksheets();
        }
    }
}
