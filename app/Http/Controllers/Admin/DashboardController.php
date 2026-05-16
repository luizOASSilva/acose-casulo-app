<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Article;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
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
                'analytics' => 'Ativo',
            ]
        ]);
    }

    private function getAnalyticsData(): array
    {
        $client = new BetaAnalyticsDataClient(['credentials' => storage_path('app/private/google-analytics.json')]);
        $propertyId = env('GA4_PROPERTY_ID');

        $response = $client->runReport([
            'property' => 'properties/' . $propertyId,
            'dateRanges' => [
                new DateRange(['start_date' => 'yesterday', 'end_date' => 'today']),
            ],
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'conversions']),
            ],
        ]);

        $rows = $response->getRows();
        $today = $rows[0]->getMetricValues();
        $yesterday = isset($rows[1]) ? $rows[1]->getMetricValues() : $today;

        return [
            'visitors' => $today[0]->getValue(),
            'growth' => $this->calculateGrowth($today[0]->getValue(), $yesterday[0]->getValue()) . '%',
            'pageviews' => $today[1]->getValue(),
            'conversion' => number_format($today[2]->getValue(), 1),
        ];
    }

    private function calculateGrowth($current, $previous): string
    {
        if ($previous == 0) return $current > 0 ? '+100' : '0';
        $growth = (($current - $previous) / $previous) * 100;
        return ($growth >= 0 ? '+' : '') . round($growth, 1);
    }
}
