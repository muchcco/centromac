@extends('layouts.layout')

<style>
  #svg-map-container {
    width: 100%;
    height: 450px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 5em;
    position: relative; /* para posicionar el botón home */
  }

  #svg-map-container svg {
    width: 100%;
    height: 100%;
    pointer-events: all;
  }

  /* Hover: rellena + trazo */
  #svg-map-container svg path:hover {
    fill: #01579B !important;       /* cambio de color al pasar el ratón */
    stroke: #f50000 !important;     /* trazo rojo */
    stroke-width: 1.5 !important;
  }

  /* Región fija seleccionada */
  #svg-map-container svg path.region-selected {
    fill: #01579B !important;       /* azul oscuro */
  }

  /* Botón “home” */
  #home-btn {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 1000;
    background: #fff;
    padding: 4px 6px;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    cursor: pointer;
    font-size: 18px;
  }

  /* Tooltip */
  .region-hover-tooltip {
    position: absolute;
    pointer-events: none;
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    white-space: nowrap;
    display: none;
    z-index: 1001;
  }
</style>


@section('main')
  <div class="container-fluid">
    <div class="row mb-4">
      <!-- Tus tarjetas de conteo aquí -->
    </div>
    <div class="row">
      <!-- Tabla general -->
      <div class="col-lg-9">
        <div class="row justify-content-center mb-4">
          <!-- Asesores -->
          <div class="col-md-6 col-lg-3">
            <div class="card report-card">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-dark mb-0 fw-semibold">
                    Asesores registrados (Activos)
                  </p>
                  <h3 class="m-0">{{ $count_asesores }}</h3>
                </div>
                <div class="report-main-icon bg-light-alt"></div>
              </div>
            </div>
          </div>
          <!-- PCM -->
          <div class="col-md-6 col-lg-3">
            <div class="card report-card">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-dark mb-0 fw-semibold">
                    Personal PCM (Activo)
                  </p>
                  <h3 class="m-0">{{ $count_pcm }}</h3>
                </div>
                <div class="report-main-icon bg-light-alt"></div>
              </div>
            </div>
          </div>
          <!-- Entidades -->
          <div class="col-md-6 col-lg-3">
            <div class="card report-card">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-dark mb-0 fw-semibold">
                    Entidades participantes
                  </p>
                  <h3 class="m-0">{{ $count_entidad }}</h3>
                </div>
                <div class="report-main-icon bg-light-alt"></div>
              </div>
            </div>
          </div>
          <!-- Centros MAC -->
          <div class="col-md-6 col-lg-3">
            <div class="card report-card">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-dark mb-0 fw-semibold">
                    Centros MAC
                  </p>
                  <h3 class="m-0">{{ $count_mac }}</h3>
                </div>
                <div class="report-main-icon bg-light-alt"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">                      
                        <h4 class="card-title">Centros MAC</h4>                      
                    </div><!--end col-->
                    <div class="col-auto">               
                    </div><!--end col-->
                </div>  <!--end row-->                                  
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-white" width="50px">N°</th>
                            <th class="text-white">Nombre del Centro MAC</th>
                            <th class="text-white">Ubicación</th>
                            <th class="text-white">Fecha de apertura</th>
                            <th class="text-white">Fecha de inaguración</th>
                            <th class="text-white">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-centros-mac">
                        
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      <!-- Mapa + Home -->
      <div class="col-lg-3">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Centros MAC</h4>
            <p>Centros MAC activos por departamento</p>
          </div>
          <div class="card-body p-0">
            <div class="card-header">Mapa de Perú</div>
            <div class="card-body p-0" style="position:relative">
              <div id="svg-map-container">
                {{-- incrustamos el SVG en bruto --}}
                {!! \Illuminate\Support\Facades\File::get(public_path('imagen/map/peruHigh.svg')) !!}
                <div id="home-btn" title="Vista inicial">🏠</div>
              </div>
              <div id="tooltip" class="region-hover-tooltip"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const mapData = @json($mapData);
  let allData = [];
  let selectedEl = null;
  const svg     = document.querySelector('#svg-map-container svg');
  const tooltip = document.getElementById('tooltip');
  const homeBtn = document.getElementById('home-btn');

  // 1) Ajustar SVG para que escale perfectamente
  if (svg) {
    svg.removeAttribute('width');
    svg.removeAttribute('height');
    svg.setAttribute('viewBox', '0 0 700 700');
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.style.width  = '100%';
    svg.style.height = '100%';

    // 2) Pintar inline las regiones que tienen value > 0
    mapData.forEach(item => {
      if (item.value > 0) {
        const id   = item['hc-key'].toUpperCase();
        const path = svg.getElementById(id);
        if (path) {
          path.style.fill = '#47ade3';
        }
      }
    });
  }

  // 3) Función para renderizar la tabla
  const tbody = document.getElementById('tabla-centros-mac');
  function renderTabla(data) {
    tbody.innerHTML = '';
    if (!data.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center">No hay Centros MAC</td></tr>`;
      return;
    }
    data.forEach((c,i) => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${i+1}</td>
          <td>${c.NOMBRE_MAC}</td>
          <td>${c.NAME_DEPARTAMENTO} / ${c.NAME_PROVINCIA} / ${c.NAME_DISTRITO}</td>
          <td>${c.FECHA_APERTURA ?? '-'}</td>
          <td>${c.FECHA_INAGURACION ?? '-'}</td>
          <td>${c.FLAG === 1 ? 'Activo' : 'Inactivo'}</td>
        </tr>
      `);
    });
  }

  // 4) Carga inicial de datos completos
  fetch(`{{ route('api.data.mac') }}`)
    .then(res => res.ok ? res.json() : Promise.reject(res.statusText))
    .then(data => {
      allData = data;
      renderTabla(allData);
    })
    .catch(console.error);

  // 5) Construir lookup hc-key → { name, value }
  const lookup = {};
  mapData.forEach(item => {
    lookup[item['hc-key'].toUpperCase()] = {
      name:  item.name,
      value: item.value
    };
  });

  // 6) Interactividad sobre cada región
  if (svg) {
    svg.querySelectorAll('[id^="PE-"]').forEach(path => {
      const info = lookup[path.id];
      if (!info) return;

      path.style.cursor = 'pointer';

      // Click: filtrar tabla y marcar región seleccionada
      path.addEventListener('click', () => {
        if (selectedEl) selectedEl.classList.remove('region-selected');
        path.classList.add('region-selected');
        selectedEl = path;

        fetch(`{{ route('api.data.mac') }}?departamento=${encodeURIComponent(info.name)}`)
          .then(res => res.ok ? res.json() : Promise.reject(res.statusText))
          .then(data => renderTabla(data))
          .catch(console.error);
      });

      // Tooltip personalizado en HTML
      path.addEventListener('mousemove', e => {
        tooltip.style.display = 'block';
        tooltip.style.left    = e.pageX + 8 + 'px';
        tooltip.style.top     = e.pageY + 8 + 'px';
        tooltip.innerHTML     = `<strong>${info.name}</strong><br/>Centros MAC: ${info.value}`;
      });
      path.addEventListener('mouseout', () => {
        tooltip.style.display = 'none';
      });
    });
  }

  // 7) Botón “home”: limpia selección y recarga tabla completa
  if (homeBtn) {
    homeBtn.addEventListener('click', () => {
      if (selectedEl) selectedEl.classList.remove('region-selected');
      selectedEl = null;
      renderTabla(allData);
    });
  }
});
</script>
@endsection

