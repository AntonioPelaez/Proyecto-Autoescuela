@component('mail::message')
# Resultado de tu convocatoria

Hola {{ $student->user->name }},

Tu convocatoria del día **{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}** ya tiene resultado. Aquí tienes toda la información:

@component('mail::panel')
**Ciudad:** {{ $examCall->town->name }}  
**Profesor:** {{ $teacher->user->name }}  
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})  
**Resultado:** {{ $status_result }}  
@endcomponent

@if($notes)
## Notas del profesor
@component('mail::panel')
{{ $notes }}
@endcomponent
@endif

Si tienes dudas sobre tu resultado o quieres preparar la siguiente convocatoria, contacta con tu profesor o con la autoescuela.

Gracias por confiar en Autoescuela AIBE.

@endcomponent
