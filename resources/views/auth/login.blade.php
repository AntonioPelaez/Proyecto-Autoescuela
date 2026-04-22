@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="card shadow-sm">
            <div class="card-body">

                <h2 class="text-center mb-4">Iniciar sesión</h2>

                <form method="POST" action="/api/auth/login" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Entrar
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}">Crear cuenta</a><br>
                        <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
