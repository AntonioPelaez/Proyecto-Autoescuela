@component('mail::message')
# Cancelación de tu convocatoria
Hola,
Lamentamos informarte que tu convocatoria ha sido cancelada. A continuación puedes ver los detalles de la convocatoria cancelada:
@component('mail::panel')
**Fecha del examen:** {{ $startDate }}
**Ciudad:** {{ $town->name }}
**Profesor:** {{ $teacher->user->name }}
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate }})
**Estado actual:** {{ $examCall->examCallStatus->name }}
**Motivo de la cancelación:** {{ $reason }}
@endcomponent