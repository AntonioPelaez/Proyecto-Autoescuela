@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Notas del alumno: {{ $student->user->name }}</h2>

    <form action="{{ route('students.notes.save', $student) }}" method="POST">
        @csrf @method('PUT')

        <label>Notas de recogida</label>
        <textarea name="pickup_notes" class="form-control" rows="8">{{ $student->pickup_notes }}</textarea>

        <button class="btn btn-primary mt-3">Guardar notas</button>
        <a href="{{ route('students.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </form>

</div>
@endsection
