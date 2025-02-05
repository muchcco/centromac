<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">N°</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Centro MAC</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Entidad</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Nombres y Apellidos</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo / Asesor(a)</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">N° de Documento</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Celular</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Correo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha de Nacimiento</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Estado Civil</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número de Hijos</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Talla de polo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha de Ingreso al centro
                MAC</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Modalidad de contrato</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">De ser CAS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">N° de modulo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número de contrato</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Grado</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Carrera / Profesión</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Jefe inmediato superior</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo Jefe</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Teléfono</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Ingles</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Quechua</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i => $q)
            <tr>
                <th style="border: 1px solid black">{{ $i + 1 }}</th>
                <th style="border: 1px solid black">{{ ($q->NOMBRE_MAC ?? '') === 'null' ? '' : $q->NOMBRE_MAC }}</th>
                <th style="border: 1px solid black">
                    {{ $q->NOMBRE_ENTIDAD ?? $q->ABREV_ENTIDAD ?: 'Datos incompletos' }}</th>
                <th style="border: 1px solid black">
                    <span class="{{ $q->NOMBREU && $q->NOMBREU !== 'null' ? '' : 'text-danger' }}">
                        {{ ($q->NOMBREU ?? 'Datos incompletos') === 'null' ? 'Datos incompletos' : $q->NOMBREU }}
                    </span>
                </th>
                <th style="border: 1px solid black">{{ ($q->NOMBRE_CARGO ?? '') === 'null' ? '' : $q->NOMBRE_CARGO }}
                </th>
                <th style="border: 1px solid black">{{ ($q->NUM_DOC ?? '') === 'null' ? '' : $q->NUM_DOC }}</th>
                <th style="border: 1px solid black">{{ ($q->CELULAR ?? '') === 'null' ? '' : $q->CELULAR }}</th>
                <th style="border: 1px solid black">{{ ($q->CORREO ?? '') === 'null' ? '' : $q->CORREO }}</th>
                <th style="border: 1px solid black">
                    {{ $q->FECH_NACIMIENTO && $q->FECH_NACIMIENTO !== 'null' ? date('d/m/Y', strtotime($q->FECH_NACIMIENTO)) : '' }}
                </th>
                <th style="border: 1px solid black">{{ ($q->ESTADO_CIVIL ?? '') === 'null' ? '' : $q->ESTADO_CIVIL }}
                </th>
                <th style="border: 1px solid black">{{ ($q->DF_N_HIJOS ?? '') === 'null' ? '' : $q->DF_N_HIJOS }}</th>
                <th style="border: 1px solid black">{{ ($q->PCM_TALLA ?? '') === 'null' ? '' : $q->PCM_TALLA }}</th>

                <th style="border: 1px solid black">
                    {{ ($q->PD_FECHA_INGRESO ?? '') === 'null' ? '' : $q->PD_FECHA_INGRESO }}</th>

                <th style="border: 1px solid black">
                    @switch($q->TVL_ID)
                        @case(1)
                            DL 1057-CAS
                        @break

                        @case(2)
                            DL 276
                        @break

                        @case(3)
                            DL 728
                        @break

                        @case(4)
                            Servicios no Personales-SNP
                        @break

                        @case(5)
                            OS
                        @break

                        @case(6)
                            Tercerización
                        @break

                        @default
                            <!-- Si el valor no coincide con ninguno -->
                            --
                    @endswitch
                </th>
                <th style="border: 1px solid black">
                    @switch($q->TVL_ID)
                        @case(1)
                            Permanente
                        @break

                        @case(2)
                            Temporal
                        @break

                        @default
                            <!-- Si el valor no coincide con ninguno -->
                            --
                    @endswitch
                </th>
                <th style="border: 1px solid black">{{ ($q->TIP_CAS ?? '') === 'null' ? '' : $q->TIP_CAS }}</th>
                <th style="border: 1px solid black">{{ ($q->GI_CARRERA ?? '') === 'null' ? '' : $q->N_CONTRATO }}</th>
                <th style="border: 1px solid black">{{ ($q->GI_CARRERA ?? '') === 'null' ? '' : $q->GI_ID }}</th>
                <th style="border: 1px solid black">{{ ($q->GI_CURSO_ESP ?? '') === 'null' ? '' : $q->GI_CARRERA }}
                </th>
                <th style="border: 1px solid black">
                    {{ ($q->DLP_JEFE_INMEDIATO ?? '') === 'null' ? '' : $q->DLP_JEFE_INMEDIATO }}</th>
                <th style="border: 1px solid black">{{ ($q->DLP_CARGO ?? '') === 'null' ? '' : $q->DLP_CARGO }}</th>
                <th style="border: 1px solid black">{{ ($q->DLP_TELEFONO ?? '') === 'null' ? '' : $q->DLP_TELEFONO }}
                </th>
                <th style="border: 1px solid black">
                    @switch($q->I_INGLES)
                        @case(1)
                            NO
                        @break

                        @case(2)
                            Básico
                        @break

                        @case(3)
                            Intermedio
                        @break

                        @case(4)
                            Avanzado
                        @break

                        @default
                            <!-- Si el valor no coincide con ninguno -->
                            --
                    @endswitch
                </th>
                <th style="border: 1px solid black">
                    @switch($q->I_QUECHUA)
                        @case(1)
                            NO
                        @break

                        @case(2)
                            Básico
                        @break

                        @case(3)
                            Intermedio
                        @break

                        @case(4)
                            Avanzado
                        @break

                        @default
                            <!-- Si el valor no coincide con ninguno -->
                            --
                    @endswitch
                </th>
        @endforeach


    </tbody>

</table>
