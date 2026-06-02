<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminActionLogResource;
use App\Http\Resources\Admin\DashboardResource;
use App\Models\Activity;
use App\Models\AdminActionLog;
use App\Models\Article;
use App\Models\Document;
use App\Models\Donation;
use App\Models\MediaFile;
use App\Models\Partner;
use App\Services\GoogleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $analyticsData = $this->getAnalyticsData();

        $donationsToday = Donation::query()
            ->whereDate('created_at', Carbon::today())
            ->count();

        $donationsYesterday = Donation::query()
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        $donationGrowth = $this->calculateGrowth(
            $donationsToday,
            $donationsYesterday
        );

        $data = [
            'analytics' => [
                'visitors' => $analyticsData['visitors'],
                'visitors_growth' => $analyticsData['growth'],
                'donations' => $donationsToday,
                'donations_growth' => $donationGrowth . '%',
                'articles_read' => $analyticsData['pageviews'],
                'conversion' => $analyticsData['conversion'] . '%',
                'conversion_growth' => '0%',
            ],

            'cms' => [
                'articles' => Article::query()->count(),
                'activities' => Activity::query()->count(),
                'partners' => Partner::query()->count(),
                'documents' => Document::query()->count(),
                'media' => MediaFile::query()->count(),
            ],

            'status' => [
                'api' => 'Online',
                'analytics' => ($analyticsData['available'] ?? false)
                    ? 'Online'
                    : 'Indisponível',
            ],

            'recent_activity' => $this->getRecentAdminActions($request),
        ];

        return response()->json(
            DashboardResource::make($data)->resolve($request)
        );
    }

    private function getRecentAdminActions(Request $request): array
    {
        $logs = AdminActionLog::query()
            ->latest()
            ->take(8)
            ->get();

        return AdminActionLogResource::collection($logs)
            ->resolve($request);
    }

    private function getAnalyticsData(): array
    {
        try {
            return app(GoogleAnalyticsService::class)->dashboardSummary();
        } catch (Throwable $exception) {
            report($exception);

            return $this->defaultAnalyticsData();
        }
    }

    private function defaultAnalyticsData(): array
    {
        return [
            'available' => false,
            'visitors' => 0,
            'growth' => '0',
            'pageviews' => 0,
            'conversion' => '0.0',
        ];
    }

    private function calculateGrowth($current, $previous): string
    {
        if ((int) $previous === 0) {
            return (int) $current > 0 ? '+100' : '0';
        }

        $growth = (($current - $previous) / $previous) * 100;

        return ($growth >= 0 ? '+' : '') . round($growth, 1);
    }
}
