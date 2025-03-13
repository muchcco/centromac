/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index.Vista = new PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index.Controller();
        PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index.Vista.Ini(); 
    });
} catch (ex) {
}