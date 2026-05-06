<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Http\Requests\Donation\StoreDonationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /**
     * Recupera o Token de Acesso das configurações.
     */
    private function getToken(): string
    {
        return config('services.mercadopago.access_token');
    }

    /**
     * Cria uma doação e gera o pagamento PIX no Mercado Pago.
     */
    public function store(StoreDonationRequest $request): JsonResponse
    {
        $token = $this->getToken();

        // Regra de negócio para brinde
        $hasGift = $request->amount >= 100;

        // 1. Persiste a doação no banco de dados local
        $donation = Donation::create([
            'amount'       => $request->amount,
            'has_gift'     => $hasGift,
            'gift_status'  => $hasGift ? 'pending' : null,
            'name'         => $request->name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'zip_code'     => $request->zip_code,
            'street'       => $request->street,
            'number'       => $request->number,
            'complement'   => $request->complement,
            'neighborhood' => $request->neighborhood,
            'city'         => $request->city,
            'state'        => $request->state,
        ]);

        // 2. Chamada à API do Mercado Pago
        $response = Http::withToken($token)
            ->withHeaders([
                'X-Idempotency-Key' => Str::uuid()->toString(),
            ])
            ->post('https://api.mercadopago.com/v1/payments', [
                'transaction_amount' => (float) $request->amount,
                'description'        => 'Doação Acose Casulo - TESTE',
                'payment_method_id'  => 'pix',
                'payer' => [
                    'email' => 'comprador_externo_deteste@gmail.com',
                    'first_name' => 'Comprador',
                    'last_name'  => 'Teste',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => '19100000000' // CPF padrão de teste
                    ],
                ],
                'external_reference' => (string) $donation->id,
            ]);

        if (!$response->successful()) {
            $donation->delete();

            return response()->json([
                'error'   => 'Mercado Pago error',
                'details' => $response->json(),
            ], 422);
        }

        $payment = $response->json();

        $donation->update([
            'payment_id' => $payment['id'] ?? null,
            'pix_copy_paste' => $payment['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'pix_qr_code' => $payment['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
        ]);

        return response()->json([
            'id'             => $donation->id,
            'amount'         => $donation->amount,
            'pix_copy_paste' => $donation->pix_copy_paste,
            'pix_qr_code'    => $donation->pix_qr_code,
        ], 201);
    }

    /**
     * Recebe notificações de alteração de status do Mercado Pago.
     */
    public function webhook(Request $request): JsonResponse
    {
        if ($request->input('type') !== 'payment' && $request->input('action') !== 'payment.created') {
            return response()->json(['ok' => true]);
        }

        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (!$paymentId) {
            return response()->json(['ok' => true]);
        }

        $token = $this->getToken();

        $response = Http::withToken($token)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if ($response->successful()) {
            $paymentData = $response->json();

            $donation = Donation::where('payment_id', $paymentId)
                ->orWhere('id', $paymentData['external_reference'] ?? null)
                ->first();

            if ($donation) {
                $donation->update([
                    'status' => $paymentData['status'] ?? null,
                ]);

                // Se aprovado, você pode disparar eventos ou e-mails aqui
                if (($paymentData['status'] ?? null) === 'approved') {
                    // Log::info("Doação {$donation->id} aprovada.");
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
