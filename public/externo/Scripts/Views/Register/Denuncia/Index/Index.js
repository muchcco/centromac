/// <summary>
/// Script de Controlador.
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Denuncia.Index');
try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Register.Denuncia.Index.Vista = new PCM.Reclamo.Presentacion.Register.Denuncia.Index.Controller();
        PCM.Reclamo.Presentacion.Register.Denuncia.Index.Vista.Ini();
    });
} catch (ex) {
}