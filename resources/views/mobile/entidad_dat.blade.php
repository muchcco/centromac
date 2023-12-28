<div class="row">
    <div class="col-12">
    
      <label >Entidades disponibles para el MAC {{ $mac->NOMBRE_MAC }}</label>
      <ul class="ul-ent">
            <li class="list_empl">
                <div class="text-simpl">
                    <span class="text-list-ent col-7 text-min-2" style="display:flex; justify-content: center; align-self: center;">
                        Entidad
                    </span>            
                    <span class="type_dato col-2 text-min-2" style="display:flex; justify-content: center; align-self: center;">
                        Estado  (HOY)
                    </span>
                    <span class="col-2 text-min-2" style="display:flex; justify-content: center; align-self: center;">
                        Info.
                    </span>
                </div>
                <div class="text-simpl "> 
                </div>
            </li>
        @forelse ($entidades as $entidad)
            <li class="list_empl">
                <div class="text-simpl">
                    <span class="text-list-ent col-7">
                        <div style="display:flex; justify-content: start; align-self: center; width:100%;">{{ $entidad->ABREV_ENTIDAD }}</div> <br /> 
                        <strong class="text-min" style="display:flex; justify-content: start; align-self: center; width:100%;" >{{ $entidad->NOMBRE_ENTIDAD }}</strong>
                    </span>            
                    <span class="type_dato col-2" style="display:flex; justify-content: center; align-self: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="18" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path fill="#589f17" d="M96 80c0-26.5 21.5-48 48-48H432c26.5 0 48 21.5 48 48V384H96V80zm313 47c-9.4-9.4-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L409 161c9.4-9.4 9.4-24.6 0-33.9zM0 336c0-26.5 21.5-48 48-48H64V416H512V288h16c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336z"/></svg>
                    </span>
                    <span class="col-2" style="display:flex; justify-content: center; align-self: center;">
                        <button type="button" class="nobtn" onclick="">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path fill="#008080" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                        </button>
                    </span>
                </div>
                <div class="text-simpl"> 
                </div>
                
            </li>
        @empty
            
        @endforelse

        
      </ul>
    </div>
  </div>