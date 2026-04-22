@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de Usuarios</h2>

        <!-- Botón para crear usuario -->
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            Crear Usuario
        </a>
    </div>

    <!-- Barra de búsqueda -->
    <form method="GET" action="{{ route('users.index') }}" class="mb-3">
        <input type="text" name="search" class="form-control"
               placeholder="Buscar por nombre o email"
               value="{{ $search }}">
    </form>

    <!-- Tabla -->
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fecha de Alta</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>

                <td>
                    <!-- Botón editar -->
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                        Editar
                    </a>

                    <!-- Botón eliminar -->
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    {{ $users->links() }}

</div>
@endsection
