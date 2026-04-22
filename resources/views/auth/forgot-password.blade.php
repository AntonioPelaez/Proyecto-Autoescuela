@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="card shadow-sm">
            <div class="card-body">

                <h2 class="text-center mb-4">Recuperar contraseña</h2>

                <form method="POST" action="/api/auth/forgot-password" id="forgotPasswordForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Enviar enlace de recuperación
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
