<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Http\Requests\Donation\StoreDonationRequest;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mercadoPago) {}

    public function store(StoreDonationRequest $request): JsonResponse
    {
        $donation = Donation::create([
            ...$request->validated(),
            'status' => Donation::STATUS_PENDING,
        ]);

        $this->mercadoPago->generatePix($donation);

        return response()->json([
            'id' => $donation->id,
            'amount' => $donation->amount,
            'pix_copy_paste' => $donation->pix_copy_paste,
            'pix_qr_code' => $donation->pix_qr_code,
            'pix_expires_at' => $donation->pix_expires_at,
            'status' => $donation->status,
        ], 201);
    }

    public function status(int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isPending() && $donation->isExpired()) {
            $donation->markExpired();
        }

        return response()->json([
            'status' => $donation->status,
            'expires_at' => $donation->pix_expires_at,
            'amount' => $donation->amount,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        Log::info('webhook_received', $request->all());

        try {
            $paymentId = data_get($request->all(), 'data.id');

            if (!$paymentId) {
                return response()->json(['ok' => true]);
            }

            $this->mercadoPago->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('webhook_error', [
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
