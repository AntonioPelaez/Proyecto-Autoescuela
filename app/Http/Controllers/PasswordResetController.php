<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
    /**
     * Enviar link de restablecimiento de contraseña.
     */
   public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // Buscar el usuario
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'message' => 'No encontramos una cuenta con ese email'
        ], 404);
    }

    // Generar token
    $token = Password::broker()->createToken($user);

    // URL del frontend
    $frontendUrl = config('app.frontend_url');

    // URL final que recibe el usuario
    $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

    // Enviar email
    Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

    return response()->json([
        'message' => 'Email de recuperación enviado correctamente'
    ]);
}

    /**
     * Restablecer la contraseña.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña actualizada'])
            : response()->json(['message' => 'Error al actualizar'], 400);
    }
}
