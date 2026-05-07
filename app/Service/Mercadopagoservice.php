<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    private string $apiUrl;
    private string $accessToken;

    public function __construct()
    {
        $this->apiUrl       = config('services.mercadopago.api_url');
        $this->accessToken  = config('services.mercadopago.access_token');
    }

    public function generatePix(Donation $donation): void
    {
        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'X-Idempotency-Key' => "donation-{$donation->id}-{$donation->updated_at->timestamp}",
            ])
            ->post($this->apiUrl, [
                'transaction_amount' => (float) $donation->amount,
                'description'        => 'Doação ACOSE Casulo',
                'payment_method_id'  => 'pix',
                'date_of_expiration' => now()->addMinutes(15)->toIso8601String(),
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
            throw new \RuntimeException('Erro ao gerar PIX junto ao Mercado Pago.');
        }

        $payment = $response->json();
        $txData  = $payment['point_of_interaction']['transaction_data'] ?? [];

        $donation->update([
            'payment_id'     => $payment['id'] ?? null,
            'pix_copy_paste' => $txData['qr_code']        ?? null,
            'pix_qr_code'    => $txData['qr_code_base64'] ?? null,
            'pix_expires_at' => now()->addMinutes(15),
            'status'         => Donation::STATUS_PENDING,
        ]);
    }

    public function handleWebhook(array $payload): bool
    {
        $paymentId = $payload['data']['id'] ?? null;

        if (!$paymentId) {
            return false;
        }

        $response = Http::withToken($this->accessToken)
            ->get("{$this->apiUrl}/{$paymentId}");

        if (!$response->successful()) {
            Log::warning('MercadoPago webhook: falha ao buscar pagamento', ['payment_id' => $paymentId]);
            return false;
        }

        $payment = $response->json();
        $status  = $payment['status'] ?? null;
        $extRef  = $payment['external_reference'] ?? null;

        if (!$extRef) {
            return false;
        }

        $donation = Donation::find((int) $extRef);

        if (!$donation) {
            Log::warning('MercadoPago webhook: doação não encontrada', ['external_reference' => $extRef]);
            return false;
        }

        if ($donation->isApproved()) {
            return true;
        }

        $newStatus = match ($status) {
            'approved'      => Donation::STATUS_APPROVED,
            'cancelled',
            'refunded'      => Donation::STATUS_CANCELLED,
            default         => null,
        };

        if ($newStatus) {
            $donation->update(['status' => $newStatus]);
            Log::info('Donation status updated via webhook', [
                'donation_id' => $donation->id,
                'status'      => $newStatus,
            ]);
        }

        return true;
    }
}
