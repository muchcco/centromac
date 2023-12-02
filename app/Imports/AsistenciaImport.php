<?php

namespace App\Imports;

use App\Models\Asistencia;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\DB;
use App\Models\User;

HeadingRowFormatter::default('none');

class AsistenciaImport implements ToModel, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        $fecha = Date::excelToDateTimeObject($row[6])->format('Y-m-d');
        $hora = Date::excelToDateTimeObject($row[6])->format('H:i:s');
        $año = Date::excelToDateTimeObject($row[6])->format('Y');
        $mes = Date::excelToDateTimeObject($row[6])->format('m');

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/

        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $IDCENTRO_MAC = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        
        // dd($IDCENTRO_MAC);
        /*================================================================================================================*/

        $IDTIP = DB::table('M_ASISTENCIA_TIPO')->where('IDCENTRO_MAC', $IDCENTRO_MAC)->first();


        // dd($hora);
        $save = new Asistencia;
        $save->IDTIPO_ASISTENCIA = $IDTIP->IDTIPO_ASISTENCIA;
        $save->IDCENTRO_MAC = $IDCENTRO_MAC;
        $save->NUM_DOC = $row[2];
        $save->FECHA = $fecha;
        $save->HORA = $hora;
        $save->AÑO = $año;
        $save->MES = $mes;
        $save->FECHA_BIOMETRICO = Date::excelToDateTimeObject($row[6]);
        $save->CORRELATIVO_DIA = $row[0];
        $save->save();
    }

    public function batchSize(): int
    {
        return 4000;
    }

    public function chunkSize(): int
    {
        return 4000;
    }
}
