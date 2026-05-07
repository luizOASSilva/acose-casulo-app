<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mercadoPago) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'       => ['required', 'numeric', 'min:1', 'max:50000'],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'cpf'          => ['required', 'string', 'min:11', 'max:14'],
            'zip_code'     => ['nullable', 'string', 'max:9'],
            'city'         => ['nullable', 'string', 'max:100'],
            'street'       => ['nullable', 'string', 'max:255'],
            'number'       => ['nullable', 'string', 'max:20'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'state'        => ['nullable', 'string', 'max:2'],
            'size'         => ['nullable', Rule::in(['PP', 'P', 'M', 'G', 'GG', '3G'])],
        ]);

        $donation = Donation::create([
            ...$data,
            'has_gift' => ($data['amount'] ?? 0) >= 100,
            'status'   => Donation::STATUS_PENDING,
        ]);

        $this->mercadoPago->generatePix($donation);

        return response()->json($donation->only([
            'id', 'amount', 'pix_copy_paste', 'pix_qr_code', 'pix_expires_at',
        ]), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isApproved()) {
            return response()->json(['message' => 'Pagamento já aprovado.'], 400);
        }

        $data = $request->validate([
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255'],
            'cpf'          => ['sometimes', 'string', 'min:11', 'max:14'],
            'zip_code'     => ['sometimes', 'nullable', 'string', 'max:9'],
            'city'         => ['sometimes', 'nullable', 'string', 'max:100'],
            'street'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'number'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'neighborhood' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'        => ['sometimes', 'nullable', 'string', 'max:2'],
            'size'         => ['sometimes', 'nullable', Rule::in(['PP', 'P', 'M', 'G', 'GG', '3G'])],
        ]);

        $donation->update($data);

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

        if ($donation->updated_at->gt(now()->subSeconds(5))) {
            return response()->json(['message' => 'Aguarde alguns segundos antes de gerar um novo PIX.'], 429);
        }

        $donation->update([
            'amount'   => $data['amount'],
            'has_gift' => $data['amount'] >= 100,
        ]);

        $this->mercadoPago->generatePix($donation);

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
        $secret    = config('services.mercadopago.webhook_secret');
        $signature = $request->header('x-signature', '');
        $requestId = $request->header('x-request-id', '');

        if ($secret) {
            [$ts, $hash] = $this->parseSignature($signature);
            $expected = hash_hmac(
                'sha256',
                "id={$request->input('data.id')}&request-id={$requestId}&ts={$ts}",
                $secret
            );

            if (!hash_equals($expected, $hash)) {
                Log::warning('MercadoPago webhook: assinatura inválida');
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
