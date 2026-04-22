@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h2>Crear usuario</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        {{-- Nombre --}}
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- Apellidos --}}
        <div class="mb-3">
            <label>Primer apellido</label>
            <input type="text" name="surname1" class="form-control">
        </div>

        <div class="mb-3">
            <label>Segundo apellido</label>
            <input type="text" name="surname2" class="form-control">
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        {{-- Teléfono --}}
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="phone" class="form-control">
        </div>

        {{-- Rol --}}
        <div class="mb-3">
            <label>Rol</label>
            <select name="role_id" class="form-control">
                <option value="">Seleccione un rol</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Activo --}}
        <div class="mb-3">
            <label>
                <input type="checkbox" name="is_active" value="1">
                Usuario activo
            </label>
        </div>

        {{-- Contraseña con botón Ver/Ocultar --}}
        <div class="mb-3">
            <label>Contraseña</label>
            <div class="input-group">
                <input type="password" name="password" id="password_create" class="form-control" required>
                <button type="button" class="btn btn-secondary" onclick="togglePassword('password_create', this)">
                    Ver
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Volver</a>
    </form>

</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Ocultar';
    } else {
        input.type = 'password';
        btn.textContent = 'Ver';
    }
}
</script>

@endsection
