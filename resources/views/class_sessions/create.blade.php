@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Reservar Clase</h2>

    <div class="mb-3">
        <label>Pueblo</label>
        <select id="town_id" class="form-control">
            @foreach($towns as $town)
                <option value="{{ $town->id }}">{{ $town->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Profesor</label>
        <select id="teacher_id" class="form-control">
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Fecha</label>
        <input type="date" id="date" class="form-control">
    </div>

    <h4>Horas disponibles</h4>

    <div id="hoursLoader" class="text-center my-3" style="display:none;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div id="hoursContainer" class="mb-4"></div>

    <button class="btn btn-primary mb-4" id="reserveBtn" disabled>Reservar clase</button>

    <h4>Reservas pendientes</h4>
    <div id="pendingContainer" class="mb-4"></div>

    <h4>Clases confirmadas</h4>
    <div id="sessionsContainer"></div>

</div>
@endsection

@section('scripts')
<script>
    window.apiHoursUrl       = "{{ route('api.availability-hours') }}";
    window.apiStoreUrl       = "{{ route('api.class-sessions.store') }}";
    window.apiDaySessionsUrl = "{{ route('api.day-sessions') }}";
    window.apiCancelUrl      = "{{ route('api.class-sessions.cancel') }}";
    window.apiConfirmUrl     = "{{ route('api.class-sessions.confirm') }}";
</script>

<script src="{{ asset('js/class_sessions.js') }}"></script>
@endsection
