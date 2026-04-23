@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Crear alumno</h2>

    <form action="{{ route('students.store') }}" method="POST">
        @csrf

        {{-- Seleccionar usuario --}}
        <div class="mb-3">
            <label>Usuario (rol alumno)</label>
            <select name="user_id" class="form-control" required>
                <option value="">Seleccione un usuario...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->name }} {{ $u->surname1 }} - {{ $u->email }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- DNI --}}
        <div class="mb-3">
            <label>DNI</label>
            <input type="text" name="dni" class="form-control">
        </div>

        {{-- Fecha nacimiento --}}
        <div class="mb-3">
            <label>Fecha de nacimiento</label>
            <input type="date" name="birth_date" class="form-control">
        </div>

        {{-- Notas de recogida --}}
        <div class="mb-3">
            <label>Notas de recogida</label>
            <textarea name="pickup_notes" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Guardar alumno</button>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>
@endsection
