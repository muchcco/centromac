/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaTupa.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Search.ConsultaTupa.Index.Vista = new PCM.Reclamo.Presentacion.Search.ConsultaTupa.Index.Controller();
        PCM.Reclamo.Presentacion.Search.ConsultaTupa.Index.Vista.Ini(); 
    });
} catch (ex) {
}