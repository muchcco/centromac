/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index.Vista = new PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index.Controller();
        PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index.Vista.Ini(); 
    });
} catch (ex) {
}