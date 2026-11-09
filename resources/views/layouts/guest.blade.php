<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Acceso · AVIP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al sistema AVIP de gestión avícola." />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('backend/assets/images/avip-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/avip-theme.css') }}?v=1.4" rel="stylesheet" type="text/css" />
</head>

<body class="avip-login">
    <main class="avip-login__shell">
        <section class="avip-login__brand" aria-label="AVIP, sistema de gestión avícola">
            <div class="avip-login__brand-inner">
                <a href="{{ url('/') }}" class="avip-login__logo">
                    <img src="{{ asset('backend/assets/images/avip-mark.svg') }}" alt="" class="avip-brand__mark">
                    <span class="avip-login__wordmark">
                        AVIP
                        <small>Gestión avícola</small>
                    </span>
                </a>

                <p class="avip-login__kicker">Sistema de gestión avícola</p>
                <h1 class="avip-login__title">Tu operación, siempre a mano.</h1>
                <p class="avip-login__lead">
                    Pedidos, clientes y cobros en un solo lugar. Ingresa para continuar donde lo dejaste.
                </p>

                <ul class="avip-login__features">
                    <li><i class="ri-shopping-basket-2-line"></i> Pedidos por localidad</li>
                    <li><i class="ri-money-dollar-circle-line"></i> Cobros y recibos</li>
                    <li><i class="ri-bar-chart-2-line"></i> Reportes al día</li>
                </ul>
            </div>

            <div class="avip-login__foot">
                <i class="ri-shield-check-line"></i>
                Acceso privado para el equipo de AVIP
            </div>
        </section>

        <section class="avip-login__panel">
            <div class="avip-login__card">
                <div class="avip-login__mobile-brand">
                    <img src="{{ asset('backend/assets/images/avip-mark.svg') }}" alt="" class="avip-brand__mark">
                    <span>AVIP</span>
                </div>

                {{ $slot }}

                <p class="avip-login__copyright">© {{ date('Y') }} AVIP · Gestión avícola</p>
            </div>
        </section>
    </main>

    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
