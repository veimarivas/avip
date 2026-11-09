<x-guest-layout>
    <p class="avip-login__eyebrow">Recuperar acceso</p>
    <h2 class="avip-login__heading">¿Olvidaste tu contraseña?</h2>
    <p class="avip-login__sub">
        Escribe el correo de tu cuenta y te enviaremos un enlace para crear una nueva contraseña.
    </p>

    @if (session('status'))
        <div class="alert alert-success alert-border-left mt-4 mb-0" role="alert">
            <i class="ri-checkbox-circle-line me-2 align-middle"></i>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-border-left mt-4 mb-0" role="alert">
            <i class="ri-error-warning-line me-2 align-middle"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="avip-login__form">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="tu@correo.com" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary avip-login__submit">
            Enviar enlace
            <i class="ri-arrow-right-line ms-1 align-middle"></i>
        </button>

        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('login') }}" class="avip-login__help">
                <i class="ri-arrow-left-line me-1"></i> Volver al ingreso
            </a>
        </p>
    </form>
</x-guest-layout>
