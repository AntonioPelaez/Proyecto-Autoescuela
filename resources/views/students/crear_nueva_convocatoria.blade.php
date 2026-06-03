@component('mail::message')
# Nueva convocatoria creada

Se ha creado una nueva convocatoria de examen práctico.  
Aquí tienes toda la información importante:

@component('mail::panel')
**Fecha:** {{ $date }}  
**Hora:** {{ $time }}  
**Ciudad:** {{ $town->name }}  
**Profesor:** {{ $teacher->user->name }}  
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate }})  
**Estado de la convocatoria:** {{ $status }}
@endcomponent

## Estudiantes convocados:
@foreach($students as $s)
- {{ $s->student->user->name }} {{ $s->student->user->surname1 }} {{ $s->student->user->surname2 }}
@endforeach

@if($notes)
## Notas adicionales:
@component('mail::panel')
{{ $notes }}
@endcomponent
@endif

Si tienes alguna duda, contacta con tu profesor o con la autoescuela.

Gracias por confiar en Autoescuela AIBE.
@endcomponent
