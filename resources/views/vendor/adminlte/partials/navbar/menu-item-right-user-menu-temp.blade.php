@php
    // Estado de la caja para la empresa del usuario
    $statusColor = 'secondary';
    $statusText = 'SIN REGISTRO';
    
    if (Auth::user() && Auth::user()->empresa_id) {
        $lastCashHistory = \App\Models\CashHistory::where('empresa_id', Auth::user()->empresa_id)
                                               ->latest()
                                               ->first();
        
        if ($lastCashHistory) {
            $statusColor = $lastCashHistory->estado === 'Apertura' ? 'success' : 'danger';
            $statusText = $lastCashHistory->estado;
        }
    }
@endphp

@if(config('adminlte.usermenu_enabled'))
<li class="nav-item dropdown user-menu">
    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if(config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}"
                 class="user-image img-circle elevation-2"
                 alt="{{ Auth::user()->name }}">
        @endif
        <span @if(config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
            @if(Auth::user()->is_admin)
                <span class="badge badge-primary ml-1">ADMIN</span>
            @endif
            @if(Auth::user()->empresa_id)
                <span class="badge badge-{{ $statusColor }} ml-1">
                    <i class="fas fa-cash-register"></i>
                    {{ $statusText }}
                </span>
            @endif
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        {{-- User menu header --}}
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if(!config('adminlte.usermenu_image')) h-auto @endif">
                @if(config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}"
                         class="img-circle elevation-2"
                         alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if(!config('adminlte.usermenu_image')) mt-0 @endif">
                    {{ Auth::user()->name }}
                    @if(Auth::user()->is_admin)
                        <small><strong>ADMINISTRADOR</strong></small>
                    @endif
                    @if(Auth::user()->empresa_id)
                        <small>
                            @php
                                $userEmpresa = \App\Models\Empresa::find(Auth::user()->empresa_id);
                            @endphp
                            {{ $userEmpresa ? $userEmpresa->nombre : 'SIN EMPRESA' }}
                        </small>
                    @endif
                    @if(config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Menu Body --}}
        @yield('usermenu_body')

        {{-- Menu Footer --}}
        <li class="user-footer">
            @if($profile_url = config('adminlte.usermenu_profile_url', false))
                <a href="{{ $profile_url }}" class="btn btn-default btn-flat">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    {{ __('adminlte::menu.profile') }}
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form-menu').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                {{ __('adminlte::adminlte.log_out') }}
            </a>
            <form id="logout-form-menu" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</li>
@else
<li class="nav-item">
    <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-menu').submit();">
        <i class="fa fa-fw fa-power-off text-red"></i>
        {{ __('adminlte::adminlte.log_out') }}
    </a>
    <form id="logout-form-menu" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</li>
@endif
