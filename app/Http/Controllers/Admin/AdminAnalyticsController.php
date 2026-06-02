<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
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

        return response()->json(
            $analytics->fullReport($days)
        );
    }

    private function ensureAuthenticatedAdmin(): void
    {
        abort_unless(
            auth('admin')->check(),
            403
        );
    }
}

