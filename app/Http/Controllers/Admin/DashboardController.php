<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Article;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $analyticsData = $this->getAnalyticsData();

        $donationsToday = Donation::whereDate('created_at', Carbon::today())->count();
        $donationsYesterday = Donation::whereDate('created_at', Carbon::yesterday())->count();
        $donationGrowth = $this->calculateGrowth($donationsToday, $donationsYesterday);

        return response()->json([
            'analytics' => [
                'visitors' => $analyticsData['visitors'],
                'visitors_growth' => $analyticsData['growth'],
                'donations' => $donationsToday,
                'donations_growth' => $donationGrowth . '%',
                'articles_read' => $analyticsData['pageviews'],
                'conversion' => $analyticsData['conversion'] . '%',
            ],
            'cms' => [
                'articles' => Article::count(),
                'partners' => Partner::count(),
                'activities' => \App\Models\Activity::count(),
                'documents' => \App\Models\Document::count(),
            ],
            'status' => [
                'api' => 'Online',
                'analytics' => 'Indisponível',
            ]
        ]);
    }

    private function getAnalyticsData(): array
    {
        return $this->defaultAnalyticsData();
    }

    private function defaultAnalyticsData(): array
    {
        return [
            'visitors' => 0,
            'growth' => '0',
            'pageviews' => 0,
            'conversion' => '0.0',
        ];
    }

    private function calculateGrowth($current, $previous): string
    {
        if ($previous == 0) return $current > 0 ? '+100' : '0';
        $growth = (($current - $previous) / $previous) * 100;
        return ($growth >= 0 ? '+' : '') . round($growth, 1);
    }
}
