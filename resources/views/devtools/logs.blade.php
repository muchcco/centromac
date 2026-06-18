<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DevTools · Monitor</title>
    <link href="{{ asset('nuevo/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/assets/css/icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --bg:      #0d1117;
            --surface: #161b22;
            --border:  #30363d;
            --text:    #c9d1d9;
            --muted:   #8b949e;
            --green:   #3fb950;
            --yellow:  #d29922;
            --red:     #f85149;
            --blue:    #58a6ff;
            --purple:  #bc8cff;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            margin: 0; padding: 0;
            height: 100vh;
            display: flex; flex-direction: column;
        }
        /* ── Top bar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 8px 16px;
            display: flex; align-items: center; gap: 16px;
            flex-shrink: 0;
        }
        .topbar h1 { font-size: 14px; margin: 0; color: var(--blue); font-weight: 700; letter-spacing: 1px; }
        .badge-live {
            background: var(--red); color: #fff;
            border-radius: 4px; padding: 2px 7px;
            font-size: 11px; font-weight: 700; animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }
        .btn-sm-dt {
            background: var(--border); color: var(--text); border: none;
            border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px;
        }
        .btn-sm-dt:hover { background: #444c56; }
        .btn-sm-dt.active { background: var(--blue); color: #fff; }
        /* ── Layout: 3 cols top, log bottom ── */
        .grid-top {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1px;
            background: var(--border);
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .panel {
            background: var(--surface);
            padding: 12px 16px;
        }
        .panel h2 {
            font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
            color: var(--muted); margin: 0 0 10px;
            display: flex; align-items: center; gap: 6px;
        }
        .panel h2 .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .stat-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .stat-label { color: var(--muted); font-size: 12px; }
        .stat-val { font-weight: 700; font-size: 13px; }
        .val-ok    { color: var(--green); }
        .val-warn  { color: var(--yellow); }
        .val-err   { color: var(--red); }
        .val-blue  { color: var(--blue); }
        /* ── Filter bar ── */
        .filterbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 6px 16px;
            display: flex; align-items: center; gap: 8px;
            flex-shrink: 0;
        }
        .filterbar label { font-size: 11px; color: var(--muted); margin: 0; }
        .filter-btn {
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            border-radius: 3px; padding: 2px 9px;
            cursor: pointer; font-size: 11px; font-family: inherit;
        }
        .filter-btn.f-all    { border-color: var(--blue);   color: var(--blue); }
        .filter-btn.f-error  { border-color: var(--red);    color: var(--red); }
        .filter-btn.f-warn   { border-color: var(--yellow); color: var(--yellow); }
        .filter-btn.f-info   { border-color: var(--green);  color: var(--green); }
        .filter-btn.active   { color: #fff !important; }
        .filter-btn.f-all.active    { background: var(--blue); }
        .filter-btn.f-error.active  { background: var(--red); }
        .filter-btn.f-warn.active   { background: var(--yellow); }
        .filter-btn.f-info.active   { background: var(--green); }
        .search-box {
            margin-left: auto;
            background: var(--bg); border: 1px solid var(--border);
            color: var(--text); border-radius: 3px;
            padding: 3px 10px; font-size: 12px; font-family: inherit;
            width: 220px; outline: none;
        }
        .search-box::placeholder { color: var(--muted); }
        /* ── Log area ── */
        .log-wrap {
            flex: 1;
            overflow-y: auto;
            padding: 8px 16px;
            font-size: 12px;
            line-height: 1.55;
        }
        .log-entry {
            padding: 3px 0;
            border-bottom: 1px solid rgba(48,54,61,.4);
            white-space: pre-wrap;
            word-break: break-all;
            cursor: pointer;
        }
        .log-entry:hover { background: rgba(88,166,255,.05); }
        .log-entry.l-error  { color: var(--red); }
        .log-entry.l-warning{ color: var(--yellow); }
        .log-entry.l-info   { color: var(--text); }
        .log-entry.l-debug  { color: var(--muted); }
        .log-entry.expanded { background: rgba(88,166,255,.06); border-color: var(--border); }
        .log-entry .ts { color: var(--muted); font-size: 11px; }
        /* ── Failed jobs table ── */
        .failed-list { width: 100%; border-collapse: collapse; font-size: 11px; }
        .failed-list td, .failed-list th {
            padding: 3px 6px; border-bottom: 1px solid var(--border); text-align: left;
        }
        .failed-list th { color: var(--muted); font-weight: normal; }
        .failed-list tr:hover td { background: rgba(255,255,255,.03); }
        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        /* ── Misc ── */
        .pill {
            display: inline-block;
            border-radius: 3px; padding: 1px 5px;
            font-size: 10px; font-weight: 700; margin-right: 4px;
        }
        .pill-e { background: rgba(248,81,73,.2); color: var(--red); }
        .pill-w { background: rgba(210,153,34,.2); color: var(--yellow); }
        .pill-i { background: rgba(63,185,80,.15); color: var(--green); }
        .pill-d { background: rgba(139,148,158,.15); color: var(--muted); }
        .clock { font-size: 11px; color: var(--muted); }
        #autoScrollBtn.paused { background: var(--yellow); color: #000; }
        .no-data { color: var(--muted); font-size: 12px; text-align: center; padding: 12px; }
    </style>
</head>
<body>

{{-- TOP BAR --}}
<div class="topbar">
    <h1>⚙ DEVTOOLS</h1>
    <span class="badge-live">● LIVE</span>
    <span class="clock" id="clock">—</span>
    <div class="topbar-right">
        <span style="color:var(--muted);font-size:11px;">
            Logeado como: <strong style="color:var(--blue);">{{ auth()->user()->name }}</strong>
        </span>
        <button class="btn-sm-dt" id="clearBtn">Limpiar pantalla</button>
        <button class="btn-sm-dt active" id="autoScrollBtn">Auto-scroll ↓</button>
        <a href="{{ url('/') }}" class="btn-sm-dt">← Volver</a>
    </div>
</div>

{{-- STATS PANELS --}}
<div class="grid-top">
    {{-- Panel 1: Queue status --}}
    <div class="panel">
        <h2><span class="dot" style="background:var(--blue)"></span> COLAS (queue)</h2>
        <div id="queuePanel"><div class="no-data">Cargando...</div></div>
    </div>

    {{-- Panel 2: Jobs fallidos --}}
    <div class="panel">
        <h2><span class="dot" style="background:var(--red)"></span> JOBS FALLIDOS (últimas 24h)</h2>
        <div id="failedPanel"><div class="no-data">Cargando...</div></div>
    </div>

    {{-- Panel 3: Inserts recientes --}}
    <div class="panel">
        <h2><span class="dot" style="background:var(--green)"></span> IMPORTACIONES (últimas 24h)</h2>
        <div id="insertsPanel"><div class="no-data">Cargando...</div></div>
        <div style="margin-top:10px">
            <h2 style="margin-top:8px"><span class="dot" style="background:var(--yellow)"></span> JOBS FALLIDOS RECIENTES</h2>
            <div id="recentFailedPanel"><div class="no-data">Cargando...</div></div>
        </div>
    </div>
</div>

{{-- FILTER BAR --}}
<div class="filterbar">
    <label>Filtro:</label>
    <button class="filter-btn f-all  active" data-filter="all">Todos</button>
    <button class="filter-btn f-error"       data-filter="error">ERROR</button>
    <button class="filter-btn f-warn"        data-filter="warning">WARNING</button>
    <button class="filter-btn f-info"        data-filter="info">INFO</button>
    <span id="logCount" style="font-size:11px;color:var(--muted);margin-left:4px"></span>
    <input class="search-box" id="searchBox" placeholder="Buscar en logs... (token, error, ruta...)" type="text">
</div>

{{-- LOG STREAM --}}
<div class="log-wrap" id="logWrap">
    <div class="no-data" id="logEmpty">Conectando al log...</div>
</div>

<script>
(function() {
    const csrf    = document.querySelector('meta[name="csrf-token"]').content;
    const tailUrl = "{{ route('devtools.logs.tail') }}";
    const qUrl    = "{{ route('devtools.queue.status') }}";

    // ── Estado ──
    let offset     = -1;        // -1 = primera carga (últimos 80KB)
    let allEntries = [];        // array completo de entradas
    let autoScroll = true;
    let activeFilter = 'all';
    let searchTerm = '';
    let totalErrors = 0, totalWarnings = 0;

    // ── Clock ──
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            now.toLocaleDateString('es-PE') + ' ' + now.toLocaleTimeString('es-PE');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ── Auto-scroll toggle ──
    document.getElementById('autoScrollBtn').addEventListener('click', function() {
        autoScroll = !autoScroll;
        this.textContent = autoScroll ? 'Auto-scroll ↓' : 'Pausado ‖';
        this.classList.toggle('paused', !autoScroll);
    });

    // ── Clear ──
    document.getElementById('clearBtn').addEventListener('click', function() {
        allEntries = [];
        totalErrors = 0; totalWarnings = 0;
        renderLog();
    });

    // ── Filtros ──
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            renderLog();
        });
    });

    // ── Búsqueda ──
    document.getElementById('searchBox').addEventListener('input', function() {
        searchTerm = this.value.toLowerCase();
        renderLog();
    });

    // ── Render log ──
    function renderLog() {
        const wrap = document.getElementById('logWrap');
        const empty = document.getElementById('logEmpty');
        const visible = allEntries.filter(e => {
            if (activeFilter !== 'all' && e.level !== activeFilter) return false;
            if (searchTerm && !e.text.toLowerCase().includes(searchTerm)) return false;
            return true;
        });

        if (visible.length === 0) {
            if (empty) empty.style.display = '';
            wrap.querySelectorAll('.log-entry').forEach(el => el.remove());
            if (allEntries.length === 0) {
                empty.textContent = 'Sin entradas de log aún...';
            } else {
                empty.textContent = 'Sin resultados para el filtro actual.';
            }
            return;
        }
        if (empty) empty.style.display = 'none';

        // Reconstruir el DOM (solo si hay cambios)
        const existing = wrap.querySelectorAll('.log-entry');
        const existingCount = existing.length;
        const newCount = visible.length;

        // Si el filtro cambió o hay menos entradas, reconstruir todo
        if (newCount < existingCount || (activeFilter !== 'all') || searchTerm) {
            wrap.querySelectorAll('.log-entry').forEach(el => el.remove());
            visible.forEach(e => wrap.appendChild(buildEntry(e)));
        } else {
            // Solo agregar las nuevas
            const newEntries = visible.slice(existingCount);
            newEntries.forEach(e => wrap.appendChild(buildEntry(e)));
        }

        // Estadísticas
        totalErrors   = allEntries.filter(e => e.level === 'error').length;
        totalWarnings = allEntries.filter(e => e.level === 'warning').length;
        document.getElementById('logCount').textContent =
            `${allEntries.length} entradas · ${totalErrors} errores · ${totalWarnings} warnings`;

        if (autoScroll) wrap.scrollTop = wrap.scrollHeight;
    }

    function buildEntry(e) {
        const div = document.createElement('div');
        div.className = 'log-entry l-' + e.level;

        const levelMap = { error: 'pill-e', warning: 'pill-w', info: 'pill-i', debug: 'pill-d' };
        const labelMap = { error: 'ERR', warning: 'WRN', info: 'INF', debug: 'DBG' };

        // Primera línea vs stacktrace
        const lines = e.text.split('\n');
        const firstLine = lines[0];
        const rest = lines.slice(1).join('\n');

        let html = `<span class="pill ${levelMap[e.level]}">${labelMap[e.level]}</span>`;
        // Highlight tokens conocidos
        let highlighted = escHtml(firstLine)
            .replace(/(\[ProcessAsistencia\w+\])/g, '<span style="color:var(--purple)">$1</span>')
            .replace(/("token":"[^"]+)"/, '<span style="color:var(--blue)">$1</span>"')
            .replace(/(RuntimeException|Exception|Error)/g, '<span style="color:var(--red);font-weight:700">$1</span>')
            .replace(/("inserted":\d+)/, '<span style="color:var(--green)">$1</span>');
        html += highlighted;

        div.innerHTML = html;

        // Expandir stacktrace al hacer click
        if (rest.trim()) {
            div.title = 'Click para ver/ocultar stacktrace';
            div.addEventListener('click', function() {
                this.classList.toggle('expanded');
                const existing = this.querySelector('.stacktrace');
                if (existing) { existing.remove(); return; }
                const pre = document.createElement('div');
                pre.className = 'stacktrace';
                pre.style.cssText = 'margin-top:4px;padding:6px;background:#0d1117;border-radius:3px;font-size:11px;color:var(--muted);white-space:pre-wrap';
                pre.textContent = rest;
                this.appendChild(pre);
            });
        }
        return div;
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Tail polling ──
    function pollLog() {
        fetch(tailUrl + '?offset=' + offset, {
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.lines && data.lines.length > 0) {
                allEntries = allEntries.concat(data.lines);
                // Limitar a 2000 entradas en memoria
                if (allEntries.length > 2000) allEntries = allEntries.slice(-2000);
                renderLog();
            }
            offset = data.offset;
        })
        .catch(() => {});
    }

    // ── Queue status polling ──
    function pollQueue() {
        fetch(qUrl, { headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            renderQueuePanel(data);
            renderFailedPanel(data);
            renderInsertsPanel(data);
            renderRecentFailed(data);
        })
        .catch(() => {});
    }

    function renderQueuePanel(data) {
        const el = document.getElementById('queuePanel');
        let html = '';
        if (!data.pending || data.pending.length === 0) {
            html = '<div class="no-data">Sin jobs pendientes</div>';
        } else {
            data.pending.forEach(q => {
                html += `<div class="stat-row">
                    <span class="stat-label">${q.queue}</span>
                    <span class="stat-val val-blue">${q.total} pendiente(s)</span>
                </div>`;
            });
        }
        const failedTotal = data.failed ? data.failed.reduce((s,f) => s+parseInt(f.total), 0) : 0;
        html += `<div class="stat-row" style="margin-top:6px;border-top:1px solid var(--border);padding-top:6px">
            <span class="stat-label">Jobs fallidos (total)</span>
            <span class="stat-val ${failedTotal > 0 ? 'val-err' : 'val-ok'}">${failedTotal}</span>
        </div>`;
        el.innerHTML = html;
    }

    function renderFailedPanel(data) {
        const el = document.getElementById('failedPanel');
        let html = '';
        if (!data.failed || data.failed.length === 0) {
            html = '<div class="no-data" style="color:var(--green)">✓ Sin jobs fallidos</div>';
        } else {
            data.failed.forEach(f => {
                html += `<div class="stat-row">
                    <span class="stat-label">${f.queue}</span>
                    <span class="stat-val val-err">${f.total}</span>
                </div>`;
            });
        }
        el.innerHTML = html;
    }

    function renderInsertsPanel(data) {
        const el = document.getElementById('insertsPanel');
        let html = '';
        const typeMap = { 1: 'TXT', 2: 'Callao' };
        if (!data.recent_inserts || data.recent_inserts.length === 0) {
            html = '<div class="no-data">Sin importaciones en 24h</div>';
        } else {
            data.recent_inserts.forEach(r => {
                const tipo = typeMap[r.IDTIPO_ASISTENCIA] || `Tipo ${r.IDTIPO_ASISTENCIA}`;
                const ts = r.ultima ? r.ultima.substring(11,19) : '—';
                html += `<div class="stat-row">
                    <span class="stat-label">${tipo} (última: ${ts})</span>
                    <span class="stat-val val-ok">+${r.total}</span>
                </div>`;
            });
        }
        el.innerHTML = html;
    }

    function renderRecentFailed(data) {
        const el = document.getElementById('recentFailedPanel');
        if (!data.recent_failed || data.recent_failed.length === 0) {
            el.innerHTML = '<div class="no-data" style="color:var(--green)">✓ Sin errores recientes</div>';
            return;
        }
        let html = '<table class="failed-list"><tr><th>Queue</th><th>Hora</th><th>Error</th></tr>';
        data.recent_failed.forEach(f => {
            const ts = f.failed_at ? f.failed_at.substring(11,19) : '—';
            const err = f.error.replace(/</g,'&lt;').replace(/>/g,'&gt;');
            html += `<tr>
                <td style="white-space:nowrap">${f.queue}</td>
                <td style="white-space:nowrap;color:var(--muted)">${ts}</td>
                <td style="color:var(--red)">${err}</td>
            </tr>`;
        });
        html += '</table>';
        el.innerHTML = html;
    }

    // ── Iniciar ──
    pollLog();
    pollQueue();
    setInterval(pollLog,   2000);   // logs cada 2s
    setInterval(pollQueue, 8000);   // queue cada 8s
})();
</script>
</body>
</html>
