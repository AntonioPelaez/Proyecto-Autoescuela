@component('mail::message')
# Nueva convocatoria añadida al estudiante {{ $student->user->name }}
Hola {{ $student->user->name }},
Se ha añadido una nueva convocatoria de examen práctico a tu perfil. A continuación tienes toda la información importante:
@component('mail::panel')
**Fecha:** {{ $startDate }}
**Hora:** {{ $examCall->start_time }}
**Ciudad:** {{ $town->name }}
**Profesor:** {{ $teacher->user->name }}
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate }})
**Estado de confirmación del profesor:** {{ $status }}
**Estado de confirmación del estudiante:** {{ $status_student }}
@endcomponent
@endcomponent