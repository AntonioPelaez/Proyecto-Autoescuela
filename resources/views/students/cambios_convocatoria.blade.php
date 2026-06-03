@component('mail::message')
# Cambios en tu convocatoria

Hola,

Se han realizado modificaciones en tu convocatoria. A continuación puedes ver los detalles actualizados:

@component('mail::panel')
**Fecha del examen:** {{ $startDate }}  
**Ciudad:** {{ $town->name }}  
**Profesor:** {{ $teacher->user->name }}  
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate }})  
**Estado actual:** {{ $examCall->examCallStatus->name }}
@endcomponent

## Estudiantes en esta convocatoria:
@foreach($students as $student)
- {{ $student->student->user->name }} {{ $student->student->user->surname1 }} {{ $student->student->user->surname2 }}
@endforeach

@if($notes)
## Anotaciones adicionales:
@component('mail::panel')
{{ $notes }}
@endcomponent
@endif

Si tienes alguna duda, puedes contactar con tu profesor o con la autoescuela.

Gracias por confiar en Autoescuela AIBE.

@endcomponent
