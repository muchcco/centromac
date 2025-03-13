/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaEntrada.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Search.ConsultaEntrada.Index.Vista = new PCM.Reclamo.Presentacion.Search.ConsultaEntrada.Index.Controller();
        PCM.Reclamo.Presentacion.Search.ConsultaEntrada.Index.Vista.Ini(); 
    });
} catch (ex) {
}