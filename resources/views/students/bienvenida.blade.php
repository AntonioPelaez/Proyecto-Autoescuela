@component('mail::message')
# Bienvenido a Autoescuela AIBE, {{ $student->user->name }}

¡Gracias por registrarte en nuestra plataforma!  
Estamos encantados de tenerte con nosotros.

**Tus datos de contacto:**
- **Email:** {{ $student->user->email }}
- **Teléfono:** {{ $student->user->phone ?? 'No proporcionado' }}

Si necesitas ayuda, estamos aquí para acompañarte en todo el proceso.

¡Bienvenido a bordo!

Saludos,  
**Equipo de Autoescuela AIBE**
@endcomponent
