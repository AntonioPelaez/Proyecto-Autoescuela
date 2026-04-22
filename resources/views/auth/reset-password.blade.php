@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">

        <div class="card shadow-sm">
            <div class="card-body">

                <h3 class="text-center mb-4">Restablecer contraseña</h3>

                <form method="POST" action="/api/auth/reset-password">
                    @csrf

                    <input type="hidden" name="token" value="{{ request('token') }}">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100">
                        Restablecer contraseña
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">Volver al login</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
