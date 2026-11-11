@extends('admin.dashboard')

@section('admin')
    <div class="container-fluid avip-profile-page">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                <i class="ri-user-settings-line me-2"></i>Mi perfil
            </h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Mi perfil</li>
                </ol>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>
                Tus datos se actualizaron correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>
                Tu contraseña se actualizó correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="ri-account-circle-line me-1"></i>Datos de la cuenta
                        </h5>
                        <span class="badge bg-success-subtle text-success">{{ ucfirst($user->role ?? 'Usuario') }}</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Guardar cambios
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-lock-password-line me-1"></i>Cambiar contraseña
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Contraseña actual</label>
                                <input type="password" id="current_password" name="current_password"
                                    class="form-control @if ($errors->updatePassword->has('current_password')) is-invalid @endif"
                                    autocomplete="current-password" required>
                                @if ($errors->updatePassword->has('current_password'))
                                    <div class="invalid-feedback">
                                        {{ $errors->updatePassword->first('current_password') }}
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva contraseña</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @if ($errors->updatePassword->has('password')) is-invalid @endif"
                                    autocomplete="new-password" required>
                                @if ($errors->updatePassword->has('password'))
                                    <div class="invalid-feedback">
                                        {{ $errors->updatePassword->first('password') }}
                                    </div>
                                @endif
                                <small class="text-muted">Mínimo 8 caracteres.</small>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control @if ($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                                    autocomplete="new-password" required>
                                @if ($errors->updatePassword->has('password_confirmation'))
                                    <div class="invalid-feedback">
                                        {{ $errors->updatePassword->first('password_confirmation') }}
                                    </div>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="ri-lock-line me-1"></i> Actualizar contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
