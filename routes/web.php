<?php

/*************************************************************************************************************************************************/
/*
DESARROLLADO POR:   JHON KEVIN MUCHCCO ROJAS

MODIFICACIONES:
======================================================
==  NOMBRE      ==============    FECHA DE MODIFICACION     =============           MODULO             =============
==              ==============                              =============                              =============
==              ==============                              =============                              =============

==  JHON KEVIN  ==   VERSION 1.5.0                                   
===========================================================================*/

/*************************************************************************************************************************************************/

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
use App\Http\Controllers\Formatos\FormFelicitacionesController;
use App\Http\Controllers\Administrador\ConfiguracionController;
use App\Http\Controllers\Dashboard\PanelInicioController;
use App\Http\Controllers\ExternoController;
use App\Http\Controllers\Indicador\Ocupabilidad1Controller;
use App\Http\Controllers\Indicador\Puntualidad1Controller;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Modulo\PuntualidadController;
use App\Http\Controllers\Modulo\FeriadoController;
use App\Http\Controllers\Modulo\ModuloController;
use App\Http\Controllers\Modulo\OcupabilidadController;
use App\Http\Controllers\Modulo\ItineranteController;
use App\Http\Controllers\Modulo\PersonalModuloController;
use App\Http\Controllers\Modulo\VerificacionController;
use App\Http\Controllers\Modulo\MantenimientoController;
use App\Http\Controllers\Modulo\ReporteOcupabilidadController;
use App\Http\Controllers\Modulo\TipoIntObsController;
use App\Http\Controllers\Modulo\ObservacionInterrupcionController;
use App\Http\Controllers\Modulo\InterrupcionController;
use App\Http\Controllers\Modulo\ObservacionController;



/** FORMULARIO DE REGISTROS PARA BD PERSONAL **/
Route::get('validar.html5', [PagesController::class, 'validar'])->name('validar');
Route::post('validar_dato', [PagesController::class, 'validar_dato'])->name('validar_dato');
Route::post('add_datosfamiliares', [PagesController::class, 'add_datosfamiliares'])->name('add_datosfamiliares');
Route::post('delete_datosfamiliares', [PagesController::class, 'delete_datosfamiliares'])->name('delete_datosfamiliares');
Route::get('formdata.html5/{num_doc}', [PagesController::class, 'formdata'])->name('formdata');
Route::get('formdata_pcm.html5/{num_doc}', [PagesController::class, 'formdata_pcm'])->name('formdata_pcm');
Route::post('store_data', [PagesController::class, 'store_data'])->name('store_data');

Route::get('servicios.html5', [PagesController::class, 'servicios'])->name('servicios');
Route::get('centro_mac/{idcentro_mac}', [PagesController::class, 'centro_mac'])->name('centro_mac');
Route::post('validar_servicio', [PagesController::class, 'validar_servicio'])->name('validar_servicio');
Route::get('list_serv/{idcentro_mac}/{identidad}', [PagesController::class, 'list_serv'])->name('list_serv');
Route::post('md_edit_servicios_ext', [PagesController::class, 'md_edit_servicios_ext'])->name('md_edit_servicios_ext');
Route::post('update_obsev', [PagesController::class, 'update_obsev'])->name('update_obsev');

/****************** CONSUMO NOVOSGA ***************/

Route::get('vista.html5', [PagesController::class, 'vista'])->name('vista');
Route::post('validar_entidad', [PagesController::class, 'validar_entidad'])->name('validar_entidad');
Route::get('entidad_cola.html5/{identidad}', [PagesController::class, 'entidad_cola'])->name('entidad_cola');


/******************  RECURSOS  ********************/

Route::post('buscar_dni', [PagesController::class, 'buscar_dni'])->name('buscar_dni');
Route::get('provincias/{departamento_id}', [PagesController::class, 'provincias'])->name('provincias');
Route::get('distritos/{provincia_id}', [PagesController::class, 'distritos'])->name('distritos');
Route::get('subtipo_vehiculo/{idsubtipo_vehiculo}', [PagesController::class, 'subtipo_vehiculo'])->name('subtipo_vehiculo');
Route::get('consultas_novo', [PagesController::class, 'consultas_novo'])->name('consultas_novo');
Route::get('entidad/{idcentro_mac}', [PagesController::class, 'entidad'])->name('entidad');



/******************  GRAFICOS  ********************/

Route::get('asist_xdia', [PagesController::class, 'asist_xdia'])->name('asist_xdia');


/*********************************************************  MODO MOBILE  ***********************************************************************/

Route::prefix('mobile/')->as('mobile.')->group(function () {

    Route::get('index', [InternoController::class, 'index'])->name('index');
    Route::get('entidad_dat', [InternoController::class, 'entidad_dat'])->name('entidad_dat');
    Route::get('det_entidad/{idcentro_mac}/{identidad}', [InternoController::class, 'det_entidad'])->name('det_entidad');
});

/******************  DATOS DE LOGIN  ******************************/

Route::get('/login_verificacion/get/', [PagesController::class, 'login_verificacion'])->name('login_verificacion.get');

Auth::routes();

// Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('login', function () {
    return redirect()->away(env('REDIRECT_URL', 'http://190.187.182.55:8081/oauth/login'));
})->name('login');
Route::get('login', [AuthController::class, 'login']);
Route::get('authenticate', [AuthController::class, 'authenticateWithToken']);
// Route::post('logout', [AuthController::class, 'logout'])->name('logout');


/**************************************************************** CUMPLEAÑOS *******************************************************************/

Route::group(['prefix' => 'externo/cumpleaños/', 'as' => 'externo.cumpleaños.'], function () {

    Route::get('cumpleaño', [ExternoController::class, 'cumpleaño'])->name('cumpleaño');
    Route::post('cumpleaño_validar', [ExternoController::class, 'cumpleaño_validar'])->name('cumpleaño_validar');
    Route::get('cumpleaños_dat', [ExternoController::class, 'cumpleaños_dat'])->name('cumpleaños_dat');
});

/***********************************************************************************************************************************************/
// CONFIGURACION DENTRO DE LA AUTH
/***********************************************************************************************************************************************/
Route::group(['middleware' => ['auth']], function () {

    //PAGINA DE INICIO
    Route::get('/', [PagesController::class, 'index'])->name('inicio');

    Route::get('/home', function () {
        return redirect('/');
    });

    Route::post('modal-password', [PagesController::class, 'modalPassword'])->name('modal-password');
    Route::post('store-password', [PagesController::class, 'storePassword'])->name('store-password');

    /******************************************************   EXTERNO *****************************************************************************/

    // SE ALMACENA LOS ACCESOS A LAS PAGINAS EXTERNA QUE NO ES NECESARIO LOGGIN
    Route::get('/externo-int', [PagesController::class, 'externoInt'])->name('externo-int');

    /********************************************************* ASISTENCIA  ***************************************************************/

    Route::group(['prefix' => 'asistencia', 'as' => 'asistencia.'], function () {

        Route::get('/asistencia', [AsistenciaController::class, 'asistencia'])->name('asistencia');
        Route::get('/tablas/tb_asistencia', [AsistenciaController::class, 'tb_asistencia'])->name('tablas.tb_asistencia');
        Route::post('/modals/md_add_asistencia', [AsistenciaController::class, 'md_add_asistencia'])->name('modals.md_add_asistencia');
        Route::post('/modals/md_add_asistencia_callao', [AsistenciaController::class, 'md_add_asistencia_callao'])->name('modals.md_add_asistencia_callao');
        Route::post('/modals/md_detalle', [AsistenciaController::class, 'md_detalle'])->name('modals.md_detalle');
        Route::post('/modals/md_agregar_asistencia', [AsistenciaController::class, 'md_agregar_asistencia'])->name('modals.md_agregar_asistencia');
        Route::post('/store_asistencia', [AsistenciaController::class, 'store_asistencia'])->name('store_asistencia');
        Route::post('/store_asistencia_callao', [AsistenciaController::class, 'store_asistencia_callao'])->name('store_asistencia_callao');
        Route::post('/store_agregar_asistencia', [AsistenciaController::class, 'store_agregar_asistencia'])->name('store_agregar_asistencia');
        Route::post('/eliminar-hora', [AsistenciaController::class, 'eliminarHora'])->name('eliminar_hora');
        Route::post('/dow_asistencia', [AsistenciaController::class, 'dow_asistencia'])->name('dow_asistencia');
        Route::post('/modals/md_moficicar_modulo', [AsistenciaController::class, 'md_moficicar_modulo'])->name('modals.md_moficicar_modulo');
        Route::post('/modals/md_add_dni_asistencia', [AsistenciaController::class, 'md_add_dni_asistencia'])->name('modals.md_add_dni_asistencia');

        //POR ENTIDAD
        Route::get('/det_entidad.html/{mac}', [AsistenciaController::class, 'det_entidad'])->name('det_entidad');
        Route::get('/table/tb_det_entidad', [AsistenciaController::class, 'tb_det_entidad'])->name('tablas.tb_det_entidad');
        Route::post('/modals/md_det_entidad_perso', [AsistenciaController::class, 'md_det_entidad_perso'])->name('modals.md_det_entidad_perso');
        Route::get('/dow_resumen', [AsistenciaController::class, 'dow_resumen'])->name('dow_resumen');

        //DETALLE ASISTENCIA
        Route::get('/det_us/{id}.html', [AsistenciaController::class, 'det_us'])->name('det_us');
        Route::get('/tablas/tb_det_us', [AsistenciaController::class, 'tb_det_us'])->name('tablas.tb_det_us');

        //EXPORT DATA
        Route::get('/asistencia_pdf', [AsistenciaController::class, 'asistencia_pdf'])->name('asistencia_pdf');
        Route::get('/asistencia_excel', [AsistenciaController::class, 'asistencia_excel'])->name('asistencia_excel');
        Route::get('/exportgroup_excel', [AsistenciaController::class, 'exportgroup_excel'])->name('exportgroup_excel');
        Route::get('/exportgroup_excel_pr', [AsistenciaController::class, 'exportgroup_excel_pr'])->name('exportgroup_excel_pr');
        Route::get('/exportgroup_excel_general', [AsistenciaController::class, 'exportgroup_excel_general'])->name('exportgroup_excel_general');
        // EXPORT DATA HUANUCO
        Route::post('/migrar-datos', [AsistenciaController::class, 'migrarDatos'])->name('migrar.datos');

        Route::get('/upload-progress', [AsistenciaController::class, 'getUploadProgress'])->name('upload.progress');
    });

    /********************************************************** REGISTRO DE PERSONAL *******************************************************************/

    Route::group(['prefix' => 'personal', 'as' => 'personal.'], function () {

        //////  ASESORES

        Route::get('/asesores', [AsesoresController::class, 'asesores'])->name('asesores');
        Route::get('/tablas/tb_asesores', [AsesoresController::class, 'tb_asesores'])->name('tablas.tb_asesores');
        Route::post('/modals/md_add_asesores', [AsesoresController::class, 'md_add_asesores'])->name('modals.md_add_asesores');
        Route::post('/store_asesores', [AsesoresController::class, 'store_asesores'])->name('store_asesores');
        Route::post('/store_asesores_more', [AsesoresController::class, 'store_asesores_more'])->name('store_asesores_more');
        Route::post('/modals/md_edit_asesores', [AsesoresController::class, 'md_edit_asesores'])->name('modals.md_edit_asesores');
        Route::post('/update_asesores', [AsesoresController::class, 'update_asesores'])->name('update_asesores');
        Route::post('/modals/md_baja_asesores', [AsesoresController::class, 'md_baja_asesores'])->name('modals.md_baja_asesores');
        Route::post('/modals/md_cambiar_entidad', [AsesoresController::class, 'md_cambiar_entidad'])->name('modals.md_cambiar_entidad');
        Route::post('/modals/md_cambiar_modulo', [AsesoresController::class, 'md_cambiar_modulo'])->name('modals.md_cambiar_modulo');
        Route::post('/modals/md_cambio_mac', [AsesoresController::class, 'md_cambio_mac'])->name('modals.md_cambio_mac');
        Route::post('/update_entidad', [AsesoresController::class, 'update_entidad'])->name('update_entidad');
        Route::post('/update_mac', [AsesoresController::class, 'update_mac'])->name('update_mac');
        Route::post('/delete_mac_mod', [AsesoresController::class, 'delete_mac_mod'])->name('delete_mac_mod');
        Route::post('/update_modulo', [AsesoresController::class, 'update_modulo'])->name('update_modulo');
        Route::post('/baja_asesores', [AsesoresController::class, 'baja_asesores'])->name('baja_asesores');

        Route::get('/exportasesores_excel', [AsesoresController::class, 'exportasesores_excel'])->name('exportasesores_excel');

        //////  PCM

        Route::get('/pcm', [PcmController::class, 'pcm'])->name('pcm');
        Route::get('/tablas/tb_pcm', [PcmController::class, 'tb_pcm'])->name('tablas.tb_pcm');
        Route::post('/modals/md_add_pcm', [PcmController::class, 'md_add_pcm'])->name('modals.md_add_pcm');
        Route::post('/store_pcm', [PcmController::class, 'store_pcm'])->name('store_pcm');
        Route::post('/modals/md_edit_pcm', [PcmController::class, 'md_edit_pcm'])->name('modals.md_edit_pcm');
        Route::post('/update_pcm', [PcmController::class, 'update_pcm'])->name('update_pcm');
        Route::post('/delete_pcm', [PcmController::class, 'delete_pcm'])->name('delete_pcm');
        Route::post('/modals/md_baja_pcm', [PcmController::class, 'md_baja_pcm'])->name('modals.md_baja_pcm');
        Route::post('/baja_pcm', [PcmController::class, 'baja_pcm'])->name('baja_pcm');

        /// EXPORTABLE

        Route::get('/exporta_excel', [PcmController::class, 'exporta_excel'])->name('exporta_excel');
    });

    /********************************************************** SERVICIOS *******************************************************************************/

    Route::group(['prefix' => 'serv_mac', 'as' => 'serv_mac.'], function () {

        Route::get('/index', [ServMacController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ServMacController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_servicios', [ServMacController::class, 'md_add_servicios'])->name('modals.md_add_servicios');
        Route::post('/store_servicio', [ServMacController::class, 'store_servicio'])->name('store_servicio');
        Route::post('/delete_servicio', [ServMacController::class, 'delete_servicio'])->name('delete_servicio');
        Route::get('/export_serv_entidad', [ServMacController::class, 'export_serv_entidad'])->name('export_serv_entidad');
    });

    Route::group(['prefix' => 'servicios', 'as' => 'servicios.'], function () {
        Route::get('/index', [ServiciosController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ServiciosController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_servicios', [ServiciosController::class, 'md_add_servicios'])->name('modals.md_add_servicios');
        Route::post('/modals/md_edit_servicios', [ServiciosController::class, 'md_edit_servicios'])->name('modals.md_edit_servicios');
        Route::post('/store_servicio', [ServiciosController::class, 'store_servicio'])->name('store_servicio');
        Route::post('/update_servicio', [ServiciosController::class, 'update_servicio'])->name('update_servicio');
        Route::post('/delete_servicio', [ServiciosController::class, 'delete_servicio'])->name('delete_servicio');
        Route::get('/export_serv_entidad', [ServiciosController::class, 'export_serv_entidad'])->name('export_serv_entidad');
    });

    /******************************************************   MODULO Y PERSONAL ************************************************************************/

    // Rutas para la gestión de modulos
    Route::group(['prefix' => 'personalmodulos', 'as' => 'personalModulo.'], function () {
        Route::get('/index', [PersonalModuloController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [PersonalModuloController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_personalModulo', [PersonalModuloController::class, 'create'])->name('modals.md_add_personalModulo');
        Route::post('/modals/md_edit_personalModulo', [PersonalModuloController::class, 'edit'])->name('modals.md_edit_personalModulo');
        Route::post('/store_personalModulo', [PersonalModuloController::class, 'store'])->name('store_personalModulo');
        Route::post('/update_personalModulo', [PersonalModuloController::class, 'update'])->name('update_personalModulo');
        Route::post('/delete_personalModulo', [PersonalModuloController::class, 'destroy'])->name('delete_personalModulo');
        Route::get('/get-fechas-modulo/{id}', [PersonalModuloController::class, 'getFechasModulo'])->name('getFechasModulo');
    });

    /******************************************************   MODULO Y PERSONAL ITINERANTE ************************************************************************/

    // Rutas para la gestión de modulos
    Route::group(['prefix' => 'itinerantes', 'as' => 'personalModuloI.'], function () {
        Route::get('/index', [ItineranteController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ItineranteController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_personalModuloI', [ItineranteController::class, 'create'])->name('modals.md_add_personalModuloI');
        Route::post('/modals/md_edit_personalModuloI', [ItineranteController::class, 'edit'])->name('modals.md_edit_personalModuloI');
        Route::post('/store_personalModuloI', [ItineranteController::class, 'store'])->name('store_personalModuloI');
        Route::post('/update_personalModuloI', [ItineranteController::class, 'update'])->name('update_personalModuloI');
        Route::post('/delete_personalModuloI', [ItineranteController::class, 'destroy'])->name('delete_personalModuloI');
    });
    /******************************************************   FERIADO ************************************************************************/

    // Rutas para la gestión de feriados
    Route::group(['prefix' => 'feriados', 'as' => 'feriados.'], function () {
        Route::get('/index', [FeriadoController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [FeriadoController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_feriado', [FeriadoController::class, 'create'])->name('modals.md_add_feriado');
        Route::post('/modals/md_edit_feriado', [FeriadoController::class, 'edit'])->name('modals.md_edit_feriado');
        Route::post('/store_feriado', [FeriadoController::class, 'store'])->name('store_feriado');
        Route::post('/update_feriado', [FeriadoController::class, 'update'])->name('update_feriado');
        Route::post('/delete_feriado', [FeriadoController::class, 'destroy'])->name('delete_feriado');
    });
    /******************************************************   TIPO INTERRUPCIONES E OBSERVACIONES
     *  ************************************************************************/
    // Rutas para la gestión de observaciones e interrupciones
    Route::group(['prefix' => 'tipo_int_obs', 'as' => 'tipo_int_obs.'], function () {
        Route::get('/index', [TipoIntObsController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [TipoIntObsController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_tipo_obs', [TipoIntObsController::class, 'create'])->name('modals.md_add_tipo_obs');
        Route::post('/modals/md_edit_tipo_obs', [TipoIntObsController::class, 'edit'])->name('modals.md_edit_tipo_obs');
        Route::post('/store_tipo_obs', [TipoIntObsController::class, 'store'])->name('store_tipo_obs');
        Route::post('/update_tipo_obs', [TipoIntObsController::class, 'update'])->name('update_tipo_obs');
        Route::post('/delete_tipo_obs', [TipoIntObsController::class, 'destroy'])->name('delete_tipo_obs');
        Route::post('/toggle_status', [TipoIntObsController::class, 'toggleStatus'])->name('toggle_status');
    });

    Route::prefix('interrupcion')->name('interrupcion.')->group(function () {
        Route::get('/index', [InterrupcionController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [InterrupcionController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_interrupcion', [InterrupcionController::class, 'create'])->name('modals.md_add_interrupcion');
        Route::post('/store', [InterrupcionController::class, 'store'])->name('store');
        Route::post('/edit', [InterrupcionController::class, 'edit'])->name('edit');
        Route::post('/update', [InterrupcionController::class, 'update'])->name('update');
        Route::post('/delete', [InterrupcionController::class, 'destroy'])->name('delete');
        Route::post('/modals/md_subsanar_interrupcion', [InterrupcionController::class, 'subsanarModal'])->name('modals.md_subsanar_interrupcion');
        Route::post('/subsanar', [InterrupcionController::class, 'subsanarGuardar'])->name('subsanar');
    });
    Route::prefix('observacion')->name('observacion.')->group(function () {
        Route::get('/index', [ObservacionController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ObservacionController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_observacion', [ObservacionController::class, 'create'])->name('modals.md_add_observacion');
        Route::post('/modals/md_edit_observacion', [ObservacionController::class, 'edit'])->name('modals.md_edit_observacion');
        Route::post('/modals/md_subsanar_observacion', [ObservacionController::class, 'subsanarModal'])->name('modals.md_subsanar_observacion');
        Route::post('/store', [ObservacionController::class, 'store'])->name('store');
        Route::post('/update', [ObservacionController::class, 'update'])->name('update');
        Route::post('/delete', [ObservacionController::class, 'destroy'])->name('delete');
        Route::post('/subsanar', [ObservacionController::class, 'subsanarGuardar'])->name('subsanar');
        Route::post('/modals/md_ver_observacion', [ObservacionController::class, 'ver'])->name('modals.md_ver_observacion');
        Route::get('/observacion/export-excel', [ObservacionController::class, 'export_excel'])->name('export_excel');
    });


    /******************************************************   OBSERVACION INTERRUPCIONES ************************************************************************/
    // Rutas para la gestión de observaciones e interrupciones
    Route::group(['prefix' => 'observacion_interrupcion', 'as' => 'observacion_interrupcion.'], function () {
        Route::get('/index', [ObservacionInterrupcionController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ObservacionInterrupcionController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_observacion', [ObservacionInterrupcionController::class, 'create'])->name('modals.md_add_observacion');
        Route::post('/modals/md_edit_observacion', [ObservacionInterrupcionController::class, 'edit'])->name('modals.md_edit_observacion');
        Route::post('/modals/md_subsanar_observacion', [ObservacionInterrupcionController::class, 'subsanarModal'])->name('modals.md_subsanar_observacion');
        Route::post('/subsanar', [ObservacionInterrupcionController::class, 'subsanarGuardar'])->name('subsanar_observacion');
        Route::post('/store', [ObservacionInterrupcionController::class, 'store'])->name('store_observacion');
        Route::post('/update', [ObservacionInterrupcionController::class, 'update'])->name('update_observacion');
        Route::post('/delete', [ObservacionInterrupcionController::class, 'destroy'])->name('delete_observacion');
        Route::post('/toggle_status', [ObservacionInterrupcionController::class, 'toggleStatus'])->name('toggle_status');
        Route::post('/get-personales', [ObservacionInterrupcionController::class, 'getPersonales'])->name('get.personales');
    });
    /******************************************************   ITINERANTE ************************************************************************/
    // Rutas para la gestión de Itinerante
    Route::group(['prefix' => 'itinerante', 'as' => 'itinerante.'], function () {
        Route::get('/index', [ItineranteController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ItineranteController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_itinerante', [ItineranteController::class, 'create'])->name('modals.md_add_itinerante');
        Route::post('/modals/md_edit_itinerante', [ItineranteController::class, 'edit'])->name('modals.md_edit_itinerante');
        Route::post('/store_itinerante', [ItineranteController::class, 'store'])->name('store_itinerante');
        Route::post('/update_itinerante', [ItineranteController::class, 'update'])->name('update_itinerante');
        Route::post('/delete_itinerante', [ItineranteController::class, 'destroy'])->name('delete_itinerante');
    });
    /******************************************************   OCUPABILIDAD ************************************************************************/

    Route::group(['prefix' => 'ocupabilidad', 'as' => 'ocupabilidad.'], function () {
        Route::get('/index', [OcupabilidadController::class, 'index'])->name('index');

        Route::get('/tablas/tb_index', [OcupabilidadController::class, 'tb_index'])->name('tablas.tb_index');
    });
    Route::get('reporte/ocupabilidad', [ReporteOcupabilidadController::class, 'index'])->name('reporte.ocupabilidad.index');
    Route::get('reporte/ocupabilidad/tablas', [ReporteOcupabilidadController::class, 'tb_index'])->name('reporte.ocupabilidad.tablas.tb_index');
    Route::get('reporte/ocupar/export', [ReporteOcupabilidadController::class, 'export_excel'])->name('reporte.ocupabilidad.export_excel');

    /******************************************************   PUNTUALIDAD ************************************************************************/

    Route::group(['prefix' => 'puntualidad', 'as' => 'puntualidad.'], function () {
        Route::get('/index', [PuntualidadController::class, 'index'])->name('index');

        Route::get('/tablas/tb_index', [PuntualidadController::class, 'tb_index'])->name('tablas.tb_index');
    });

    /******************************************************   MODULO ************************************************************************/

    // Rutas para la gestión de modulos
    Route::group(['prefix' => 'modulos', 'as' => 'modulos.'], function () {
        Route::get('/index', [ModuloController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [ModuloController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_modulo', [ModuloController::class, 'create'])->name('modals.md_add_modulo');
        Route::post('/modals/md_edit_modulo', [ModuloController::class, 'edit'])->name('modals.md_edit_modulo');
        Route::post('/store_modulo', [ModuloController::class, 'store'])->name('store_modulo');
        Route::post('/update_modulo', [ModuloController::class, 'update'])->name('update_modulo');
        Route::post('/delete_modulo', [ModuloController::class, 'destroy'])->name('delete_modulo');
    });

    /******************************************************   FORMATOS *****************************************************************************/

    Route::group(['prefix' => 'formatos', 'as' => 'formatos.'], function () {

        Route::group(['prefix' => 'evaluacion_motivacional', 'as' => 'evaluacion_motivacional.'], function () {

            Route::get('/index', [EvalMotivacionalController::class, 'index'])->name('index');
            Route::get('/tablas/tb_index', [EvalMotivacionalController::class, 'tb_index'])->name('tablas.tb_index');
            Route::post('/store_datos', [EvalMotivacionalController::class, 'store_datos'])->name('store_datos');
            Route::post('/delete_datos', [EvalMotivacionalController::class, 'delete_datos'])->name('delete_datos');

            Route::get('/reporte', [EvalMotivacionalController::class, 'reporte'])->name('reporte');
        });

        Route::group(['prefix' => 'f_02_inicio_oper', 'as' => 'f_02_inicio_oper.'], function () {
            Route::get('/index', [F02InicioPerController::class, 'index'])->name('index');
            Route::get('/formulario/{fecha}', [F02InicioPerController::class, 'formulario'])->name('formulario');
            Route::post('/store_form', [F02InicioPerController::class, 'store_form'])->name('store_form');
            Route::get('/tablas/tb_index', [F02InicioPerController::class, 'tb_index'])->name('tablas.tb_index');
        });

        Route::group(['prefix' => 'f_felicitaciones', 'as' => 'f_felicitaciones.'], function () {
            Route::get('/index', [FormFelicitacionesController::class, 'index'])->name('index');
            Route::get('/tablas/tb_index', [FormFelicitacionesController::class, 'tb_index'])->name('tablas.tb_index');
            Route::post('/modals/md_add_felicitacion', [FormFelicitacionesController::class, 'md_add_felicitacion'])->name('modals.md_add_felicitacion');
            Route::post('/modals/md_edit_felicitacion', [FormFelicitacionesController::class, 'md_edit_felicitacion'])->name('modals.md_edit_felicitacion');
            Route::post('/store', [FormFelicitacionesController::class, 'store'])->name('store');
            Route::post('/update', [FormFelicitacionesController::class, 'update'])->name('update');
            Route::post('/eliminar_archivo', [FormFelicitacionesController::class, 'eliminar_archivo'])->name('eliminar_archivo');
            Route::post('/delete', [FormFelicitacionesController::class, 'delete'])->name('delete');

            Route::get('/export_excel', [FormFelicitacionesController::class, 'export_excel'])->name('export_excel');
        });
    });

    /******************************************************   INDICADORES *****************************************************************************/

    Route::group(['prefix' => 'indicador', 'as' => 'indicador.'], function () {

        Route::group(['prefix' => 'ocupabilidad', 'as' => 'ocupabilidad.'], function () {

            Route::get('/index', [Ocupabilidad1Controller::class, 'index'])->name('index');
            Route::get('/tablas/tb_index', [Ocupabilidad1Controller::class, 'tb_index'])->name('tablas.tb_index');


            Route::get('/export_excel', [Ocupabilidad1Controller::class, 'export_excel'])->name('export_excel');
        });

        Route::group(['prefix' => 'puntualidad', 'as' => 'puntualidad.'], function () {

            Route::get('/index', [Puntualidad1Controller::class, 'index'])->name('index');
            Route::get('/tablas/tb_index', [Puntualidad1Controller::class, 'tb_index'])->name('tablas.tb_index');


            Route::get('/export_excel', [Puntualidad1Controller::class, 'export_excel'])->name('export_excel');
        });
    });


    /******************************************************   ASIGNACION ***************************************************************************/

    Route::group(['prefix' => 'asignacion', 'as' => 'asignacion.'], function () {
        Route::get('/index', [AsignacionController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [AsignacionController::class, 'tb_index'])->name('tablas.tb_index');
        Route::get('/asignacion_inventario/{idpersonal}', [AsignacionController::class, 'asignacion_inventario'])->name('asignacion_inventario');
        Route::get('/tablas/tb_asignacion', [AsignacionController::class, 'tb_asignacion'])->name('tablas.tb_asignacion');
        Route::post('/store_item', [AsignacionController::class, 'store_item'])->name('store_item');
        Route::post('/eliminar_item', [AsignacionController::class, 'eliminar_item'])->name('eliminar_item');
        Route::post('/modals/md_add_estado', [AsignacionController::class, 'md_add_estado'])->name('modals.md_add_estado');
        Route::post('/modals/md_add_observacion', [AsignacionController::class, 'md_add_observacion'])->name('modals.md_add_observacion');
        Route::post('/modals/md_acep_asesor', [AsignacionController::class, 'md_acep_asesor'])->name('modals.md_acep_asesor');
        Route::post('/store_estado', [AsignacionController::class, 'store_estado'])->name('store_estado');
        Route::post('/store_observacion', [AsignacionController::class, 'store_observacion'])->name('store_observacion');
        Route::post('/estado_borrador', [AsignacionController::class, 'estado_borrador'])->name('estado_borrador');
        Route::post('/cargar_documento_acept', [AsignacionController::class, 'cargar_documento_acept'])->name('cargar_documento_acept');

        /* RECURSOS DE ESTE GRUPO */
        Route::post('/almacen_select', [AsignacionController::class, 'almacen_select'])->name('almacen_select');
        Route::get('/pdf/borrador_pdf/{idpersonal}', [AsignacionController::class, 'borrador_pdf'])->name('pdf.borrador_pdf');
        Route::get('/pdf/orginal_pdf/{idpersonal}', [AsignacionController::class, 'orginal_pdf'])->name('pdf.orginal_pdf');
    });


    /******************************************************   ALMACEN  *****************************************************************************/

    Route::group(['prefix' => 'almacen', 'as' => 'almacen.'], function () {
        Route::get('/index', [AlmacenController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [AlmacenController::class, 'tb_index'])->name('tablas.tb_index');
        Route::get('/tablas/tb_modelos', [AlmacenController::class, 'tb_modelos'])->name('tablas.tb_modelos');
        Route::post('/modals/md_add_item', [AlmacenController::class, 'md_add_item'])->name('modals.md_add_item');
        Route::post('/modals/md_edit_item/{id}', [AlmacenController::class, 'md_edit_item'])->name('modals.md_edit_item');
        Route::post('/modals/md_add_datos', [AlmacenController::class, 'md_add_datos'])->name('modals.md_add_datos');
        Route::post('/modals/md_categorias', [AlmacenController::class, 'md_categorias'])->name('modals.md_categorias');
        Route::post('/modals/md_modelo', [AlmacenController::class, 'md_modelo'])->name('modals.md_modelo');
        Route::get('/buscar-marca', [AlmacenController::class, 'searchMarca'])->name('buscar-marca');
        Route::post('/store_datos', [AlmacenController::class, 'store_datos'])->name('store_datos');
        Route::post('/store_modelo', [AlmacenController::class, 'storeModelo'])->name('store_modelo');
        Route::post('/store_item', [AlmacenController::class, 'store_item'])->name('store_item');
        Route::post('/update_item/{id}', [AlmacenController::class, 'update_item'])->name('update_item');
        Route::post('/delete_item', [AlmacenController::class, 'delete_item'])->name('delete_item');
        Route::post('/delete_masivo', [AlmacenController::class, 'delete_masivo'])->name('delete_masivo');
        Route::delete('/eliminar_modelo/{id}', [AlmacenController::class, 'eliminar_modelo'])->name('eliminar_modelo');


        Route::get('/modelo_marca/{idmarca}', [AlmacenController::class, 'modelo_marca'])->name('modelo_marca');
    });

    /******************************************************   MANTENIMIENTO  *****************************************************************************/

    Route::group(['prefix' => 'mantenimiento', 'as' => 'mantenimiento.'], function () {

        Route::get('/index', [MantenimientoController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [MantenimientoController::class, 'tb_index'])->name('tablas.tb_index');
    });


    /******************************************************   ADMINISTRADOR ************************************************************************/

    Route::group(['prefix' => 'usuarios', 'as' => 'usuarios.'], function () {

        Route::get('/index', [UsuarioController::class, 'index'])->name('index');
        Route::get('/tablas/tb_index', [UsuarioController::class, 'tb_index'])->name('tablas.tb_index');
        Route::post('/modals/md_add_usuario', [UsuarioController::class, 'md_add_usuario'])->name('modals.md_add_usuario');
        Route::post('/modals/md_edit_usuario', [UsuarioController::class, 'md_edit_usuario'])->name('modals.md_edit_usuario');
        Route::post('/modals/md_password_usuario', [UsuarioController::class, 'md_password_usuario'])->name('modals.md_password_usuario');
        Route::post('/store_user', [UsuarioController::class, 'store_user'])->name('store_user');
        Route::post('/update_user', [UsuarioController::class, 'update_user'])->name('update_user');
        Route::post('/updatepass_user', [UsuarioController::class, 'updatepass_user'])->name('updatepass_user');
        Route::post('/delete_user', [UsuarioController::class, 'delete_user'])->name('delete_user');
    });

    /******************************************************   CONFIGURACION ************************************************************************/

    Route::group(['prefix' => 'configuracion', 'as' => 'configuracion.'], function () {

        Route::get('/nuevo_mac', [ConfiguracionController::class, 'nuevo_mac'])->name('nuevo_mac');
        Route::get('/tablas/tb_nuevo_mac', [ConfiguracionController::class, 'tb_nuevo_mac'])->name('tablas.tb_nuevo_mac');
        Route::post('/modals/md_add_mac', [ConfiguracionController::class, 'md_add_mac'])->name('modals.md_add_mac');
        Route::post('/modals/md_edit_mac', [ConfiguracionController::class, 'md_edit_mac'])->name('modals.md_edit_mac');
        Route::post('/store_mac', [ConfiguracionController::class, 'store_mac'])->name('store_mac');
        Route::post('/update_mac', [ConfiguracionController::class, 'update_mac'])->name('update_mac');
        Route::post('/delete_mac', [ConfiguracionController::class, 'delete_mac'])->name('delete_mac');

        // CONFIGURACION DE TABLAS ASOCIADAS

        Route::get('/reg_tablas/{idcentro_mac}', [ConfiguracionController::class, 'reg_tablas'])->name('reg_tablas');
        Route::post('/addEntidad', [ConfiguracionController::class, 'addEntidad'])->name('addEntidad');
        Route::post('/deleteEntidad', [ConfiguracionController::class, 'deleteEntidad'])->name('deleteEntidad');
        Route::post('/addModulo', [ConfiguracionController::class, 'addModulo'])->name('addModulo');
        Route::post('/deleteModulo', [ConfiguracionController::class, 'deleteModulo'])->name('deleteModulo');
    });

    /******************************************************   DASHBORAD ***************************************************************************/

    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::get('/index', [PanelInicioController::class, 'index'])->name('index');
        Route::get('/getion_interna', [PanelInicioController::class, 'getion_interna'])->name('getion_interna');
        Route::get('/indicadores_ans', [PanelInicioController::class, 'indicadores_ans'])->name('indicadores_ans');

    });


    /******************************************************   REPORTERIA ************************************************************************/

    /******************************************************   VERIFICACION ********************************************************************* */

    Route::get('/verificaciones/{verificacion}/change-apertura', [VerificacionController::class, 'changeApertura'])->name('verificaciones.changeApertura');
    Route::get('/verificaciones/observaciones', [VerificacionController::class, 'observaciones'])->name('verificaciones.observaciones');
    Route::get('/verificaciones', [VerificacionController::class, 'index'])->name('verificaciones.index');
    Route::get('/verificaciones/create', [VerificacionController::class, 'create'])->name('verificaciones.create');
    Route::post('/verificaciones', [VerificacionController::class, 'store'])->name('verificaciones.store');
    Route::get('/verificaciones/edit', [VerificacionController::class, 'edit'])->name('verificaciones.edit');
    Route::put('/verificaciones/{verificacion}', [VerificacionController::class, 'update'])->name('verificaciones.update');
    Route::delete('/verificaciones/{verificacion}', [VerificacionController::class, 'destroy'])->name('verificaciones.destroy');
    Route::get('/verificaciones/contingencia', [VerificacionController::class, 'contingencia'])->name('verificaciones.contingencia');
    Route::get('/verificaciones/filtrar', [VerificacionController::class, 'filtrar'])->name('verificaciones.filtrar');
    Route::get('verificaciones/{fecha}', [VerificacionController::class, 'show'])->name('verificaciones.show');
    Route::get('/verificaciones/change-apertura', [VerificacionController::class, 'changeApertura'])->name('verificaciones.changeApertura');
    Route::get('/verificaciones/get-observations', [VerificacionController::class, 'getObservations'])->name('verificaciones.getObservations');


    /******************************************************   PAGINAS DE APOYO *****************************************************************************/

    // SE ALMACENA LOS ACCESOS A LAS PAGINAS EXTERNA QUE NO ES NECESARIO LOGGIN
    Route::get('/directorio', [PagesController::class, 'directorio'])->name('directorio');
});
