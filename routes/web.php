<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Modulo\AsistenciaController;
use App\Http\Controllers\Modulo\AsesoresController;
use App\Http\Controllers\Administrador\UsuariosController;
use App\Http\Controllers\Modulo\ServiciosController;


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


/******************  RECURSOS  ********************/

Route::get('provincias/{departamento_id}', [PagesController::class, 'provincias'])->name('provincias');
Route::get('distritos/{provincia_id}', [PagesController::class, 'distritos'])->name('distritos');
Route::get('subtipo_vehiculo/{idsubtipo_vehiculo}', [PagesController::class, 'subtipo_vehiculo'])->name('subtipo_vehiculo');

Auth::routes();

Route::group(['middleware' => ['auth']], function () {

    //PAGINA DE INICIO
    Route::get('/' , [PagesController::class, 'index'])->name('inicio');

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
        Route::post('/delete_asesores' , [AsesoresController::class, 'delete_asesores'])->name('delete_asesores');

        //////  PCM

        Route::get('/pcm' , [PcmController::class, 'pcm'])->name('pcm');
        Route::get('/tablas/tb_pcm' , [PcmController::class, 'tb_pcm'])->name('tablas.tb_pcm');

    });

    Route::group(['prefix'=>'servicios','as'=>'servicios.' ],function () {
        Route::get('/index' , [ServiciosController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [ServiciosController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_servicios' , [ServiciosController::class, 'md_add_servicios'])->name('modals.md_add_servicios');
        Route::post('/modals/md_edit_servicios' , [ServiciosController::class, 'md_edit_servicios'])->name('modals.md_edit_servicios');
        Route::post('/store_servicio' , [ServiciosController::class, 'store_servicio'])->name('store_servicio');
        Route::post('/update_servicio' , [ServiciosController::class, 'update_servicio'])->name('update_servicio');
        Route::post('/delete_servicio' , [ServiciosController::class, 'delete_servicio'])->name('delete_servicio');
    });
    

    /******************************************************   ADMINISTRADOR ************************************************************************/

    Route::group(['prefix'=>'usuarios','as'=>'usuarios.' ],function () {
        
        Route::get('/index' , [indexController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index' , [indexController::class, 'tb_index'])->name('tablas.tb_index');
        
        
    });
});