@component('mail::message')
# Retirada de tu convocatoria

Hola {{ $student->user->name }},

Lamentamos informarte que has sido retirado de tu convocatoria. Aquí tienes los detalles:

@component('mail::panel')
**Fecha del examen:** {{\Carbon\Carbon::parse($examCall->exam_date)->format('d/m/Y')}}  
**Hora:** {{ $examCall->start_time }}  
**Ciudad:** {{ $examCall->town->name }}  
**Profesor:** {{ $examStudent->teacher->user->name }}  
**Vehículo:** {{ $examStudent->vehicle->brand }} {{ $examStudent->vehicle->model }} ({{ $examStudent->vehicle->plate_number }})  
**Estado actual:** {{ $examStudent->examResultStatus->label ?? $examStudent->examResultStatus->name }}  
**Motivo de la retirada:** {{ $motive }}
@endcomponent

@endcomponent
