async function exportarExcelANSTodas() {
    const boton = document.getElementById('btnAnsTodas');
    const estado = document.getElementById('estadoAnsTodas');
    const barra = document.getElementById('barraAnsTodas');
    const enlace = document.getElementById('enlaceAnsTodas');
    const config = document.getElementById('configAnsTodas');
    const bloqueo = document.getElementById('bloqueoZipANS');
    const barraZip = document.getElementById('barraZipANS');
    const detalleZip = document.getElementById('detalleZipANS');
    if (!boton || !estado || !barra || !enlace || !config || !bloqueo || !barraZip || !detalleZip) return;
    const base = config.dataset;
    const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.content || base.csrf;
    const filtros = {};
    ['fecha_inicio', 'fecha_fin', 'tipificacion', 'estado', 'revision'].forEach(nombre => {
        const campo = document.getElementById('filtro_' + nombre);
        if (campo && campo.value) filtros[nombre] = campo.value;
    });
    if (!filtros.fecha_inicio || !filtros.fecha_fin || filtros.fecha_inicio > filtros.fecha_fin) {
        Swal.fire('Atención', 'Seleccione un rango de fechas válido.', 'warning');
        return;
    }
    async function enviar(url, datos) {
        const respuesta = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenCsrf
            },
            body: JSON.stringify(datos)
        });
        const json = await respuesta.json().catch(() => ({}));
        if (!respuesta.ok) throw new Error(json.error || json.message || 'Error HTTP ' + respuesta.status);
        return json;
    }
    boton.disabled = true;
    bloqueo.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    barra.style.width = '0%';
    barra.textContent = '0%';
    barraZip.style.width = '0%';
    barraZip.textContent = '0%';
    enlace.hidden = true;
    estado.textContent = 'Buscando entidades con interrupciones...';
    detalleZip.textContent = estado.textContent;
    try {
        const inicio = await enviar(base.iniciar, filtros);
        if (!inicio.total) throw new Error('No se encontraron interrupciones en el periodo seleccionado.');
        for (let indice = 0; indice < inicio.total; indice++) {
            const resultado = await enviar(base.procesar, {token: inicio.token, indice});
            const porcentaje = Math.round(resultado.procesadas / resultado.total * 100);
            barra.style.width = porcentaje + '%';
            barra.textContent = porcentaje + '%';
            barraZip.style.width = porcentaje + '%';
            barraZip.textContent = porcentaje + '%';
            estado.textContent = `Procesadas ${resultado.procesadas} de ${resultado.total}: ${resultado.entidad}`;
            detalleZip.textContent = estado.textContent;
        }
        estado.textContent = 'Comprimiendo archivos Excel...';
        detalleZip.textContent = estado.textContent;
        const resultado = await enviar(base.finalizar, {token: inicio.token});
        barra.style.width = '100%';
        barra.textContent = '100%';
        barraZip.style.width = '100%';
        barraZip.textContent = '100%';
        estado.textContent = `Reporte generado correctamente. Total de entidades: ${inicio.total}`;
        detalleZip.textContent = 'Descarga lista.';
        enlace.href = resultado.descarga;
        enlace.hidden = false;
        window.location.assign(resultado.descarga);
    } catch (error) {
        estado.textContent = error.message;
        Swal.fire('Exportación ANS', error.message, 'error');
        console.error('Error exportación ANS:', error);
    } finally {
        bloqueo.style.display = 'none';
        document.body.style.overflow = '';
        boton.disabled = false;
    }
}