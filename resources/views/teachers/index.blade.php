@extends('layouts.app')

@section('content')
<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <h2>Profesores</h2>
        <a href="{{ route('teachers.create') }}" class="btn btn-primary">Crear profesor</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Email</th>
                <th>Licencia</th>
                <th>Activo reservas</th>
                <th>Poblaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($teachers as $t)
            <tr>
                <td>{{ $t->id }}</td>

                <td>{{ $t->user->name }} {{ $t->user->surname1 }} {{ $t->user->surname2 }}</td>

                <td>{{ $t->user->email }}</td>

                <td>{{ $t->license_number }}</td>

                <td>
                    @if($t->is_active_for_booking)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-danger">Inactivo</span>
                    @endif
                </td>

                <td>
                    @foreach($t->town as $town)
                        <span class="badge bg-info">{{ $town->name }}</span>
                    @endforeach
                </td>

                <td>
                    <a href="{{ route('teachers.edit', $t) }}" class="btn btn-warning btn-sm">Editar</a>

                    <form action="{{ route('teachers.delete', $t) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>

                    <a href="{{ route('teachers.notes', $t) }}" class="btn btn-secondary btn-sm">
                        Notas
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
