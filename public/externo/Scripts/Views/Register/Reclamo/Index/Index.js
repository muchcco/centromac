/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Reclamo.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Register.Reclamo.Index.Vista = new PCM.Reclamo.Presentacion.Register.Reclamo.Index.Controller();
        PCM.Reclamo.Presentacion.Register.Reclamo.Index.Vista.Ini();
    });
} catch (ex) {
}