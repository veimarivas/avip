<x-guest-layout>
    <p class="avip-login__eyebrow">Crea tu cuenta</p>
    <h2 class="avip-login__heading">Regístrate</h2>
    <p class="avip-login__sub">Completa tus datos para empezar a usar AVIP.</p>

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

    <form method="POST" action="{{ route('register') }}" class="avip-login__form">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" type="password" name="password"
                class="form-control @error('password') is-invalid @enderror"
                required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirma la contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                required autocomplete="new-password">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary avip-login__submit">
            Crear cuenta
            <i class="ri-arrow-right-line ms-1 align-middle"></i>
        </button>

        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('login') }}" class="avip-login__help">
                ¿Ya tienes cuenta? Ingresa
            </a>
        </p>
    </form>
</x-guest-layout>
