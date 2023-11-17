<!-- Top Bar Start -->
<div class="topbar" >       
    <!-- Navbar -->
    <nav class="navbar-custom" >    
        <div class=" row align-items-center" style=" padding-top: 0.7%;"> 
            <div class="col-1" id="divicnav1">
                <ul class="list-unstyled topbar-nav mb-0">                        
                    <li>
                        <button class="nav-link button-menu-mobile">
                            <i data-feather="menu" class="align-self-center topbar-icon"></i>
                        </button>
                    </li>                         
                </ul>
            </div>
            <div class="col-10" id="divicnav2">
                <div style="text-align: center;">
                    <h3 style="color:#fff;margin-bottom: 0px;">
                        SISTEMA INTRANET CENTROS MAC - PCM
                    </h3>
                    <p style="color:#fff; font-size:15px" >(CENTRO MAC - 
                        @php
                            $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->first();

                            echo $user->NOMBRE_MAC.')';
                        @endphp                    
                    </p>
                </div>  
            </div>
            <div class="col-1" id="">
                <div class="row" style="text-align: center;">
                    <div class="col-6" style="margin:auto;">
                        {{-- <a style="color:#fff; padding-left: 1%;" href="javascript:abrirpdf('proceso');">
                            <i class="fas fa-book" aria-hidden="true" style="font-size: 150%;"></i>
                        </a> --}}
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled topbar-nav float-end mb-0">  
                            <li class="dropdown">
                                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-bs-toggle="dropdown" href="#" role="button"
                                    aria-haspopup="false" aria-expanded="false">
                                    <!--<span class="ms-1 nav-user-name hidden-sm"></span>-->
                                    <!--<img src="{{ asset('Img\profile-photos\1.png')}}" alt="profile-user" class="rounded-circle thumb-xs" />-->
                                    <!--
                                    <div class="avatar-box thumb-sm align-self-center me-2">
                                        <span class="avatar-title bg-soft-primary rounded-circle">{{auth()->user()->name[0]}}</span>
                                    </div>  -->
                                    <div class="avatar-box thumb-sm align-self-center me-2">
                                        <!--<span class="avatar-title bg-purple rounded-circle" style="background: linear-gradient(#B8F2FC, #16DFFF);"><i class="fas fa-user"></i></span>-->
                                        <span><i class="fas fa-user" style="color: #fff; font-size: 150%;"></i></span>
                                    </div>                               
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    {{-- <a class="dropdown-item" href="javascript:abrirpdf('manual_usuario');"><i data-feather="file-text" class="align-self-center icon-xs icon-dual me-1"></i> Pasos para el Registro de Documentos</a> --}}
                                    <!--<a class="dropdown-item" href="javascript:abrirpdf('manual_sistema');"><i data-feather="file-text" class="align-self-center icon-xs icon-dual me-1"></i> Manual del Sistema</a>-->
                                    <div class="dropdown-divider mb-0"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i data-feather="power" class="align-self-center icon-xs icon-dual me-1"></i> Cerrar Sesión</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                </div>
                            </li>
                        </ul><!--end topbar-nav-->
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar-->
</div>
<!-- Top Bar End -->