@component('mail::message')
# Reserva confirmada

Hola {{ $student->user->name }},

Tu reserva de clase ha sido confirmada. Aquí tienes el resumen completo:

@component('mail::panel')
**Fecha:** {{ \Carbon\Carbon::parse($sessionDate)->format('d/m/Y') }}  
**Hora de inicio:** {{ $startTime }}  
**Hora de fin:** {{ $endTime }}  
**Ciudad:** {{ $session->town->name }}  
**Profesor:** {{ $teacher->user->name }}  
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})  
@endcomponent

## Información del pago
@component('mail::panel')
**Tipo de pago:** {{ ucfirst($paymentType) }}  
**Estado del pago:** {{ ucfirst($paymentStatus) }}  
**Precio:** {{ number_format($price, 2) }} €  
@endcomponent

Si necesitas modificar o cancelar tu reserva, contacta con tu profesor o con la autoescuela.

Gracias por confiar en Autoescuela AIBE.

@endcomponent
