<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Almacen;

HeadingRowFormatter::default('none');

class AlmacenImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{

    protected $id;

    function __construct($id) {
            $this->id = $id;
    }
    public function model(array $row)
    {        
        // dd($row);
        $save = new Almacen;
        $save->IDCENTRO_MAC = $this->id;
        $save->OC = $row['O/C'];
        $save->COD_SBN = $row['CODIGO SBN'];
        $save->COD_PRONSACE = $row['CODIGO PROMSACE'];
        $save->COD_INTERNO_PCM = $row['CODIGO INTERNO'];
        $save->FECHA_OC = $row['FECHA OC'];
        $save->PROVEEDOR = $row['PROVEEDOR'];
        $save->DESCRIPCION = $row['DESCRIPCION'];
        $save->MARCA = $row['MARCA'];
        $save->MODELO = $row['MODELO'];
        $save->SERIE_MEDIDA = $row['SERIE / MEDIDA'];
        $save->COLOR = $row['COLOR'];
        $save->UBICACION_EQUIPOS = $row['UBICACION'];
        $save->CANTIDAD = $row['CANTIDAD'];
        $save->save();
    }

    public function headingRow(): int
    {
        return 1;
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
