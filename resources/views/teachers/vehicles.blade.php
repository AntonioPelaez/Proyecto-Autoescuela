@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Vehículos asignados a: {{ $teacher->user->name }}</h2>

    {{-- FORMULARIO PARA ASIGNAR VEHÍCULO --}}
    <form action="{{ route('teachers.vehicles.assign', $teacher) }}" method="POST" class="mb-4">
        @csrf

        <div class="row">
            <div class="col-md-4">
                <label>Seleccionar vehículo</label>
                <select name="vehicle_id" class="form-control" required>
                    <option value="">Seleccione un vehículo...</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">
                            {{ $v->plate_number }} - {{ $v->brand }} {{ $v->model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Desde</label>
                <input type="datetime-local" name="start_at" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Hasta</label>
                <input type="datetime-local" name="end_at" class="form-control">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success">Asignar vehículo</button>
            </div>
        </div>
    </form>

    {{-- LISTADO DE VEHÍCULOS ASIGNADOS --}}
    <h4>Vehículos asignados</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Asignado el</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($teacher->vehicles as $v)
                <tr>
                    <td>{{ $v->plate_number }}</td>
                    <td>{{ $v->brand }}</td>
                    <td>{{ $v->model }}</td>

                    {{-- CAMPOS DEL PIVOT --}}
                    <td>{{ $v->pivot->starts_at }}</td>
                    <td>{{ $v->pivot->ends_at }}</td>
                    <td>{{ $v->pivot->created_at }}</td>

                    <td>
                        <form action="{{ route('teachers.vehicles.remove', [$teacher, $v]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Quitar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('teachers.index') }}" class="btn btn-secondary mt-3">Volver</a>

</div>
@endsection
