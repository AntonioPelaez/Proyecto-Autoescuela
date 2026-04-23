@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Crear vehículo</h2>

    <form action="{{ route('vehicles.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Matrícula</label>
            <input type="text" name="plate_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Marca</label>
            <input type="text" name="brand" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Modelo</label>
            <input type="text" name="model" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Activo</label>
            <select name="is_active" class="form-control">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Notas</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Guardar vehículo</button>
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>
@endsection
