<x-guest-layout>
    <p class="avip-login__eyebrow">Verifica tu correo</p>
    <h2 class="avip-login__heading">Revisa tu bandeja de entrada</h2>
    <p class="avip-login__sub">
        Te enviamos un enlace de verificación. Ábrelo para activar tu cuenta. Si no lo recibiste,
        podemos enviarte otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success alert-border-left mt-4 mb-0" role="alert">
            <i class="ri-checkbox-circle-line me-2 align-middle"></i>
            Enviamos un nuevo enlace a tu correo.
        </div>
    @endif

    <div class="avip-login__form d-flex flex-column gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary avip-login__submit">
                Reenviar verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light w-100">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
