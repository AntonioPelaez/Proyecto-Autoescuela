@component('mail::message')
# Confirmación de asistencia del estudiante

Hola {{ $examStudent->teacher->user->name }},

El estudiante **{{ $student->user->name }} {{ $student->user->surname1 }} {{ $student->user->surname2 }}** ha confirmado su asistencia a la convocatoria.

@component('mail::panel')
**Fecha:** {{ $examCall->exam_date }}  
**Hora:** {{ $examCall->start_time }}  
**Ciudad:** {{ $examCall->town->name }}  
**Vehículo asignado:** {{ $examStudent->vehicle->brand }} {{ $examStudent->vehicle->model }} ({{ $examStudent->vehicle->plate }})  
**Estado del estudiante:** Confirmado  
@endcomponent

Por favor, revisa la lista de estudiantes confirmados en tu panel de profesor.

Gracias por tu trabajo.

@endcomponent
