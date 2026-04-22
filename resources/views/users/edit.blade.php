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

    <h2>Editar usuario</h2>

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $user->name }}" required>
        </div>

        {{-- Apellidos --}}
        <div class="mb-3">
            <label>Primer apellido</label>
            <input type="text" name="surname1" class="form-control"
                   value="{{ $user->surname1 }}">
        </div>

        <div class="mb-3">
            <label>Segundo apellido</label>
            <input type="text" name="surname2" class="form-control"
                   value="{{ $user->surname2 }}">
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ $user->email }}" required>
        </div>

        {{-- Teléfono --}}
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="phone" class="form-control"
                   value="{{ $user->phone }}">
        </div>

        {{-- Rol --}}
        <div class="mb-3">
            <label>Rol</label>
            <select name="role_id" class="form-control">
                <option value="">Seleccione un rol</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}"
                        {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Activo --}}
        <div class="mb-3">
            <label>
                <input type="checkbox" name="is_active" value="1"
                       {{ $user->is_active ? 'checked' : '' }}>
                Usuario activo
            </label>
        </div>

        {{-- Nueva contraseña --}}
        <div class="mb-3">
            <label>Nueva contraseña (opcional)</label>
            <div class="input-group">
                <input type="password" name="password" id="password_edit" class="form-control">
                <button type="button" class="btn btn-secondary" onclick="togglePassword('password_edit', this)">
                    Ver
                </button>
            </div>
        </div>

        {{-- Fechas --}}
        <div class="mb-3">
            <label>Fecha de alta</label>
            <input type="text" class="form-control" value="{{ $user->created_at }}" readonly>
        </div>

        <div class="mb-3">
            <label>Última actualización</label>
            <input type="text" class="form-control" value="{{ $user->updated_at }}" readonly>
        </div>

        <div class="mb-3">
            <label>Último inicio de sesión</label>
            <input type="text" class="form-control" value="{{ $user->last_login_at }}" readonly>
        </div>

        {{-- Remember token --}}
        <div class="mb-3">
            <label>Remember token</label>
            <input type="text" class="form-control" value="{{ $user->remember_token }}" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
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
