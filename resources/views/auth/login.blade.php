<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Ingresar · AVIP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al sistema AVIP de gestión avícola." />
    <meta name="theme-color" content="#14503c" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <link rel="icon" type="image/svg+xml" href="{{ asset('backend/assets/images/avip-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/avip-theme.css') }}?v=1.5" rel="stylesheet" type="text/css" />
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
                <h1 class="avip-login__title">Del pedido al cobro, todo en orden.</h1>
                <p class="avip-login__lead">
                    Organiza los pedidos de tus localidades, controla los cobros y consulta reportes claros,
                    sin planillas sueltas ni cuentas pendientes.
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

                <p class="avip-login__eyebrow">Bienvenido de nuevo</p>
                <h2 class="avip-login__heading">Ingresa a tu cuenta</h2>
                <p class="avip-login__sub">Usa tu usuario o correo para continuar.</p>

                @if ($errors->any())
                    <div class="alert alert-danger alert-border-left mt-4 mb-0" role="alert">
                        <i class="ri-error-warning-line me-2 align-middle"></i>
                        <strong>No pudimos iniciar sesión.</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success alert-border-left mt-4 mb-0" role="alert">
                        <i class="ri-checkbox-circle-line me-2 align-middle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="avip-login__form">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Usuario o correo</label>
                        <input type="text" id="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="tu@correo.com" autocomplete="username" autofocus required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-1">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="avip-login__password">
                            <input type="password" id="password" name="password"
                                class="form-control password-input @error('password') is-invalid @enderror"
                                placeholder="Tu contraseña" autocomplete="current-password" required>
                            <button type="button" class="avip-login__eye password-addon"
                                aria-label="Mostrar u ocultar contraseña">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="avip-login__row">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="avip-login__help" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary avip-login__submit">
                        Ingresar
                        <i class="ri-arrow-right-line ms-1 align-middle"></i>
                    </button>
                </form>

                <p class="avip-login__note">
                    <i class="ri-lock-2-line"></i>
                    Tu sesión está protegida y se cierra al terminar.
                </p>

                <p class="avip-login__copyright">© {{ date('Y') }} AVIP · Gestión avícola</p>
            </div>
        </section>
    </main>

    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('.password-addon').forEach(function(button) {
            button.addEventListener('click', function() {
                const wrapper = button.closest('.avip-login__password');
                const input = wrapper ? wrapper.querySelector('input') : null;
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                button.innerHTML = isPassword ?
                    '<i class="ri-eye-off-line"></i>' :
                    '<i class="ri-eye-line"></i>';
            });
        });
    </script>
</body>

</html>
