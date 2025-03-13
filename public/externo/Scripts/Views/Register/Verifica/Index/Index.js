/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Verifica.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Register.Verifica.Index.Vista = new PCM.Reclamo.Presentacion.Register.Verifica.Index.Controller();
        PCM.Reclamo.Presentacion.Register.Verifica.Index.Vista.Ini();
    });
} catch (ex) {
}