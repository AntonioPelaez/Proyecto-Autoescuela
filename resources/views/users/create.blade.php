@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Crear Usuario</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <!-- Nombre -->
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <!-- Email verificado -->
        <div class="mb-3">
            <label class="form-label">Email Verificado (opcional)</label>
            <input type="datetime-local" name="email_verified_at" class="form-control">
        </div>

        <!-- Token -->
        <div class="mb-3">
            <label class="form-label">Remember Token (opcional)</label>
            <input type="text" name="remember_token" class="form-control">
        </div>

        <!-- Botón corregido -->
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection

