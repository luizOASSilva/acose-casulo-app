<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MercadoPagoService
{
    private string $apiUrl;
    private string $accessToken;

    public function __construct()
    {
        $this->apiUrl      = config('services.mercadopago.api_url') ?? 'https://api.mercadopago.com/v1/payments';
        $this->accessToken = config('services.mercadopago.access_token') ?? '';
    }

    public function generatePix(Donation $donation): void
    {
        $idempotencyKey = "donation_{$donation->id}_" . time();
        $expiration = now()->addMinutes(30);
        $formattedDate = $expiration->format('Y-m-d\TH:i:s.vP');

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post($this->apiUrl, [
                'transaction_amount' => (float) $donation->amount,
                'description'        => 'Doação ACOSE Casulo',
                'payment_method_id'  => 'pix',
                'date_of_expiration' => $formattedDate,
                'payer'              => [
                    'email'          => $donation->email,
                    'first_name'     => $donation->name,
                    'identification' => [
                        'type'   => 'CPF',
                        'number' => preg_replace('/\D/', '', $donation->cpf ?? ''),
                    ],
                ],
                'external_reference' => (string) $donation->id,
            ]);

        if (!$response->successful()) {
            Log::error('MercadoPago PIX error', [
                'donation_id' => $donation->id,
                'status'      => $response->status(),
                'body'        => $response->json(),
            ]);

            $errorData = $response->json();
            $errorMsg = $errorData['message'] ?? 'Erro desconhecido na API';

            if (isset($errorData['cause'][0]['description'])) {
                $errorMsg .= " - " . $errorData['cause'][0]['description'];
            }

            throw new \RuntimeException("Erro ao gerar PIX: {$errorMsg}");
        }

        $payment = $response->json();
        $txData = $payment['point_of_interaction']['transaction_data'] ?? [];

        $donation->update([
            'payment_id'     => (string) ($payment['id'] ?? ''),
            'pix_copy_paste' => $txData['qr_code'] ?? null,
            'pix_qr_code'    => $txData['qr_code_base64'] ?? null,
            'pix_expires_at' => $expiration,
            'status'         => Donation::STATUS_PENDING,
            'updated_at'     => now(),
        ]);
    }

    public function handleWebhook(array $payload): bool
    {
        $paymentId = $payload['data']['id'] ?? null;

        if (!$paymentId) return false;

        $response = Http::withToken($this->accessToken)
            ->get("{$this->apiUrl}/{$paymentId}");

        if (!$response->successful()) {
            return false;
        }

        $payment = $response->json();
        $status  = $payment['status'] ?? null;
        $extRef  = $payment['external_reference'] ?? null;

        if (!$extRef) return false;

        $donation = Donation::find((int) $extRef);

        if (!$donation || $donation->status === Donation::STATUS_APPROVED) {
            return true;
        }

        $newStatus = match ($status) {
            'approved' => Donation::STATUS_APPROVED,
            'cancelled', 'refunded', 'rejected' => Donation::STATUS_CANCELLED,
            'expired' => Donation::STATUS_EXPIRED,
            default => null,
        };

        if ($newStatus) {
            $donation->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);
        }

        return true;
    }
}
