<?php
namespace App\Exports;
use Carbon\Carbon;
use ZipArchive;
use XMLWriter;
class ReporteOcupabilidadPuntualidadExport
{
    private static function texto($v): string
    {
        $s = mb_convert_encoding((string)($v ?? ''), 'UTF-8', 'UTF-8');
        return mb_substr(preg_replace('/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{FFFE}\x{FFFF}]/u', '', $s), 0, 32767);
    }
    private static function celda(XMLWriter $w, string $ref, $valor, int $estilo, bool $numero = false): void
    {
        $w->startElement('c');
        $w->writeAttribute('r', $ref);
        $w->writeAttribute('s', (string)$estilo);
        if ($valor !== null && $valor !== '') {
            if ($numero) $w->writeElement('v', (string)$valor);
            else {
                $w->writeAttribute('t', 'inlineStr');
                $w->startElement('is'); $w->startElement('t');
                $w->writeAttributeNs('xml', 'space', 'http://www.w3.org/XML/1998/namespace', 'preserve');
                $w->text(self::texto($valor));
                $w->endElement(); $w->endElement();
            }
        }
        $w->endElement();
    }
    private static function cambioCelda(string $xml, string $referencia, $valor, bool $numero = false): string
    {
        $pattern = '~<c\b(?=[^>]*\br="'.preg_quote($referencia, '~').'")[^>]*(?:/>|>.*?</c>)~s';
        if (!preg_match($pattern, $xml, $m)) throw new \RuntimeException('Celda de plantilla no encontrada: '.$referencia);
        preg_match('/\bs="(\d+)"/', $m[0], $match);
        $estilo = (int)($match[1] ?? 0);
        $destino = '<c r="'.$referencia.'" s="'.$estilo.'"'.($numero ? '><v>'.(float)$valor.'</v></c>' : ' t="inlineStr"><is><t>'.htmlspecialchars(self::texto($valor), ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>');
        return preg_replace($pattern, str_replace('\\', '\\\\', $destino), $xml, 1);
    }
    public static function save(string $directorio, string $destino, array $indicadores, string $inicio, string $fin, string $entidad, string $centro, int $totalLotes): void
    {
        $plantilla = storage_path('app/templates/PJ OCUPABILIDAD 2025_LIVIANA.xlsx');
        if (!is_file($plantilla)) throw new \RuntimeException('Falta PJ OCUPABILIDAD 2025_LIVIANA.xlsx');
        $original = new ZipArchive();
        if ($original->open($plantilla) !== true) throw new \RuntimeException('No se pudo leer plantilla Excel');
        $hoja = $original->getFromName('xl/worksheets/sheet1.xml');
        if ($hoja === false) { $original->close(); throw new \RuntimeException('No existe la hoja RESUMEN'); }
        $periodo = Carbon::parse($inicio)->format('d/m/Y').' al '.Carbon::parse($fin)->format('d/m/Y');
        $h = (int)$indicadores['dias_habiles']; $a = (int)$indicadores['asistencias']; $p = (int)$indicadores['puntuales'];
        foreach (['A6'=>'REPORTE DE ANS DE OCUPABILIDAD Y PUNTUALIDAD','A7'=>'Entidad: '.$entidad.'   |   Periodo: '.$periodo,'A14'=>'Entidad: '.$entidad."\nPeriodo: ".$periodo."\nCentro MAC: ".$centro,'A15'=>'▥  Detalle de Asistencia'] as $c=>$v) $hoja = self::cambioCelda($hoja,$c,$v);
        foreach (['B10'=>$h,'E10'=>$h ? $a/$h : 0,'H10'=>$a ? $p/$a : 0,'K10'=>(int)$indicadores['centros']] as $c=>$v) $hoja = self::cambioCelda($hoja,$c,$v,true);
        foreach (['B12'=>'Jornadas hábiles evaluadas','E12'=>$a.' de '.$h.' días hábiles','H12'=>$p.' de '.$a.' días marcados','K12'=>'Centros evaluados'] as $c=>$v) $hoja = self::cambioCelda($hoja,$c,$v);
        $inicioData = strpos($hoja, '<sheetData>'); $finData = strpos($hoja, '</sheetData>');
        if ($inicioData === false || $finData === false) { $original->close(); throw new \RuntimeException('Formato de hoja desconocido'); }
        $cuerpoAntes = substr($hoja, $inicioData + strlen('<sheetData>'), $finData-$inicioData-strlen('<sheetData>'));
        // La plantilla conserva solo los encabezados: se retira cualquier fila de detalle preexistente.
        $cuerpoAntes = preg_replace_callback('~<row\b[^>]*\br="(\d+)"[^>]*>.*?</row>~s', function ($m) { return (int)$m[1] >= 17 ? '' : $m[0]; }, $cuerpoAntes);
        $xmlTemporal = $destino.'.sheet.xml';
        $w = new XMLWriter();
        if (!$w->openURI($xmlTemporal)) { $original->close(); throw new \RuntimeException('No se pudo crear hoja temporal'); }
        try {
            $w->startDocument('1.0','UTF-8');
            $w->startElementNS(null,'worksheet','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $w->startElement('sheetData');
            $w->writeRaw($cuerpoAntes);
            $n = 17;
            $col = range('A','R');
            for ($lote=0; $lote<$totalLotes; $lote++) {
                $archivo = $directorio.'/detalle_'.$lote.'.jsonl';
                if (!is_file($archivo)) throw new \RuntimeException('Falta lote de entidad '.$lote);
                $fp = fopen($archivo,'rb');
                if (!$fp) throw new \RuntimeException('No se pudo leer lote '.$lote);
                try {
                    while (($linea=fgets($fp))!==false) {
                        $f=json_decode($linea,true,512,JSON_THROW_ON_ERROR);
                        $fecha = !empty($f['fecha']) ? (int)((strtotime($f['fecha'].' 00:00:00 UTC')-strtotime('1899-12-30 00:00:00 UTC'))/86400) : null;
                        $valores=[$n-16,$f['centro_mac']??'',$f['entidad']??'',(string)($f['dni']??''),$f['colaborador']??'','','',$f['cargo']??'',$f['modulo']??'',$fecha,$f['dia']??'',$f['ingreso']??'',$f['salida']??'',$f['estado']??'',$f['puntual']??'',$f['feriado']??'',$f['tipo_feriado']??'',$f['motivo_feriado']??''];
                        $w->startElement('row'); $w->writeAttribute('r',(string)$n); $w->writeAttribute('ht','23'); $w->writeAttribute('customHeight','1');
                        foreach ($valores as $k=>$valor) self::celda($w,$col[$k].$n,$valor,match($k){3=>2,4=>41,9=>4,13=>3,default=>2},$k===0||$k===9);
                        $w->endElement(); $n++;
                        if ($n>1048576) throw new \RuntimeException('La entidad supera el máximo de filas de Excel');
                    }
                } finally { fclose($fp); }
            }
            if ($n===17) { $w->startElement('row');$w->writeAttribute('r','17');self::celda($w,'A17','Sin registros para el periodo',2);$w->endElement(); }
            $w->endElement();
            $w->endElement(); $w->endDocument(); $w->flush(); unset($w);
            $cabeza = substr($hoja,0,$inicioData);
            $cabeza = preg_replace('~<dimension ref="[^"]*"\s*/>~','<dimension ref="A4:R'.max(17,$n-1).'"/>',$cabeza);
            $cola = substr($hoja, $finData + strlen('</sheetData>'));
            $ultima = max(17, $n-1);
            // Mantener el filtro de la plantilla y actualizar su rango, sin duplicarlo.
            $filtro = '';
            if (preg_match('~<autoFilter\b[^>]*(?:/>|>.*?</autoFilter>)~s', $cola, $m)) {
                $filtro = preg_replace('~\bref="[^"]*"~', 'ref="A16:R'.$ultima.'"', $m[0], 1);
                $cola = str_replace($m[0], '', $cola);
            } else {
                $filtro = '<autoFilter ref="A16:R'.$ultima.'"/>';
            }
            $combinaciones = ['A15:R15','A6:M6','A7:M7','A14:M14','E16:G16'];
            if ($n > 17) for ($fila=17; $fila<=$ultima; $fila++) $combinaciones[]='E'.$fila.':G'.$fila;
            $mezclas = '<mergeCells count="'.count($combinaciones).'">';
            foreach ($combinaciones as $ref) $mezclas .= '<mergeCell ref="'.$ref.'"/>';
            $mezclas .= '</mergeCells>';
            $cola = preg_replace('~<mergeCells\b[^>]*>.*?</mergeCells>~s', '', $cola);
            // Orden OOXML correcto: autoFilter, mergeCells, resto (pageMargins/drawing).
            $cola = $filtro.$mezclas.$cola;
            $tmp = $destino.'.complete.xml';
            $out = fopen($tmp,'wb'); if (!$out) throw new \RuntimeException('Error preparando XLSX');
            fwrite($out,$cabeza);
            $in = fopen($xmlTemporal,'rb');
            $buffer = '';
            while (!feof($in) && strpos($buffer, '<sheetData>') === false) $buffer .= fread($in, 8192);
            $desde = strpos($buffer, '<sheetData>');
            if ($desde === false) throw new \RuntimeException('Detalle XML incompleto');
            $buffer = substr($buffer, $desde);
            $finTag = '</sheetData>';
            while (true) {
                $pos = strpos($buffer, $finTag);
                if ($pos !== false) { fwrite($out, substr($buffer, 0, $pos + strlen($finTag))); break; }
                if (feof($in)) throw new \RuntimeException('Detalle XML sin cierre');
                if (strlen($buffer) > 16384) { fwrite($out, substr($buffer, 0, -64)); $buffer = substr($buffer, -64); }
                $buffer .= fread($in, 65536);
            }
            fclose($in);
            fwrite($out,$cola);fclose($out);
            $zip=new ZipArchive();if ($zip->open($destino,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new \RuntimeException('No se pudo crear Excel final');
            for($i=0;$i<$original->numFiles;$i++) {
                $nombre=$original->getNameIndex($i);
                if ($nombre==='xl/calcChain.xml') continue;
                if ($nombre==='xl/_rels/workbook.xml.rels') {
                    $contenido=$original->getFromIndex($i);
                    if ($contenido===false) throw new \RuntimeException('No se pudo leer workbook.xml.rels');
                    $contenido=preg_replace('~<Relationship\b(?=[^>]*\bType=["\'][^"\']*/calcChain["\'])[^>]*/>~i','',$contenido);
                    if ($contenido===null || !$zip->addFromString($nombre,$contenido)) throw new \RuntimeException('No se pudo actualizar workbook.xml.rels');
                    continue;
                }
                if ($nombre==='[Content_Types].xml') {
                    $contenido=$original->getFromIndex($i);
                    if ($contenido===false) throw new \RuntimeException('No se pudo leer [Content_Types].xml');
                    $contenido=preg_replace('~<Override\b(?=[^>]*\bPartName=["\']/xl/calcChain\.xml["\'])[^>]*/>~i','',$contenido);
                    if ($contenido===null || !$zip->addFromString($nombre,$contenido)) throw new \RuntimeException('No se pudo actualizar [Content_Types].xml');
                    continue;
                }
                if ($nombre==='xl/worksheets/sheet1.xml') {
                    if (!$zip->addFile($tmp,$nombre)) throw new \RuntimeException('No pudo agregar hoja');
                } elseif ($nombre==='xl/workbook.xml') {
                    $workbook=$original->getFromIndex($i);
                    if ($workbook===false) throw new \RuntimeException('No se pudo leer workbook.xml');
                    $workbook=preg_replace_callback(
                        '~(<definedName\b[^>]*\bname="_xlnm\._FilterDatabase"[^>]*>).*?(</definedName>)~s',
                        function ($m) use ($ultima) { return $m[1].'RESUMEN!$A$16:$R$'.$ultima.$m[2]; },
                        $workbook,1,$reemplazos
                    );
                    if ($workbook===null || $reemplazos!==1) throw new \RuntimeException('No se pudo actualizar filtro del libro');
                    $workbook=preg_replace('~<calcPr\b[^>]*/>~','<calcPr calcId="0" fullCalcOnLoad="1"/>',$workbook,1);
                    if ($workbook===null) throw new \RuntimeException('No se pudo actualizar propiedades de cálculo');
                    if (!$zip->addFromString($nombre,$workbook)) throw new \RuntimeException('No pudo agregar workbook.xml');
                } else {
                    $contenido=$original->getFromIndex($i);
                    if ($contenido===false || !$zip->addFromString($nombre,$contenido)) throw new \RuntimeException('No pudo conservar recurso: '.$nombre);
                }
            }
            if (!$zip->close()) throw new \RuntimeException('No se pudo cerrar Excel');
        } finally {
            if (isset($w)) { $w->flush();unset($w); }
            if (isset($original)) $original->close();
            @unlink($xmlTemporal);@unlink($destino.'.complete.xml');
        }
    }
}
