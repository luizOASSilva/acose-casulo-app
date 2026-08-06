<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    public function summary(
        Request $request,
        GoogleAnalyticsService $analytics
    ): JsonResponse {
        $this->ensureAuthenticatedAdmin();

        $days = (int) $request->integer('days', 30);
        $days = max(1, min($days, 365));

        return response()->json(
            $analytics->fullReport($days)
        );
    }

    private function ensureAuthenticatedAdmin(): void
    {
        abort_unless(
            auth('admin')->check(),
            403,
            'Administrador não autenticado.'
        );
    }
}
