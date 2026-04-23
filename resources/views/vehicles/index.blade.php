@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de vehículos</h2>
        <a href="{{ route('vehicles.create') }}" class="btn btn-success">Crear vehículo</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($vehicles as $v)
                <tr>
                    <td>{{ $v->plate_number }}</td>
                    <td>{{ $v->brand }}</td>
                    <td>{{ $v->model }}</td>

                    <td>
                        @if($v->is_active)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>

                    <td class="d-flex gap-1">
                        <a href="{{ route('vehicles.edit', $v) }}" class="btn btn-warning btn-sm">Editar</a>

                        <form action="{{ route('vehicles.delete', $v) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
