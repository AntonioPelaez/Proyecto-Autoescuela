@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Editar vehículo</h2>

    <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Matrícula</label>
            <input type="text" name="plate_number" class="form-control" value="{{ $vehicle->plate_number }}" required>
        </div>

        <div class="mb-3">
            <label>Marca</label>
            <input type="text" name="brand" class="form-control" value="{{ $vehicle->brand }}" required>
        </div>

        <div class="mb-3">
            <label>Modelo</label>
            <input type="text" name="model" class="form-control" value="{{ $vehicle->model }}" required>
        </div>

        <div class="mb-3">
            <label>Activo</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ $vehicle->is_active ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ !$vehicle->is_active ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Notas</label>
            <textarea name="notes" class="form-control">{{ $vehicle->notes }}</textarea>
        </div>

        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>
@endsection
