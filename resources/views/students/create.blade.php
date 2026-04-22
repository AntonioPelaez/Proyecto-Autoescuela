@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Crear alumno</h2>

    <form action="{{ route('students.store') }}" method="POST">
        @csrf

        <h4 class="mt-3">Datos del usuario</h4>

        <div class="row">
            <div class="col-md-4">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Primer apellido</label>
                <input type="text" name="surname1" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Segundo apellido</label>
                <input type="text" name="surname2" class="form-control">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <h4 class="mt-4">Datos del alumno</h4>

        <div class="row">
            <div class="col-md-4">
                <label>DNI</label>
                <input type="text" name="dni" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Fecha de nacimiento</label>
                <input type="date" name="birth_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Teléfono</label>
                <input type="text" name="phone" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-4">Guardar alumno</button>

    </form>

</div>
@endsection
