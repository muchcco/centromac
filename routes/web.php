<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Modulo\AsistenciaController;
use App\Http\Controllers\Modulo\AsesoresController;
use App\Http\Controllers\Administrador\UsuariosController;
use App\Http\Controllers\Modulo\ServiciosController;
use App\Http\Controllers\Modulo\UsuarioController;
use App\Http\Controllers\Modulo\PcmController;
use App\Http\Controllers\Formatos\EvalMotivacionalController;
use App\Http\Controllers\Modulo\AlmacenController;
use App\Http\Controllers\Modulo\AsignacionController;
use App\Http\Controllers\Formatos\F02InicioPerController;

use App\Http\Controllers\Mobile\InternoController;
use App\Http\Controllers\Modulo\ServMacController;


/** FORMULARIO DE REGISTROS PARA BD PERSONAL **/
Route::get('validar.html5' , [PagesController::class, 'validar'])->name('validar');
Route::post('validar_dato' , [PagesController::class, 'validar_dato'])->name('validar_dato');
Route::post('add_datosfamiliares' , [PagesController::class, 'add_datosfamiliares'])->name('add_datosfamiliares');
Route::post('delete_datosfamiliares' , [PagesController::class, 'delete_datosfamiliares'])->name('delete_datosfamiliares');
Route::get('formdata.html5/{num_doc}' , [PagesController::class, 'formdata'])->name('formdata');
Route::post('store_data' , [PagesController::class, 'store_data'])->name('store_data');

Route::get('servicios.html5' , [PagesController::class, 'servicios'])->name('servicios');
Route::get('centro_mac/{idcentro_mac}' , [PagesController::class, 'centro_mac'])->name('centro_mac');
Route::post('validar_servicio' , [PagesController::class, 'validar_servicio'])->name('validar_servicio');
Route::get('list_serv/{idcentro_mac}/{identidad}' , [PagesController::class, 'list_serv'])->name('list_serv');
Route::post('md_edit_servicios_ext' , [PagesController::class, 'md_edit_servicios_ext'])->name('md_edit_servicios_ext');
Route::post('update_obsev' , [PagesController::class, 'update_obsev'])->name('update_obsev');

/****************** CONSUMO NOVOSGA ***************/

Route::get('vista.html5' , [PagesController::class, 'vista'])->name('vista');
Route::post('validar_entidad' , [PagesController::class, 'validar_entidad'])->name('validar_entidad');
Route::get('entidad_cola.html5/{identidad}' , [PagesController::class, 'entidad_cola'])->name('entidad_cola');


/******************  RECURSOS  ********************/

Route::post('buscar_dni', [PagesController::class, 'buscar_dni'])->name('buscar_dni');
Route::get('provincias/{departamento_id}', [PagesController::class, 'provincias'])->name('provincias');
Route::get('distritos/{provincia_id}', [PagesController::class, 'distritos'])->name('distritos');
Route::get('subtipo_vehiculo/{idsubtipo_vehiculo}', [PagesController::class, 'subtipo_vehiculo'])->name('subtipo_vehiculo');
Route::get('consultas_novo', [PagesController::class, 'consultas_novo'])->name('consultas_novo');

/******************  GRAFICOS  ********************/

Route::get('asist_xdia', [PagesController::class, 'asist_xdia'])->name('asist_xdia');


/*********************************************************  MODO MOBILE  ***********************************************************************/

Route::prefix('mobile/')->as('mobile.')->group( function () {

    Route::get('index', [InternoController::class, 'index'])->name('index');
    Route::get('entidad_dat', [InternoController::class, 'entidad_dat'])->name('entidad_dat');
    Route::get('det_entidad/{idcentro_mac}/{identidad}', [InternoController::class, 'det_entidad'])->name('det_entidad');
    
});

/******************  DATOS DE LOGIN  ******************************/

Route::get('/login_verificacion/get/' , [PagesController::class, 'login_verificacion'])->name('login_verificacion.get');

Auth::routes();

Route::group(['middleware' => ['auth']], function () {

    //PAGINA DE INICIO
    Route::get('/' , [PagesController::class, 'index'])->name('inicio');

    Route::get('/home', function(){
        return redirect('/');
    });

    /******************************************************   EXTERNO *****************************************************************************/

    // SE ALMACENA LOS ACCESOS A LAS PAGINAS EXTERNA QUE NO ES NECESARIO LOGGIN
    Route::get('/externo' , [PagesController::class, 'externo'])->name('externo');

    /**********************************************************************************************************************************************/

    Route::group(['prefix'=>'asistencia','as'=>'asistencia.' ],function () {

        Route::get('/asistencia' , [AsistenciaController::class, 'asistencia'])->name('asistencia');
        Route::get('/tablas/tb_asistencia' , [AsistenciaController::class, 'tb_asistencia'])->name('tablas.tb_asistencia');
        Route::post('/modals/md_add_asistencia' , [AsistenciaController::class, 'md_add_asistencia'])->name('modals.md_add_asistencia');
        Route::post('/modals/md_detalle' , [AsistenciaController::class, 'md_detalle'])->name('modals.md_detalle');
        Route::post('/store_asistencia' , [AsistenciaController::class, 'store_asistencia'])->name('store_asistencia');

        //POR ENTIDAD
        Route::get('/det_entidad.html/{mac}' , [AsistenciaController::class, 'det_entidad'])->name('det_entidad');         
        Route::get('/table/tb_det_entidad' , [AsistenciaController::class, 'tb_det_entidad'])->name('tablas.tb_det_entidad');
        Route::post('/modals/md_det_entidad_perso' , [AsistenciaController::class, 'md_det_entidad_perso'])->name('modals.md_det_entidad_perso');
        Route::get('/dow_resumen' , [AsistenciaController::class, 'dow_resumen'])->name('dow_resumen');        

        //DETALLE ASISTENCIA
        Route::get('/det_us/{id}.html' , [AsistenciaController::class, 'det_us'])->name('det_us');
        Route::get('/tablas/tb_det_us' , [AsistenciaController::class, 'tb_det_us'])->name('tablas.tb_det_us');

        //EXPORT DATA
        Route::get('/asistencia_pdf' , [AsistenciaController::class, 'asistencia_pdf'])->name('asistencia_pdf');
        Route::get('/asistencia_excel' , [AsistenciaController::class, 'asistencia_excel'])->name('asistencia_excel');
        Route::get('/exportgroup_excel' , [AsistenciaController::class, 'exportgroup_excel'])->name('exportgroup_excel');
        Route::get('/exportgroup_excel_pr' , [AsistenciaController::class, 'exportgroup_excel_pr'])->name('exportgroup_excel_pr');


    });

    Route::group(['prefix'=>'personal','as'=>'personal.' ],function () {

        //////  ASESORES

        Route::get('/asesores' , [AsesoresController::class, 'asesores'])->name('asesores');
        Route::get('/tablas/tb_asesores' , [AsesoresController::class, 'tb_asesores'])->name('tablas.tb_asesores');
        Route::post('/modals/md_add_asesores' , [AsesoresController::class, 'md_add_asesores'])->name('modals.md_add_asesores');
        Route::post('/store_asesores' , [AsesoresController::class, 'store_asesores'])->name('store_asesores');
        Route::post('/modals/md_edit_asesores' , [AsesoresController::class, 'md_edit_asesores'])->name('modals.md_edit_asesores');
        Route::post('/update_asesores' , [AsesoresController::class, 'update_asesores'])->name('update_asesores');
        Route::post('/modals/md_baja_asesores' , [AsesoresController::class, 'md_baja_asesores'])->name('modals.md_baja_asesores');
        Route::post('/baja_asesores' , [AsesoresController::class, 'baja_asesores'])->name('baja_asesores');

        //////  PCM

        Route::get('/pcm' , [PcmController::class, 'pcm'])->name('pcm');
        Route::get('/tablas/tb_pcm' , [PcmController::class, 'tb_pcm'])->name('tablas.tb_pcm');
        Route::post('/modals/md_add_pcm' , [PcmController::class, 'md_add_pcm'])->name('modals.md_add_pcm');
        Route::post('/store_pcm' , [PcmController::class, 'store_pcm'])->name('store_pcm');
        Route::post('/modals/md_edit_pcm' , [PcmController::class, 'md_edit_pcm'])->name('modals.md_edit_pcm');
        Route::post('/update_pcm' , [PcmController::class, 'update_pcm'])->name('update_pcm');
        Route::post('/delete_pcm' , [PcmController::class, 'delete_pcm'])->name('delete_pcm');
        Route::post('/modals/md_baja_pcm' , [PcmController::class, 'md_baja_pcm'])->name('modals.md_baja_pcm');
        Route::post('/baja_pcm' , [PcmController::class, 'baja_pcm'])->name('baja_pcm');

    });

    /********************************************************** SERVICIOS *******************************************************************************/

    Route::group(['prefix'=>'serv_mac','as'=>'serv_mac.' ],function () {
       
        Route::get('/index', [ServMacController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [ServMacController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_servicios' , [ServMacController::class, 'md_add_servicios'])->name('modals.md_add_servicios');
        Route::post('/store_servicio' , [ServMacController::class, 'store_servicio'])->name('store_servicio');
        Route::post('/delete_servicio' , [ServMacController::class, 'delete_servicio'])->name('delete_servicio');
        Route::get('/export_serv_entidad' , [ServMacController::class, 'export_serv_entidad'])->name('export_serv_entidad');

    });  

    Route::group(['prefix'=>'servicios','as'=>'servicios.' ],function () {
        Route::get('/index' , [ServiciosController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [ServiciosController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_servicios' , [ServiciosController::class, 'md_add_servicios'])->name('modals.md_add_servicios');
        Route::post('/modals/md_edit_servicios' , [ServiciosController::class, 'md_edit_servicios'])->name('modals.md_edit_servicios');
        Route::post('/store_servicio' , [ServiciosController::class, 'store_servicio'])->name('store_servicio');
        Route::post('/update_servicio' , [ServiciosController::class, 'update_servicio'])->name('update_servicio');
        Route::post('/delete_servicio' , [ServiciosController::class, 'delete_servicio'])->name('delete_servicio');
        Route::get('/export_serv_entidad' , [ServiciosController::class, 'export_serv_entidad'])->name('export_serv_entidad');
    });

    /******************************************************   FORMATOS *****************************************************************************/
    
    Route::group(['prefix'=>'formatos','as'=>'formatos.' ],function () {
        
        Route::group(['prefix' => 'evaluacion_motivacional', 'as' => 'evaluacion_motivacional.'], function(){

            Route::get('/index' , [EvalMotivacionalController::class, 'index'])->name('index');
            Route::get('/tablas/tb_index' , [EvalMotivacionalController::class, 'tb_index'])->name('tablas.tb_index');
            Route::post('/store_datos' , [EvalMotivacionalController::class, 'store_datos'])->name('store_datos');
            Route::post('/delete_datos' , [EvalMotivacionalController::class, 'delete_datos'])->name('delete_datos');

            Route::get('/reporte' , [EvalMotivacionalController::class, 'reporte'])->name('reporte');

        });

        Route::group(['prefix' => 'f_02_inicio_oper', 'as' => 'f_02_inicio_oper.'], function(){
            Route::get('/index' , [F02InicioPerController::class, 'index'])->name('index');
            Route::get('/formulario/{fecha}' , [F02InicioPerController::class, 'formulario'])->name('formulario');
            Route::post('/store_form' , [F02InicioPerController::class, 'store_form'])->name('store_form');
            Route::get('/tablas/tb_index' , [F02InicioPerController::class, 'tb_index'])->name('tablas.tb_index');
        });

    });

    /******************************************************   ASIGNACION ***************************************************************************/

    Route::group(['prefix'=>'asignacion','as'=>'asignacion.' ],function () {
        Route::get('/index' , [AsignacionController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [AsignacionController::class, 'tb_index'])->name('tablas.tb_index');
        Route::get('/asignacion_inventario/{idpersonal}' , [AsignacionController::class, 'asignacion_inventario'])->name('asignacion_inventario');
        Route::get('/tablas/tb_asignacion' , [AsignacionController::class, 'tb_asignacion'])->name('tablas.tb_asignacion');
        Route::post('/store_item' , [AsignacionController::class, 'store_item'])->name('store_item');
        Route::post('/eliminar_item' , [AsignacionController::class, 'eliminar_item'])->name('eliminar_item');
        Route::post('/modals/md_add_estado' , [AsignacionController::class, 'md_add_estado'])->name('modals.md_add_estado');
        Route::post('/modals/md_add_observacion' , [AsignacionController::class, 'md_add_observacion'])->name('modals.md_add_observacion');
        Route::post('/modals/md_acep_asesor' , [AsignacionController::class, 'md_acep_asesor'])->name('modals.md_acep_asesor');
        Route::post('/store_estado' , [AsignacionController::class, 'store_estado'])->name('store_estado');
        Route::post('/store_observacion' , [AsignacionController::class, 'store_observacion'])->name('store_observacion');
        Route::post('/estado_borrador' , [AsignacionController::class, 'estado_borrador'])->name('estado_borrador');
        Route::post('/cargar_documento_acept' , [AsignacionController::class, 'cargar_documento_acept'])->name('cargar_documento_acept');
        
        /* RECURSOS DE ESTE GRUPO */
        Route::post('/almacen_select' , [AsignacionController::class, 'almacen_select'])->name('almacen_select');
        Route::get('/pdf/borrador_pdf/{idpersonal}' , [AsignacionController::class, 'borrador_pdf'])->name('pdf.borrador_pdf');
        Route::get('/pdf/orginal_pdf/{idpersonal}' , [AsignacionController::class, 'orginal_pdf'])->name('pdf.orginal_pdf');
    });


    /******************************************************   ALMACEN  *****************************************************************************/

    Route::group(['prefix'=>'almacen','as'=>'almacen.' ],function () {
        Route::get('/index' , [AlmacenController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [AlmacenController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_datos' , [AlmacenController::class, 'md_add_datos'])->name('modals.md_add_datos');
        Route::post('/store_datos' , [AlmacenController::class, 'store_datos'])->name('store_datos');
        
    });

    /******************************************************   ADMINISTRADOR ************************************************************************/

    Route::group(['prefix'=>'usuarios','as'=>'usuarios.' ],function () {
        
        Route::get('/index' , [UsuarioController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [UsuarioController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_usuario' , [UsuarioController::class, 'md_add_usuario'])->name('modals.md_add_usuario');
        Route::post('/modals/md_edit_usuario' , [UsuarioController::class, 'md_edit_usuario'])->name('modals.md_edit_usuario');
        Route::post('/modals/md_password_usuario' , [UsuarioController::class, 'md_password_usuario'])->name('modals.md_password_usuario');
        Route::post('/store_user' , [UsuarioController::class, 'store_user'])->name('store_user');
        Route::post('/update_user' , [UsuarioController::class, 'update_user'])->name('update_user');
        Route::post('/updatepass_user' , [UsuarioController::class, 'updatepass_user'])->name('updatepass_user');
        Route::post('/delete_user' , [UsuarioController::class, 'delete_user'])->name('delete_user');
        
    });
});