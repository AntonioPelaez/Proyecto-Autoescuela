@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Crear profesor</h2>

    <form action="{{ route('teachers.store') }}" method="POST">
        @csrf

        {{-- Seleccionar usuario --}}
        <h4 class="mt-3">Usuario asociado</h4>
        <div class="mb-3">
            <label>Seleccione un usuario con rol profesor</label>
            <select name="user_id" class="form-control" required>
                <option value="">Seleccione un usuario...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->name }} {{ $u->surname1 }} {{ $u->surname2 }} - {{ $u->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <h4 class="mt-4">Datos del profesor</h4>

        <div class="row">
            <div class="col-md-4">
                <label>DNI</label>
                <input type="text" name="dni" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Número de licencia</label>
                <input type="text" name="license_number" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Activo para reservas</label>
                <select name="is_active_for_booking" class="form-control">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>

        <h4 class="mt-4">Poblaciones</h4>

        <div class="row">
            @foreach($towns as $town)
                <div class="col-md-3">
                    <label>
                        <input type="checkbox" name="towns[]" value="{{ $town->id }}">
                        {{ $town->name }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-success">Guardar profesor</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Volver</a>
        </div>

    </form>

</div>
@endsection
