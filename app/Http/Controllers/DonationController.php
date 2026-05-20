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
    public function __construct(
        private readonly MercadoPagoService $mercadoPago
    ) {}

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

        return response()->json([
            'ok' => true,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Donation::query();

        if ($request->has('status') && $request->status !== 'has_gift') {
            $query->where('status', $request->status);
        } elseif ($request->status === 'has_gift') {
            $query->where('has_gift', true);
        }

        $donations = $query->latest()->paginate(20);

        return response()->json([
            'data' => DonationResource::collection($donations->items()),

            'meta' => [
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
            ],

            'stats' => [
                'total_raised' => Donation::where('status', 'approved')->sum('amount'),
                'approved_count' => Donation::where('status', 'approved')->count(),
                'pending_count' => Donation::where('status', 'pending')->count(),
                'gifts_count' => Donation::where('has_gift', true)->count(),
            ],
        ]);
    }
}
