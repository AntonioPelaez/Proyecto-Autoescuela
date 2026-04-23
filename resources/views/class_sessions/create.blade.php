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

    <!-- LOADER -->
    <div id="hoursLoader" class="text-center my-3" style="display:none;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    <!-- HORAS -->
    <div id="hoursContainer" class="mb-4"></div>

    <!-- BOTÓN RESERVAR -->
    <button class="btn btn-primary mb-4" id="reserveBtn" disabled>Reservar clase</button>

    <h4>Clases reservadas ese día</h4>
    <div id="sessionsContainer"></div>

</div>
@endsection

@section('scripts')
<script>
const apiHoursUrl = "{{ url('api/availability-hours') }}";
const apiStoreUrl = "{{ url('api/class-sessions') }}";
const apiDaySessionsUrl = "{{ url('api/class-sessions/day') }}";

let selectedStart = null;
let selectedEnd = null;
let selectedVehicle = null;

document.addEventListener("DOMContentLoaded", () => {

    document.getElementById('date').addEventListener('change', loadHoursAndSessions);
    document.getElementById('teacher_id').addEventListener('change', loadHoursAndSessions);
    document.getElementById('town_id').addEventListener('change', loadHoursAndSessions);

    document.getElementById('reserveBtn').addEventListener('click', reserveClass);
});

function loadHoursAndSessions() {

    let townId = document.getElementById('town_id').value;
    let teacherId = document.getElementById('teacher_id').value;
    let date = document.getElementById('date').value;

    if (!date) return;

    selectedStart = null;
    selectedEnd = null;
    selectedVehicle = null;
    document.getElementById('reserveBtn').disabled = true;

    // MOSTRAR LOADER
    document.getElementById('hoursLoader').style.display = 'block';
    document.getElementById('hoursContainer').innerHTML = '';

    // HORAS DISPONIBLES
    fetch(`${apiHoursUrl}?town_id=${townId}&teacher_id=${teacherId}&date=${date}`)
        .then(res => res.json())
        .then(data => {

            let container = document.getElementById('hoursContainer');
            container.innerHTML = '';

            // OCULTAR LOADER
            document.getElementById('hoursLoader').style.display = 'none';

            if (!data.hours || data.hours.length === 0) {
                container.innerHTML = '<p>No hay horas disponibles.</p>';
                return;
            }

            data.hours.forEach(h => {
                let hour = h.start.split(' ')[1].substring(0,5);

                if (h.reserved) {
                    container.innerHTML += `
                        <button class="btn btn-danger m-1" disabled>
                            ${hour} (ocupada)
                        </button>
                    `;
                } else {
                    container.innerHTML += `
                        <button class="btn btn-outline-success m-1 hour-btn"
                            data-start="${h.start}"
                            data-end="${h.end}"
                            data-vehicle="${h.vehicle_id}">
                            ${hour}
                        </button>
                    `;
                }
            });

            document.querySelectorAll('.hour-btn').forEach(btn => {
                btn.addEventListener('click', function() {

                    document.querySelectorAll('.hour-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    selectedStart = this.dataset.start;
                    selectedEnd = this.dataset.end;
                    selectedVehicle = this.dataset.vehicle;

                    document.getElementById('reserveBtn').disabled = false;
                });
            });
        });

    // CLASES RESERVADAS
    fetch(`${apiDaySessionsUrl}?teacher_id=${teacherId}&date=${date}`)
        .then(res => res.json())
        .then(data => {

            let container = document.getElementById('sessionsContainer');
            container.innerHTML = '';

            if (!data.sessions || data.sessions.length === 0) {
                container.innerHTML = '<p>No hay clases reservadas.</p>';
                return;
            }

            let html = '<ul class="list-group">';
            data.sessions.forEach(s => {
                html += `
                    <li class="list-group-item">
                        ${s.start_time} - ${s.end_time}
                        — Profesor: ${s.teacher_profile?.user?.name ?? 'N/A'}
                        — Alumno: ${s.student_profile?.user?.name ?? 'N/A'}
                        — Vehículo: ${s.vehicle?.plate ?? 'N/A'}
                    </li>
                `;
            });
            html += '</ul>';

            container.innerHTML = html;
        });
}

function reserveClass() {

    if (!selectedStart || !selectedEnd || !selectedVehicle) {
        alert("Selecciona una hora primero");
        return;
    }

    let teacherId = document.getElementById('teacher_id').value;
    let townId = document.getElementById('town_id').value;
    let studentId = 1; // temporal

    fetch(apiStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            teacher_id: teacherId,
            student_id: studentId,
            town_id: townId,
            vehicle_id: selectedVehicle,
            date: selectedStart.split(' ')[0],
            start: selectedStart,
            end: selectedEnd,
            price: 25
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || data.error || 'Error');
        loadHoursAndSessions();
    });
}
</script>
@endsection
