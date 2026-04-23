@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Editar profesor</h2>

    <form action="{{ route('teachers.update', $teacher) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Usuario (solo lectura) --}}
        <div class="mb-3">
            <label>Usuario</label>
            <input type="text" class="form-control"
                   value="{{ $teacher->user->name }} {{ $teacher->user->surname1 }} {{ $teacher->user->surname2 }}"
                   readonly>
        </div>

        {{-- DNI --}}
        <div class="mb-3">
            <label>DNI</label>
            <input type="text" name="dni" class="form-control" value="{{ $teacher->dni }}">
        </div>

        {{-- Licencia --}}
        <div class="mb-3">
            <label>Número de licencia</label>
            <input type="text" name="license_number" class="form-control"
                   value="{{ $teacher->license_number }}" required>
        </div>

        {{-- Activo --}}
        <div class="mb-3">
            <label>
                <input type="checkbox" name="is_active_for_booking" value="1"
                       {{ $teacher->is_active_for_booking ? 'checked' : '' }}>
                Profesor activo para reservas
            </label>
        </div>

        {{-- Poblaciones --}}
        <div class="mb-3">
            <label>Poblaciones asignadas</label>
            <div class="border p-2">
                @foreach($towns as $town)
                    <label class="d-block">
                        <input type="checkbox" name="towns[]"
                               value="{{ $town->id }}"
                               {{ $teacher->towns->contains($town->id) ? 'checked' : '' }}>
                        {{ $town->name }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Botón para notas --}}
        <a href="{{ route('teachers.notes', $teacher) }}" class="btn btn-secondary mb-3">
            Editar notas del profesor
        </a>

        <br>

        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Volver</a>
    </form>

</div>
@endsection
