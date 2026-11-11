@php
    $headerUser = Auth::guard('web')->user();
    $headerInitials = collect(explode(' ', trim($headerUser->name ?? 'AVIP')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center gap-2">
                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none"
                    id="topnav-hamburger-icon" aria-label="Abrir menú">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <a href="{{ route('admin.dashboard') }}" class="avip-topbar-brand d-lg-none">
                    <img src="{{ asset('backend/assets/images/avip-mark.svg') }}" alt="" class="avip-brand__mark">
                    <span>AVIP</span>
                </a>
            </div>

            <div class="d-flex align-items-center">

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode d-none"
                        aria-label="Cambiar tema">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false" aria-label="Notificaciones">
                        <i class='bx bx-bell fs-22'></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-notifications-dropdown">
                        <div class="avip-notif-head">Notificaciones</div>
                        <div class="avip-notif-empty">
                            <i class="ri-notification-off-line"></i>
                            <p>No tienes notificaciones nuevas</p>
                        </div>
                        <div class="notification-actions d-none" id="notification-actions"></div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none avip-user-chip"
                        id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <span class="avip-avatar">{{ $headerInitials }}</span>
                        <span class="avip-user-chip__meta">
                            <span class="avip-user-chip__name">{{ $headerUser->name ?? 'Usuario' }}</span>
                            <span class="avip-user-chip__role">{{ $headerUser->role === 'admin' ? 'Administrador' : ucfirst($headerUser->role ?? 'Usuario') }}</span>
                        </span>
                        <i class="ri-arrow-down-s-line text-muted ms-1 d-none d-sm-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">{{ $headerUser->name ?? 'Mi cuenta' }}</h6>
                        <p class="px-3 pb-2 mb-0 small text-muted">{{ $headerUser->email ?? '' }}</p>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="ri-user-settings-line fs-16 align-middle me-1"></i>
                            <span class="align-middle">Mi perfil</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}">
                            <i class="ri-logout-box-r-line fs-16 align-middle me-1"></i>
                            <span class="align-middle">Cerrar sesión</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
