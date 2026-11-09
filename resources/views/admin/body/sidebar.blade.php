@php
    $clientesActivo = request()->routeIs('admin.clientes.*', 'admin.recibos.*', 'admin.reportes.*', 'admin.control-ingresos');
    $localidadesActivo = request()->routeIs('admin.localidades.*');
    $adminActivo = request()->routeIs('admin.permissions.*', 'admin.roles.*', 'admin.role-permissions.*', 'admin.users.*');
@endphp

<div id="scrollbar">
    <div class="container-fluid">
        <div id="two-column-menu"></div>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title"><span data-key="t-menu">Operación</span></li>

            <!-- Dashboard Principal -->
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                </a>
            </li>

            <!-- Clientes (con submenú) -->
            <li class="nav-item">
                <a class="nav-link menu-link {{ $clientesActivo ? 'active' : '' }}" href="#clientesCollapse"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $clientesActivo ? 'true' : 'false' }}" aria-controls="clientesCollapse">
                    <i class="ri-user-3-line"></i> <span data-key="t-clientes">Clientes y cobros</span>
                </a>
                <div class="collapse submenu {{ $clientesActivo ? 'show' : '' }}" id="clientesCollapse">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.clientes.listar') }}"
                                class="nav-link {{ request()->routeIs('admin.clientes.listar') ? 'active' : '' }}">
                                Listar clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.recibos.listar') }}"
                                class="nav-link {{ request()->routeIs('admin.recibos.*') ? 'active' : '' }}">
                                Recibos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.clientes.ranking-deudas') ? 'active' : '' }}"
                                href="{{ route('admin.clientes.ranking-deudas') }}">
                                Ranking de deudas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reportes.cobros-diarios') }}"
                                class="nav-link {{ request()->routeIs('admin.reportes.cobros-diarios') ? 'active' : '' }}">
                                Cobros por día
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.control-ingresos') }}"
                                class="nav-link {{ request()->routeIs('admin.control-ingresos') ? 'active' : '' }}">
                                Control de ingresos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reportes.graficos-ingresos') }}"
                                class="nav-link {{ request()->routeIs('admin.reportes.graficos-ingresos') ? 'active' : '' }}">
                                Reportes de ingresos
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Localidades (con submenú) -->
            <li class="nav-item">
                <a class="nav-link menu-link {{ $localidadesActivo ? 'active' : '' }}" href="#localidadesCollapse"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $localidadesActivo ? 'true' : 'false' }}" aria-controls="localidadesCollapse">
                    <i class="ri-map-pin-line"></i> <span data-key="t-localidades">Localidades</span>
                </a>
                <div class="collapse submenu {{ $localidadesActivo ? 'show' : '' }}" id="localidadesCollapse">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.localidades.listar') }}"
                                class="nav-link {{ request()->routeIs('admin.localidades.listar') ? 'active' : '' }}">
                                Listar localidades
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            @if (Auth::guard('web')->user()->can('permisos.listar'))
                <li class="menu-title"><span>Administración</span></li>

                <!-- Asesores (con submenú) -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $adminActivo ? 'active' : '' }}" href="#asesoresCollapse"
                        data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $adminActivo ? 'true' : 'false' }}" aria-controls="asesoresCollapse">
                        <i class="ri-team-line"></i> <span data-key="t-asesores">Equipo y accesos</span>
                    </a>
                    <div class="collapse submenu {{ $adminActivo ? 'show' : '' }}" id="asesoresCollapse">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.listar') }}"
                                    class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                    Permisos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.listar') }}"
                                    class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                    Roles
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.role-permissions.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.role-permissions.*') ? 'active' : '' }}">
                                    Asignar permisos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.listar') }}"
                                    class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    Usuarios
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

        </ul>
    </div>
</div>
