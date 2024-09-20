


<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">N°</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Centro Mac</th>
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
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha de Ingreso al centro MAC</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">N° de modulo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Modalidad de contrato</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número de contrato</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Grado</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Carrera / Profesión</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Jefe inmediato superior</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Teléfono</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Ingles</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">Quechua</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i => $q)
            <tr>
                <th style="border: 1px solid black">{{ $i + 1 }}</th>
                <th style="border: 1px solid black">{{ $q->NOMBRE_MAC ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->ABREV_ENTIDAD ?? '' }}</th>
                <th style="border: 1px solid black">
                    <span class="{{ $q->NOMBREU ? '' : 'text-danger' }}">
                        {{ $q->NOMBREU ?? 'Datos incompletos' }}
                    </span>
                </th>
                <th style="border: 1px solid black">Asesor de Servicio</th>
                <th style="border: 1px solid black">{{ $q->NUM_DOC ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->CELULAR ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->CORREO ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->FECH_NACIMIENTO ? date("d/m/Y", strtotime($q->FECH_NACIMIENTO)) : '' }}</th>
                <th style="border: 1px solid black">{{ $q->ESTADO_CIVIL ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->DF_N_HIJOS ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->PCM_TALLA ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->TVL_ID ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->N_CONTRATO ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->TIP_CAS ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->GI_ID ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->GI_CARRERA ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->GI_CURSO_ESP ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->DLP_JEFE_INMEDIATO ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->DLP_CARGO ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->DLP_TELEFONO ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->NUM_DOC ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->I_INGLES ?? '' }}</th>
                <th style="border: 1px solid black">{{ $q->I_QUECHUA ?? '' }}</th>                
            </tr>    
        @endforeach

    </tbody>

</table>

