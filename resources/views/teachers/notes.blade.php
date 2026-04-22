@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Notas del profesor: {{ $teacher->user->name }}</h2>

    <form action="{{ route('teachers.notes.save', $teacher) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Notas</label>
        <textarea name="notes" class="form-control" rows="8">{{ $teacher->notes }}</textarea>

        <button class="btn btn-primary mt-3">Guardar notas</button>
        <a href="{{ route('teachers.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </form>

</div>
@endsection
