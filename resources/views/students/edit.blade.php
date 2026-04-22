@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Editar alumno</h2>

    <form action="{{ route('students.update', $student) }}" method="POST">
        @csrf @method('PUT')

        <h4 class="mt-3">Datos del usuario</h4>

        <div class="row">
            <div class="col-md-4">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ $student->user->name }}" required>
            </div>

            <div class="col-md-4">
                <label>Primer apellido</label>
                <input type="text" name="surname1" class="form-control" value="{{ $student->user->surname1 }}">
            </div>

            <div class="col-md-4">
                <label>Segundo apellido</label>
                <input type="text" name="surname2" class="form-control" value="{{ $student->user->surname2 }}">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $student->user->email }}" required>
            </div>

            <div class="col-md-6">
                <label>Teléfono</label>
                <input type="text" name="phone" class="form-control" value="{{ $student->user->phone }}">
            </div>
        </div>

        <h4 class="mt-4">Datos del alumno</h4>

        <div class="row">
            <div class="col-md-4">
                <label>DNI</label>
                <input type="text" name="dni" class="form-control" value="{{ $student->dni }}" required>
            </div>

            <div class="col-md-4">
                <label>Fecha de nacimiento</label>
                <input type="date" name="birth_date" class="form-control" value="{{ $student->birth_date }}" required>
            </div>
        </div>

        <button class="btn btn-primary mt-4">Actualizar alumno</button>

    </form>

</div>
@endsection
