<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentIntent;
use App\Models\ClassSession;

class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Crear intención de pago (tarjeta)
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $request->validate([
            'class_session_id' => 'required|integer|exists:class_sessions,id',
            'amount'           => 'required|numeric|min:0',
            'currency'         => 'required|string|max:10',
        ]);

        $intent = PaymentIntent::create([
            'class_session_id'   => $request->class_session_id,
            'provider'           => 'card',
            'provider_reference' => strtoupper(Str::random(12)),
            'amount'             => $request->amount,
            'currency'           => $request->currency,
            'status'             => 'pending',
        ]);

        return response()->json([
            'message' => 'Intento de pago creado',
            'intent'  => $intent
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmar pago con tarjeta (simulación)
    |--------------------------------------------------------------------------
    */
    public function confirm(int $id)
    {
        return DB::transaction(function () use ($id) {

            $intent = PaymentIntent::findOrFail($id);

            $intent->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $intent->classSession->update([
                'payment_status' => 'paid'
            ]);

            return response()->json([
                'message' => 'Pago confirmado',
                'intent'  => $intent
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar pago como fallido
    |--------------------------------------------------------------------------
    */
    public function fail(int $id)
    {
        $intent = PaymentIntent::findOrFail($id);

        $intent->update([
            'status' => 'failed'
        ]);

        return response()->json([
            'message' => 'Pago marcado como fallido',
            'intent'  => $intent
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar estado del pago
    |--------------------------------------------------------------------------
    */
    public function show(int $id)
    {
        $intent = PaymentIntent::with([
            'classSession.studentProfile.user',
            'classSession.teacherProfile.user',
            'classSession.vehicle',
            'classSession.town'
        ])->findOrFail($id);

        $session = $intent->classSession;

        return response()->json([
            'payment_intent' => $intent,
            'class_session'  => [
                'id'              => $session->id,
                'booking_reference' => $session->booking_reference,
                'date'            => $session->session_date,
                'start_time'      => $session->start_time,
                'end_time'        => $session->end_time,
                'slot_starts_at'  => $session->slot_starts_at,
                'slot_ends_at'    => $session->slot_ends_at,
                'status'          => $session->status,
                'payment_status'  => $session->payment_status,
                'price'           => $session->price,
                'student_comments' => $session->student_comments,
                'internal_notes'  => $session->internal_notes,

                'student' => [
                    'id'       => $session->studentProfile->id,
                    'name'     => $session->studentProfile->user->name,
                    'surname1' => $session->studentProfile->user->surname1,
                    'surname2' => $session->studentProfile->user->surname2,
                ],

                'teacher' => [
                    'id'       => $session->teacherProfile->id,
                    'name'     => $session->teacherProfile->user->name,
                    'surname1' => $session->teacherProfile->user->surname1,
                    'surname2' => $session->teacherProfile->user->surname2,
                ],

                'vehicle' => [
                    'id'     => $session->vehicle->id,
                    'brand'  => $session->vehicle->brand,
                    'model'  => $session->vehicle->model,
                    'plate'  => $session->vehicle->plate_number,
                ],

                'town' => [
                    'id'   => $session->town->id,
                    'name' => $session->town->name,
                ]
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pago con monedero
    |--------------------------------------------------------------------------
    */
    public function payWithWallet(Request $request)
    {
        $request->validate([
            'class_session_id' => 'required|exists:class_sessions,id',
        ]);

        $session = ClassSession::with('studentProfile.wallet')
            ->findOrFail($request->class_session_id);

        $wallet = $session->studentProfile->wallet;

        if ($wallet->balance < $session->price) {
            return response()->json([
                'message' => 'Saldo insuficiente en el monedero',
                'balance' => $wallet->balance
            ], 400);
        }

        return DB::transaction(function () use ($wallet, $session) {

            $wallet->update([
                'balance' => $wallet->balance - $session->price
            ]);

            $wallet->transactions()->create([
                'type' => 'payment',
                'amount' => -$session->price,
                'description' => 'Pago de clase '.$session->id
            ]);

            $session->update([
                'payment_status' => 'paid'
            ]);

            return response()->json([
                'message' => 'Pago realizado con monedero',
                'class_session_id' => $session->id,
                'amount' => $session->price,
                'balance' => $wallet->balance
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Recargar monedero
    |--------------------------------------------------------------------------
    */
    public function recharge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $wallet = $request->user()->studentProfile->wallet;

        return DB::transaction(function () use ($wallet, $request) {

            $wallet->update([
                'balance' => $wallet->balance + $request->amount
            ]);

            $wallet->transactions()->create([
                'type' => 'recharge',
                'amount' => $request->amount,
                'description' => 'Recarga de monedero'
            ]);

            return response()->json([
                'message' => 'Monedero recargado',
                'balance' => $wallet->balance
            ]);
        });
    }
}
