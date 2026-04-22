@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h2>Listado de alumnos</h2>
        <a href="{{ route('students.create') }}" class="btn btn-success">Crear alumno</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Fecha nacimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($students as $s)
                <tr>
                    <td>{{ $s->user->name }} {{ $s->user->surname1 }}</td>
                    <td>{{ $s->user->email }}</td>
                    <td>{{ $s->user->phone }}</td>
                    <td>{{ $s->birth_date }}</td>

                    <td class="d-flex gap-1">
                        <a href="{{ route('students.edit', $s) }}" class="btn btn-warning btn-sm">Editar</a>

                        <form action="{{ route('students.delete', $s) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </form>

                        <a href="{{ route('students.notes', $s) }}" class="btn btn-secondary btn-sm">Notas</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
