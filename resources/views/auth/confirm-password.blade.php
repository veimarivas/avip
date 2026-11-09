<x-guest-layout>
    <p class="avip-login__eyebrow">Área segura</p>
    <h2 class="avip-login__heading">Confirma tu contraseña</h2>
    <p class="avip-login__sub">
        Por seguridad, confirma tu contraseña antes de continuar.
    </p>

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

    <form method="POST" action="{{ route('password.confirm') }}" class="avip-login__form">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" type="password" name="password"
                class="form-control @error('password') is-invalid @enderror"
                required autofocus autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary avip-login__submit">
            Confirmar
            <i class="ri-arrow-right-line ms-1 align-middle"></i>
        </button>
    </form>
</x-guest-layout>
