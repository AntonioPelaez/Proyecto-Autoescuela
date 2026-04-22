@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Usuarios</h2>

        {{-- Botón crear --}}
        <a href="{{ route('users.create') }}" class="btn btn-primary">Crear usuario</a>
    </div>

    {{-- Barra de búsqueda --}}
    <form method="GET" action="{{ route('users.index') }}" class="mb-3">
        <input type="text" name="search" class="form-control"
               placeholder="Buscar por nombre, apellidos o email..."
               value="{{ $search }}">
    </form>

    {{-- Tabla --}}
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Fecha alta</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>

                {{-- Nombre completo --}}
                <td>{{ $user->name }} {{ $user->surname1 }} {{ $user->surname2 }}</td>

                <td>{{ $user->email }}</td>

                <td>{{ $user->phone ?? '-' }}</td>

                {{-- Nombre del rol --}}
                <td>{{ $user->role->name ?? 'Sin rol' }}</td>

                {{-- Activo --}}
                <td>
                    @if($user->is_active)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-danger">Inactivo</span>
                    @endif
                </td>

                {{-- Fecha alta --}}
                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>

                {{-- Botones --}}
                <td class="text-center">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">Editar</a>

                    <form action="{{ route('users.destroy', $user) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Paginación --}}
    {{ $users->links() }}

</div>
@endsection
