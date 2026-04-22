@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Alumnos del profesor: {{ $teacher->user->name }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h4 class="mt-4">Asignar alumno</h4>

    <form action="{{ route('teachers.students.attach', $teacher) }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <select name="student_id" class="form-control">
                    @foreach($availableStudents as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->user->name }} {{ $s->user->surname1 }} ({{ $s->user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary">Asignar</button>
            </div>
        </div>
    </form>

    <h4 class="mt-5">Alumnos asignados</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Email</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @foreach($assignedStudents as $s)
                <tr>
                    <td>{{ $s->user->name }} {{ $s->user->surname1 }}</td>
                    <td>{{ $s->user->email }}</td>
                    <td>
                        <form action="{{ route('teachers.students.detach', [$teacher, $s]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Desasociar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
