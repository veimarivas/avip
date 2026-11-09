<x-guest-layout>
    <p class="avip-login__eyebrow">Nueva contraseña</p>
    <h2 class="avip-login__heading">Define tu nueva contraseña</h2>
    <p class="avip-login__sub">Elige una contraseña segura para volver a tu cuenta.</p>

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

    <form method="POST" action="{{ route('password.store') }}" class="avip-login__form">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                class="form-control @error('email') is-invalid @enderror"
                required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nueva contraseña</label>
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
            Guardar contraseña
            <i class="ri-arrow-right-line ms-1 align-middle"></i>
        </button>

        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('login') }}" class="avip-login__help">
                <i class="ri-arrow-left-line me-1"></i> Volver al ingreso
            </a>
        </p>
    </form>
</x-guest-layout>
