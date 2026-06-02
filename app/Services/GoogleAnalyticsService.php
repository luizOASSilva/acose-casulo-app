<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\Credentials\UserRefreshCredentials;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GoogleAnalyticsService
{
    private string $property;

    private string $credentialsPath;

    private int $cacheSeconds;

    public function __construct()
    {
        $propertyId = (string) config('google-analytics.property_id');

        $this->property = 'properties/' . $propertyId;

        $this->credentialsPath = base_path(
            (string) config('google-analytics.credentials_path')
        );

        $this->cacheSeconds = (int) config('google-analytics.cache_seconds', 600);
    }

    public function dashboardSummary(): array
    {
        return Cache::remember(
            'google_analytics.dashboard_summary',
            $this->cacheSeconds,
            function () {
                try {
                    $today = $this->metricsForPeriod('today', 'today');
                    $yesterday = $this->metricsForPeriod('yesterday', 'yesterday');

                    return [
                        'available' => true,
                        'visitors' => $today['activeUsers'],
                        'growth' => $this->calculateGrowth(
                            $today['activeUsers'],
                            $yesterday['activeUsers']
                        ),
                        'pageviews' => $today['screenPageViews'],
                        'conversion' => $this->calculateConversion(
                            $today['conversions'],
                            $today['activeUsers']
                        ),
                    ];
                } catch (Throwable $exception) {
                    report($exception);

                    return [
                        'available' => false,
                        'visitors' => 0,
                        'growth' => '0',
                        'pageviews' => 0,
                        'conversion' => '0.0',
                    ];
                }
            }
        );
    }

    public function fullReport(int $days = 30): array
    {
        $days = max(1, min($days, 365));

        return Cache::remember(
            "google_analytics.full_report.{$days}",
            $this->cacheSeconds,
            function () use ($days) {
                try {
                    $startDate = now()->subDays($days - 1)->toDateString();
                    $endDate = 'today';

                    return [
                        'available' => true,
                        'period' => [
                            'days' => $days,
                            'start_date' => $startDate,
                            'end_date' => now()->toDateString(),
                        ],
                        'overview' => $this->overview($startDate, $endDate),
                        'realtime' => $this->realtime(),
                        'timeseries' => $this->timeseries($startDate, $endDate),
                        'top_pages' => $this->topPages($startDate, $endDate),
                        'sources' => $this->sources($startDate, $endDate),
                        'devices' => $this->devices($startDate, $endDate),
                        'countries' => $this->countries($startDate, $endDate),
                        'cities' => $this->cities($startDate, $endDate),
                    ];
                } catch (Throwable $exception) {
                    report($exception);

                    return $this->emptyFullReport($days);
                }
            }
        );
    }

    private function overview(string $startDate, string $endDate): array
    {
        $current = $this->metricsForPeriod($startDate, $endDate);

        $days = Carbon::parse($startDate)->diffInDays(Carbon::today()) + 1;

        $previousStart = Carbon::parse($startDate)
            ->subDays($days)
            ->toDateString();

        $previousEnd = Carbon::parse($startDate)
            ->subDay()
            ->toDateString();

        $previous = $this->metricsForPeriod($previousStart, $previousEnd);

        return [
            'active_users' => $current['activeUsers'],
            'sessions' => $current['sessions'],
            'pageviews' => $current['screenPageViews'],
            'event_count' => $current['eventCount'],
            'conversions' => $current['conversions'],
            'engaged_sessions' => $current['engagedSessions'],
            'engagement_rate' => $current['engagementRate'],
            'average_session_duration' => $current['averageSessionDuration'],

            'active_users_growth' => $this->calculateGrowth(
                $current['activeUsers'],
                $previous['activeUsers']
            ),
            'sessions_growth' => $this->calculateGrowth(
                $current['sessions'],
                $previous['sessions']
            ),
            'pageviews_growth' => $this->calculateGrowth(
                $current['screenPageViews'],
                $previous['screenPageViews']
            ),
            'conversion_rate' => $this->calculateConversion(
                $current['conversions'],
                $current['activeUsers']
            ),
        ];
    }

    private function metricsForPeriod(string $startDate, string $endDate): array
    {
        $response = $this->client()->runReport(new RunReportRequest([
            'property' => $this->property,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'dimension_filter' => $this->excludeInternalPagesFilter(),
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'eventCount']),
                new Metric(['name' => 'conversions']),
                new Metric(['name' => 'engagedSessions']),
                new Metric(['name' => 'engagementRate']),
                new Metric(['name' => 'averageSessionDuration']),
            ],
        ]));

        $row = $response->getRows()[0] ?? null;

        if (! $row) {
            return $this->emptyMetrics();
        }

        $values = $row->getMetricValues();

        return [
            'activeUsers' => (int) ($values[0]?->getValue() ?? 0),
            'sessions' => (int) ($values[1]?->getValue() ?? 0),
            'screenPageViews' => (int) ($values[2]?->getValue() ?? 0),
            'eventCount' => (int) ($values[3]?->getValue() ?? 0),
            'conversions' => (int) ($values[4]?->getValue() ?? 0),
            'engagedSessions' => (int) ($values[5]?->getValue() ?? 0),
            'engagementRate' => round((float) ($values[6]?->getValue() ?? 0) * 100, 1),
            'averageSessionDuration' => round((float) ($values[7]?->getValue() ?? 0), 1),
        ];
    }

    private function realtime(): array
    {
        try {
            $response = $this->client()->runRealtimeReport(
                new RunRealtimeReportRequest([
                    'property' => $this->property,
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                    ],
                ])
            );

            $row = $response->getRows()[0] ?? null;

            return [
                'active_users' => $row
                    ? (int) ($row->getMetricValues()[0]?->getValue() ?? 0)
                    : 0,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'active_users' => 0,
            ];
        }
    }

    private function timeseries(string $startDate, string $endDate): array
    {
        $response = $this->client()->runReport(new RunReportRequest([
            'property' => $this->property,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'dimension_filter' => $this->excludeInternalPagesFilter(),
            'dimensions' => [
                new Dimension(['name' => 'date']),
            ],
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'sessions']),
            ],
            'order_bys' => [
                new OrderBy([
                    'dimension' => new OrderBy\DimensionOrderBy([
                        'dimension_name' => 'date',
                    ]),
                ]),
            ],
        ]));

        $items = [];

        foreach ($response->getRows() as $row) {
            $date = $row->getDimensionValues()[0]?->getValue();

            $items[] = [
                'date' => $date
                    ? Carbon::createFromFormat('Ymd', $date)->format('Y-m-d')
                    : null,
                'active_users' => (int) ($row->getMetricValues()[0]?->getValue() ?? 0),
                'pageviews' => (int) ($row->getMetricValues()[1]?->getValue() ?? 0),
                'sessions' => (int) ($row->getMetricValues()[2]?->getValue() ?? 0),
            ];
        }

        return $items;
    }

    private function topPages(string $startDate, string $endDate): array
    {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: ['pagePath', 'pageTitle'],
            metrics: ['screenPageViews', 'activeUsers', 'averageSessionDuration'],
            orderMetric: 'screenPageViews',
            limit: 15,
            mapper: fn ($dimensions, $metrics) => [
                'path' => $dimensions[0] ?? '—',
                'title' => $dimensions[1] ?? 'Sem título',
                'pageviews' => (int) ($metrics[0] ?? 0),
                'active_users' => (int) ($metrics[1] ?? 0),
                'average_session_duration' => round((float) ($metrics[2] ?? 0), 1),
            ]
        );
    }

    private function sources(string $startDate, string $endDate): array
    {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: ['sessionSourceMedium'],
            metrics: ['sessions', 'activeUsers'],
            orderMetric: 'sessions',
            limit: 12,
            mapper: fn ($dimensions, $metrics) => [
                'source' => $dimensions[0] ?? '—',
                'sessions' => (int) ($metrics[0] ?? 0),
                'active_users' => (int) ($metrics[1] ?? 0),
            ]
        );
    }

    private function devices(string $startDate, string $endDate): array
    {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: ['deviceCategory'],
            metrics: ['activeUsers', 'sessions'],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn ($dimensions, $metrics) => [
                'device' => $dimensions[0] ?? '—',
                'active_users' => (int) ($metrics[0] ?? 0),
                'sessions' => (int) ($metrics[1] ?? 0),
            ]
        );
    }

    private function countries(string $startDate, string $endDate): array
    {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: ['country'],
            metrics: ['activeUsers', 'sessions'],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn ($dimensions, $metrics) => [
                'country' => $dimensions[0] ?? '—',
                'active_users' => (int) ($metrics[0] ?? 0),
                'sessions' => (int) ($metrics[1] ?? 0),
            ]
        );
    }

    private function cities(string $startDate, string $endDate): array
    {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: ['city'],
            metrics: ['activeUsers', 'sessions'],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn ($dimensions, $metrics) => [
                'city' => $dimensions[0] ?? '—',
                'active_users' => (int) ($metrics[0] ?? 0),
                'sessions' => (int) ($metrics[1] ?? 0),
            ]
        );
    }

    private function dimensionReport(
        string $startDate,
        string $endDate,
        array $dimensions,
        array $metrics,
        string $orderMetric,
        int $limit,
        callable $mapper
    ): array {
        $response = $this->client()->runReport(new RunReportRequest([
            'property' => $this->property,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'dimension_filter' => $this->excludeInternalPagesFilter(),
            'dimensions' => collect($dimensions)
                ->map(fn ($name) => new Dimension(['name' => $name]))
                ->values()
                ->all(),
            'metrics' => collect($metrics)
                ->map(fn ($name) => new Metric(['name' => $name]))
                ->values()
                ->all(),
            'order_bys' => [
                new OrderBy([
                    'metric' => new MetricOrderBy([
                        'metric_name' => $orderMetric,
                    ]),
                    'desc' => true,
                ]),
            ],
            'limit' => $limit,
        ]));

        $items = [];

        foreach ($response->getRows() as $row) {
            $dimensionValues = collect($row->getDimensionValues())
                ->map(fn ($value) => $value->getValue())
                ->all();

            $metricValues = collect($row->getMetricValues())
                ->map(fn ($value) => $value->getValue())
                ->all();

            $items[] = $mapper($dimensionValues, $metricValues);
        }

        return $items;
    }

    private function excludeInternalPagesFilter(): ?FilterExpression
    {
        $prefixes = config('google-analytics.excluded_path_prefixes', []);

        if (empty($prefixes)) {
            return null;
        }

        $expressions = collect($prefixes)
            ->filter()
            ->map(fn (string $prefix) => new FilterExpression([
                'not_expression' => new FilterExpression([
                    'filter' => new Filter([
                        'field_name' => 'pagePath',
                        'string_filter' => new StringFilter([
                            'match_type' => StringFilter\MatchType::BEGINS_WITH,
                            'value' => $prefix,
                        ]),
                    ]),
                ]),
            ]))
            ->values()
            ->all();

        if (empty($expressions)) {
            return null;
        }

        return new FilterExpression([
            'and_group' => new FilterExpressionList([
                'expressions' => $expressions,
            ]),
        ]);
    }

    private function client(): BetaAnalyticsDataClient
    {
        if ((string) config('google-analytics.auth_mode') === 'oauth') {
            $credentials = new UserRefreshCredentials(
                ['https://www.googleapis.com/auth/analytics.readonly'],
                [
                    'client_id' => (string) config('google-analytics.client_id'),
                    'client_secret' => (string) config('google-analytics.client_secret'),
                    'refresh_token' => (string) config('google-analytics.refresh_token'),
                ]
            );

            return new BetaAnalyticsDataClient([
                'credentials' => $credentials,
            ]);
        }

        return new BetaAnalyticsDataClient([
            'credentials' => $this->credentialsPath,
        ]);
    }

    private function emptyMetrics(): array
    {
        return [
            'activeUsers' => 0,
            'sessions' => 0,
            'screenPageViews' => 0,
            'eventCount' => 0,
            'conversions' => 0,
            'engagedSessions' => 0,
            'engagementRate' => 0,
            'averageSessionDuration' => 0,
        ];
    }

    private function emptyFullReport(int $days): array
    {
        return [
            'available' => false,
            'period' => [
                'days' => $days,
                'start_date' => now()->subDays($days - 1)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'overview' => [
                'active_users' => 0,
                'sessions' => 0,
                'pageviews' => 0,
                'event_count' => 0,
                'conversions' => 0,
                'engaged_sessions' => 0,
                'engagement_rate' => 0,
                'average_session_duration' => 0,
                'active_users_growth' => '0',
                'sessions_growth' => '0',
                'pageviews_growth' => '0',
                'conversion_rate' => '0.0',
            ],
            'realtime' => [
                'active_users' => 0,
            ],
            'timeseries' => [],
            'top_pages' => [],
            'sources' => [],
            'devices' => [],
            'countries' => [],
            'cities' => [],
        ];
    }

    private function calculateGrowth(int|float $current, int|float $previous): string
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? '+100' : '0';
        }

        $growth = (($current - $previous) / $previous) * 100;

        return ($growth >= 0 ? '+' : '') . round($growth, 1);
    }

    private function calculateConversion(int|float $conversions, int|float $visitors): string
    {
        if ((float) $visitors === 0.0) {
            return '0.0';
        }

        return (string) round(($conversions / $visitors) * 100, 1);
    }
}
