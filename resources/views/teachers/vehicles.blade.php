@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Vehículos asignados a: {{ $teacher->user->name }}</h2>

    {{-- FORMULARIO PARA ASIGNAR VEHÍCULO --}}
    <form action="{{ route('teachers.vehicles.assign', $teacher) }}" method="POST" class="mb-4">
        @csrf

        <div class="row">
            <div class="col-md-6">
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

            <div class="col-md-3 d-flex align-items-end">
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
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($vehicles as $v)
                @if(in_array($v->id, $assigned))
                    <tr>
                        <td>{{ $v->plate_number }}</td>
                        <td>{{ $v->brand }}</td>
                        <td>{{ $v->model }}</td>

                        <td>
                            <form action="{{ route('teachers.vehicles.remove', [$teacher, $v]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Quitar</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('teachers.index') }}" class="btn btn-secondary mt-3">Volver</a>

</div>
@endsection
