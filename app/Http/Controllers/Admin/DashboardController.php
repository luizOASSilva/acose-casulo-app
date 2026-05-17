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
                'analytics' => 'Ativo',
            ]
        ]);
    }

    private function getAnalyticsData(): array
    {
        try {
            $client = new \Google\Analytics\Data\V1beta\BetaAnalyticsDataClient([
                'credentials' => '/etc/secrets/google-analytics.json'
            ]);

            $propertyId = env('GA4_PROPERTY_ID');

            $response = $client->runReport([
                'property' => 'properties/' . $propertyId,
                'dateRanges' => [
                    new \Google\Analytics\Data\V1beta\DateRange(['start_date' => 'today', 'end_date' => 'today']),
                    new \Google\Analytics\Data\V1beta\DateRange(['start_date' => 'yesterday', 'end_date' => 'yesterday']),
                ],
                'metrics' => [
                    new \Google\Analytics\Data\V1beta\Metric(['name' => 'activeUsers']),
                    new \Google\Analytics\Data\V1beta\Metric(['name' => 'screenPageViews']),
                    new \Google\Analytics\Data\V1beta\Metric(['name' => 'conversions']),
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
        } catch (\Throwable $e) {
            return [
                'visitors' => 0,
                'growth' => '0%',
                'pageviews' => 0,
                'conversion' => '0.0',
            ];
        }
    }

    private function calculateGrowth($current, $previous): string
    {
        if ($previous == 0) return $current > 0 ? '+100' : '0';
        $growth = (($current - $previous) / $previous) * 100;
        return ($growth >= 0 ? '+' : '') . round($growth, 1);
    }
}
