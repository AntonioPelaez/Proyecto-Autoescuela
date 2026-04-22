@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Usuario</h2>

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Nombre -->
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $user->name }}" required>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="email" class="form-control"
                   value="{{ $user->email }}" required>
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <label class="form-label">Nueva Contraseña (opcional)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <!-- Email verificado -->
        <div class="mb-3">
            <label class="form-label">Email Verificado</label>
            <input type="datetime-local" name="email_verified_at"
                   class="form-control"
                   value="{{ $user->email_verified_at }}">
        </div>

        <!-- Token -->
        <div class="mb-3">
            <label class="form-label">Remember Token</label>
            <input type="text" name="remember_token" class="form-control"
                   value="{{ $user->remember_token }}">
        </div>

        <button class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
