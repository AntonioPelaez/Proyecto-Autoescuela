@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de profesores</h2>
        <a href="{{ route('teachers.create') }}" class="btn btn-success">Crear profesor</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Poblaciones</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($teachers as $t)
                <tr>
                    <td>{{ $t->user->name }} {{ $t->user->surname1 }}</td>
                    <td>{{ $t->user->email }}</td>

                    <td>
                        @foreach($t->towns ?? [] as $town)
                            <span class="badge bg-primary">{{ $town->name }}</span>
                        @endforeach
                    </td>

                    <td>
                        @if($t->is_active_for_booking)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>

                    <td class="d-flex gap-1">

                        <a href="{{ route('teachers.edit', $t) }}" class="btn btn-warning btn-sm">Editar</a>

                        <form action="{{ route('teachers.delete', $t) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </form>

                        <a href="{{ route('teachers.notes', $t) }}" class="btn btn-secondary btn-sm">Notas</a>

                        <a href="{{ route('teachers.vehicles', $t) }}" class="btn btn-dark btn-sm">Vehículos</a>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
