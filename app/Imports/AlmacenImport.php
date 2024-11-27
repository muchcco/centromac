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

    protected $usu_reg;

    function __construct($id, $usu_reg) {
            $this->id = $id;
            $this->usu_reg = $usu_reg;
    }
    public function model(array $row)
    {        
        // dd($row);
        $save = new Almacen;
        $save->IDCENTRO_MAC = $this->id;
        $save->IDCATEGORIA = $row['IDCATEGORIA'];
        $save->IDMODELO = $row['IDMODELO'];
        $save->OC = $row['O/C'];
        $save->COD_SBN = $row['CODIGO SBN'];
        $save->COD_PRONSACE = $row['CODIGO PROMSACE'];
        $save->COD_INTERNO_PCM = $row['CODIGO INTERNO'];
        $save->FECHA_OC = $row['FECHA OC'];
        $save->PROVEEDOR = $row['PROVEEDOR'];
        $save->DESCRIPCION = $row['DESCRIPCION'];        
        $save->SERIE_MEDIDA = $row['SERIE / MEDIDA'];
        $save->COLOR = $row['COLOR'];
        $save->UBICACION_EQUIPOS = $row['UBICACIÓN'];
        $save->CANTIDAD = $row['CANTIDAD'];
        $save->USU_REG = $this->usu_reg;
        $save->ESTADO = $row['ESTADO'];
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
