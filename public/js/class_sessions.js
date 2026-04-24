const apiHoursUrl       = window.apiHoursUrl;
const apiStoreUrl       = window.apiStoreUrl;
const apiDaySessionsUrl = window.apiDaySessionsUrl;
const apiCancelUrl      = window.apiCancelUrl;
const apiConfirmUrl     = window.apiConfirmUrl;

let selectedStart = null;
let selectedEnd = null;
let selectedVehicle = null;

document.addEventListener("DOMContentLoaded", () => {

    document.getElementById('teacher_id').addEventListener('change', () => {
        document.getElementById('date').value = "";
        loadAll();
    });

    document.getElementById('town_id').addEventListener('change', () => {
        document.getElementById('date').value = "";
        loadAll();
    });

    document.getElementById('date').addEventListener('change', loadAll);

    document.getElementById('reserveBtn').addEventListener('click', reserveClass);

    loadAll();
});

function loadAll() {
    loadHours();
    loadDaySessions();
}

function loadHours() {
    let townId    = document.getElementById('town_id').value;
    let teacherId = document.getElementById('teacher_id').value;
    let date      = document.getElementById('date').value;

    if (!date) {
        document.getElementById('hoursContainer').innerHTML =
            '<p>Selecciona una fecha para ver horas disponibles.</p>';
        return;
    }

    selectedStart = null;
    selectedEnd = null;
    selectedVehicle = null;
    document.getElementById('reserveBtn').disabled = true;

    document.getElementById('hoursLoader').style.display = 'block';
    document.getElementById('hoursContainer').innerHTML = '';

    fetch(`${apiHoursUrl}?town_id=${townId}&teacher_id=${teacherId}&date=${date}`)
        .then(res => res.json())
        .then(data => {

            let container = document.getElementById('hoursContainer');
            container.innerHTML = '';
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

                    selectedStart   = this.dataset.start;
                    selectedEnd     = this.dataset.end;
                    selectedVehicle = this.dataset.vehicle;

                    document.getElementById('reserveBtn').disabled = false;
                });
            });
        })
        .catch(() => {
            document.getElementById('hoursLoader').style.display = 'none';
            document.getElementById('hoursContainer').innerHTML = '<p>Error cargando horas.</p>';
        });
}

function loadDaySessions() {
    let teacherId = document.getElementById('teacher_id').value;
    let date      = document.getElementById('date').value;

    fetch(`${apiDaySessionsUrl}?teacher_id=${teacherId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            renderConfirmed(data.confirmed || []);
            renderPending(data.pending || []);
        })
        .catch(() => {
            document.getElementById('sessionsContainer').innerHTML = '<p>Error cargando reservas.</p>';
        });
}

function renderConfirmed(list) {
    let container = document.getElementById('sessionsContainer');
    container.innerHTML = '';

    if (list.length === 0) {
        container.innerHTML = '<p>No hay clases confirmadas.</p>';
        return;
    }

    let html = '<ul class="list-group">';
    list.forEach(s => {
        html += `
            <li class="list-group-item">
                <strong>${s.session_date}</strong><br>
                ${s.start_time} - ${s.end_time}<br>
                Profesor: ${s.teacher_profile?.user?.name ?? 'N/A'}<br>
                Alumno: ${s.student_profile?.user?.name ?? 'N/A'}<br>
                Vehículo: ${s.vehicle?.plate_number ?? 'N/A'}<br>
                <button class="btn btn-danger btn-sm mt-2" onclick="cancelClass(${s.id})">Cancelar</button>
            </li>
        `;
    });
    html += '</ul>';

    container.innerHTML = html;
}

function renderPending(list) {
    let container = document.getElementById('pendingContainer');
    container.innerHTML = '';

    if (list.length === 0) {
        container.innerHTML = '<p>No hay reservas pendientes.</p>';
        return;
    }

    let html = '<ul class="list-group">';
    list.forEach(s => {
        html += `
            <li class="list-group-item" data-session-id="${s.id}">
                <strong>${s.session_date}</strong><br>
                ${s.start_time} - ${s.end_time}<br>
                Profesor: ${s.teacher_profile?.user?.name ?? 'N/A'}<br>
                Alumno: ${s.student_profile?.user?.name ?? 'N/A'}<br>
                Vehículo: ${s.vehicle?.plate_number ?? 'N/A'}<br>

                <button class="btn btn-success btn-sm mt-2" onclick="confirmClass(${s.id})">Confirmar</button>
                <button class="btn btn-danger btn-sm mt-2" onclick="cancelClass(${s.id})">Cancelar</button>

                <div class="mt-2 payment-group">
                    <strong>Método de pago:</strong><br>

                    <button class="btn btn-outline-primary btn-sm payment-btn"
                            onclick="selectPayment(this)">
                        Tarjeta
                    </button>

                    <button class="btn btn-outline-secondary btn-sm payment-btn"
                            onclick="selectPayment(this)">
                        Efectivo
                    </button>

                    <button class="btn btn-outline-info btn-sm payment-btn"
                            onclick="selectPayment(this)">
                        Usar paquete
                    </button>
                </div>
            </li>
        `;
    });
    html += '</ul>';

    container.innerHTML = html;
}

function selectPayment(btn) {
    const group = btn.closest('.payment-group');

    group.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
}

function reserveClass() {
    if (!selectedStart || !selectedEnd || !selectedVehicle) {
        alert("Selecciona una hora primero");
        return;
    }

    let teacherId = document.getElementById('teacher_id').value;
    let townId    = document.getElementById('town_id').value;
    let studentId = 1;

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
        loadAll();
    });
}

function cancelClass(id) {
    fetch(apiCancelUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(() => loadAll());
}

function confirmClass(id) {
    fetch(apiConfirmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(() => loadAll());
}
