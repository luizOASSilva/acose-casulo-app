<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Http\Requests\Donation\StoreDonationRequest;
use App\Http\Requests\Donation\UpdateDonationRequest;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mercadoPago) {}

    public function store(StoreDonationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $donation = Donation::create([
                ...$data,
                'has_gift' => ($data['amount'] ?? 0) >= 100,
                'status'   => Donation::STATUS_PENDING,
            ]);

            $this->mercadoPago->generatePix($donation);

            return response()->json($donation->only([
                'id', 'amount', 'pix_copy_paste', 'pix_qr_code', 'pix_expires_at',
            ]), 201);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateDonationRequest $request, int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isApproved()) {
            return response()->json(['message' => 'Pagamento já aprovado.'], 400);
        }

        $donation->update($request->validated());

        return response()->json($donation->only([
            'id', 'name', 'email', 'cpf', 'updated_at',
        ]));
    }

    public function updatePix(Request $request, int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isApproved()) {
            return response()->json(['message' => 'Pagamento já aprovado.'], 400);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:50000'],
        ]);

        if ($donation->updated_at && $donation->updated_at->gt(now()->subSeconds(10))) {
            return response()->json(['message' => 'Aguarde alguns segundos antes de gerar um novo PIX.'], 429);
        }

        $donation->update([
            'amount'     => $data['amount'],
            'has_gift'   => $data['amount'] >= 100,
            'updated_at' => now(),
        ]);

        try {
            $this->mercadoPago->generatePix($donation);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($donation->only([
            'id', 'amount', 'pix_copy_paste', 'pix_qr_code', 'pix_expires_at',
        ]));
    }

    public function status(int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isPending() && $donation->isExpired()) {
            $donation->markExpired();
        }

        return response()->json([
            'status'         => $donation->status,
            'pix_expires_at' => $donation->pix_expires_at?->toIso8601String(),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        Log::info('MercadoPago Webhook', ['payload' => $request->all()]);

        $secret    = config('services.mercadopago.webhook_secret');
        $signature = $request->header('x-signature', '');
        $requestId = $request->header('x-request-id', '');

        if ($secret && $signature) {
            [$ts, $hash] = $this->parseSignature($signature);
            $expected = hash_hmac(
                'sha256',
                "id={$request->input('data.id')}&request-id={$requestId}&ts={$ts}",
                $secret
            );

            if (!hash_equals($expected, $hash)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        try {
            $this->mercadoPago->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('Webhook processing error', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'ok']);
    }

    private function parseSignature(string $signature): array
    {
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$k] = $v;
        }
        return [$parts['ts'] ?? '', $parts['v1'] ?? ''];
    }
}
