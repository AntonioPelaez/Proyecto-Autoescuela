@component('mail::message')
# Restablecer tu contraseña

Hola {{ $user->name }},

Has solicitado restablecer tu contraseña en Autoescuela AIBE. Haz clic en el botón de abajo para continuar:

@component('mail::button', ['url' => $resetUrl])
Restablecer contraseña
@endcomponent

Este enlace expirará en 60 minutos.

Si no solicitaste restablecer tu contraseña, puedes ignorar este email.

---

**Autoescuela AIBE**
@endcomponent
