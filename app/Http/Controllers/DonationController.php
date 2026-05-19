<?php

namespace App\Http\Controllers;

use App\Http\Requests\Donation\StoreDonationRequest;
use App\Http\Resources\Admin\DonationResource;
use App\Models\Donation;
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

        return response()->json($donation);
    }

    public function status(int $id): JsonResponse
    {
        $donation = Donation::findOrFail($id);

        return response()->json([
            'status' => $donation->status,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        Log::info('webhook', $request->all());

        try {
            $this->mercadoPago->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function index(Request $request): JsonResponse
    {
        $donations = Donation::query()
            ->when(
                $request->status,
                fn($q) => $q->where('status', $request->status),
                fn($q) => $q->whereIn('status', ['pending', 'approved'])
            )
            ->latest()
            ->paginate(20);

        return DonationResource::collection($donations)->response();
    }
}
