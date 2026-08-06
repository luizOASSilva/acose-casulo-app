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
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleAnalyticsService
{
    private string $property;

    private string $credentialsPath;

    private int $cacheSeconds;

    public function __construct()
    {
        $propertyId = trim(
            (string) config('google-analytics.property_id')
        );

        $this->property = 'properties/'.$propertyId;

        $this->credentialsPath = base_path(
            (string) config(
                'google-analytics.credentials_path'
            )
        );

        $this->cacheSeconds = max(
            0,
            (int) config(
                'google-analytics.cache_seconds',
                600
            )
        );
    }

    public function dashboardSummary(): array
    {
        /*
         * A versão no nome evita reutilizar respostas
         * "available: false" que ficaram no cache antigo.
         */
        $cacheKey = 'google_analytics.dashboard_summary.v2';

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $this->ensureConfigured();

            $today = $this->metricsForPeriod(
                'today',
                'today'
            );

            $yesterday = $this->metricsForPeriod(
                'yesterday',
                'yesterday'
            );

            $result = [
                'available' => true,

                'visitors' => $today['activeUsers'],

                'growth' => $this->calculateGrowth(
                    $today['activeUsers'],
                    $yesterday['activeUsers']
                ),

                'pageviews' => $today['screenPageViews'],

                /*
                 * Mantém o nome "conversion" na resposta,
                 * mas utiliza keyEvents na consulta ao GA4.
                 */
                'conversion' => $this->calculateConversion(
                    $today['keyEvents'],
                    $today['activeUsers']
                ),
            ];

            $this->cacheResult(
                $cacheKey,
                $result
            );

            return $result;
        } catch (Throwable $exception) {
            $this->logFailure(
                context: 'dashboard_summary',
                exception: $exception
            );

            return [
                'available' => false,
                'visitors' => 0,
                'growth' => '0',
                'pageviews' => 0,
                'conversion' => '0.0',
            ];
        }
    }

    public function fullReport(int $days = 30): array
    {
        $days = max(1, min($days, 365));

        /*
         * Nova versão da chave para ignorar o cache
         * antigo que pode conter available=false.
         */
        $cacheKey = "google_analytics.full_report.v2.{$days}";

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $this->ensureConfigured();

            $startDate = now()
                ->subDays($days - 1)
                ->toDateString();

            $endDate = 'today';

            $result = [
                'available' => true,

                'period' => [
                    'days' => $days,
                    'start_date' => $startDate,
                    'end_date' => now()->toDateString(),
                ],

                'overview' => $this->overview(
                    $startDate,
                    $endDate
                ),

                'realtime' => $this->realtime(),

                'timeseries' => $this->timeseries(
                    $startDate,
                    $endDate
                ),

                'top_pages' => $this->topPages(
                    $startDate,
                    $endDate
                ),

                'sources' => $this->sources(
                    $startDate,
                    $endDate
                ),

                'devices' => $this->devices(
                    $startDate,
                    $endDate
                ),

                'countries' => $this->countries(
                    $startDate,
                    $endDate
                ),

                'cities' => $this->cities(
                    $startDate,
                    $endDate
                ),
            ];

            /*
             * Somente respostas bem-sucedidas são armazenadas.
             * Erros não ficam presos no cache.
             */
            $this->cacheResult(
                $cacheKey,
                $result
            );

            return $result;
        } catch (Throwable $exception) {
            $this->logFailure(
                context: 'full_report',
                exception: $exception,
                extra: [
                    'days' => $days,
                ]
            );

            return $this->emptyFullReport($days);
        }
    }

    private function overview(
        string $startDate,
        string $endDate
    ): array {
        $current = $this->metricsForPeriod(
            $startDate,
            $endDate
        );

        $days = Carbon::parse($startDate)
            ->diffInDays(Carbon::today()) + 1;

        $previousStart = Carbon::parse($startDate)
            ->subDays($days)
            ->toDateString();

        $previousEnd = Carbon::parse($startDate)
            ->subDay()
            ->toDateString();

        $previous = $this->metricsForPeriod(
            $previousStart,
            $previousEnd
        );

        return [
            'active_users' => $current['activeUsers'],
            'sessions' => $current['sessions'],
            'pageviews' => $current['screenPageViews'],
            'event_count' => $current['eventCount'],

            /*
             * O frontend continua recebendo "conversions".
             * Internamente o valor vem de keyEvents.
             */
            'conversions' => $current['keyEvents'],

            'engaged_sessions' => $current['engagedSessions'],
            'engagement_rate' => $current['engagementRate'],

            'average_session_duration' =>
                $current['averageSessionDuration'],

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
                $current['keyEvents'],
                $current['activeUsers']
            ),
        ];
    }

    private function metricsForPeriod(
        string $startDate,
        string $endDate
    ): array {
        $response = $this->client()->runReport(
            new RunReportRequest([
                'property' => $this->property,

                'date_ranges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],

                'dimension_filter' =>
                    $this->excludeInternalPagesFilter(),

                'metrics' => [
                    new Metric([
                        'name' => 'activeUsers',
                    ]),

                    new Metric([
                        'name' => 'sessions',
                    ]),

                    new Metric([
                        'name' => 'screenPageViews',
                    ]),

                    new Metric([
                        'name' => 'eventCount',
                    ]),

                    /*
                     * Substitui a métrica antiga "conversions".
                     */
                    new Metric([
                        'name' => 'keyEvents',
                    ]),

                    new Metric([
                        'name' => 'engagedSessions',
                    ]),

                    new Metric([
                        'name' => 'engagementRate',
                    ]),

                    new Metric([
                        'name' => 'averageSessionDuration',
                    ]),
                ],
            ])
        );

        $row = $response->getRows()[0] ?? null;

        if (! $row) {
            return $this->emptyMetrics();
        }

        $values = $row->getMetricValues();

        return [
            'activeUsers' => (int) (
                $values[0]?->getValue() ?? 0
            ),

            'sessions' => (int) (
                $values[1]?->getValue() ?? 0
            ),

            'screenPageViews' => (int) (
                $values[2]?->getValue() ?? 0
            ),

            'eventCount' => (int) (
                $values[3]?->getValue() ?? 0
            ),

            'keyEvents' => (int) (
                $values[4]?->getValue() ?? 0
            ),

            'engagedSessions' => (int) (
                $values[5]?->getValue() ?? 0
            ),

            'engagementRate' => round(
                (float) (
                    $values[6]?->getValue() ?? 0
                ) * 100,
                1
            ),

            'averageSessionDuration' => round(
                (float) (
                    $values[7]?->getValue() ?? 0
                ),
                1
            ),
        ];
    }

    private function realtime(): array
    {
        try {
            $response = $this->client()->runRealtimeReport(
                new RunRealtimeReportRequest([
                    'property' => $this->property,

                    'metrics' => [
                        new Metric([
                            'name' => 'activeUsers',
                        ]),
                    ],
                ])
            );

            $row = $response->getRows()[0] ?? null;

            return [
                'active_users' => $row
                    ? (int) (
                        $row
                            ->getMetricValues()[0]
                            ?->getValue() ?? 0
                    )
                    : 0,
            ];
        } catch (Throwable $exception) {
            /*
             * Uma falha apenas no tempo real não precisa
             * derrubar o restante do relatório.
             */
            $this->logFailure(
                context: 'realtime',
                exception: $exception,
                reportException: false
            );

            return [
                'active_users' => 0,
            ];
        }
    }

    private function timeseries(
        string $startDate,
        string $endDate
    ): array {
        $response = $this->client()->runReport(
            new RunReportRequest([
                'property' => $this->property,

                'date_ranges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],

                'dimension_filter' =>
                    $this->excludeInternalPagesFilter(),

                'dimensions' => [
                    new Dimension([
                        'name' => 'date',
                    ]),
                ],

                'metrics' => [
                    new Metric([
                        'name' => 'activeUsers',
                    ]),

                    new Metric([
                        'name' => 'screenPageViews',
                    ]),

                    new Metric([
                        'name' => 'sessions',
                    ]),
                ],

                'order_bys' => [
                    new OrderBy([
                        'dimension' =>
                            new OrderBy\DimensionOrderBy([
                                'dimension_name' => 'date',
                            ]),
                    ]),
                ],
            ])
        );

        $items = [];

        foreach ($response->getRows() as $row) {
            $date = $row
                ->getDimensionValues()[0]
                ?->getValue();

            $items[] = [
                'date' => $date
                    ? Carbon::createFromFormat(
                        'Ymd',
                        $date
                    )->format('Y-m-d')
                    : null,

                'active_users' => (int) (
                    $row
                        ->getMetricValues()[0]
                        ?->getValue() ?? 0
                ),

                'pageviews' => (int) (
                    $row
                        ->getMetricValues()[1]
                        ?->getValue() ?? 0
                ),

                'sessions' => (int) (
                    $row
                        ->getMetricValues()[2]
                        ?->getValue() ?? 0
                ),
            ];
        }

        return $items;
    }

    private function topPages(
        string $startDate,
        string $endDate
    ): array {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: [
                'pagePath',
                'pageTitle',
            ],
            metrics: [
                'screenPageViews',
                'activeUsers',
                'averageSessionDuration',
            ],
            orderMetric: 'screenPageViews',
            limit: 15,
            mapper: fn (
                array $dimensions,
                array $metrics
            ) => [
                'path' => $dimensions[0] ?? '—',
                'title' => $dimensions[1] ?? 'Sem título',

                'pageviews' => (int) (
                    $metrics[0] ?? 0
                ),

                'active_users' => (int) (
                    $metrics[1] ?? 0
                ),

                'average_session_duration' => round(
                    (float) ($metrics[2] ?? 0),
                    1
                ),
            ]
        );
    }

    private function sources(
        string $startDate,
        string $endDate
    ): array {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: [
                'sessionSourceMedium',
            ],
            metrics: [
                'sessions',
                'activeUsers',
            ],
            orderMetric: 'sessions',
            limit: 12,
            mapper: fn (
                array $dimensions,
                array $metrics
            ) => [
                'source' => $dimensions[0] ?? '—',

                'sessions' => (int) (
                    $metrics[0] ?? 0
                ),

                'active_users' => (int) (
                    $metrics[1] ?? 0
                ),
            ]
        );
    }

    private function devices(
        string $startDate,
        string $endDate
    ): array {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: [
                'deviceCategory',
            ],
            metrics: [
                'activeUsers',
                'sessions',
            ],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn (
                array $dimensions,
                array $metrics
            ) => [
                'device' => $dimensions[0] ?? '—',

                'active_users' => (int) (
                    $metrics[0] ?? 0
                ),

                'sessions' => (int) (
                    $metrics[1] ?? 0
                ),
            ]
        );
    }

    private function countries(
        string $startDate,
        string $endDate
    ): array {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: [
                'country',
            ],
            metrics: [
                'activeUsers',
                'sessions',
            ],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn (
                array $dimensions,
                array $metrics
            ) => [
                'country' => $dimensions[0] ?? '—',

                'active_users' => (int) (
                    $metrics[0] ?? 0
                ),

                'sessions' => (int) (
                    $metrics[1] ?? 0
                ),
            ]
        );
    }

    private function cities(
        string $startDate,
        string $endDate
    ): array {
        return $this->dimensionReport(
            startDate: $startDate,
            endDate: $endDate,
            dimensions: [
                'city',
            ],
            metrics: [
                'activeUsers',
                'sessions',
            ],
            orderMetric: 'activeUsers',
            limit: 10,
            mapper: fn (
                array $dimensions,
                array $metrics
            ) => [
                'city' => $dimensions[0] ?? '—',

                'active_users' => (int) (
                    $metrics[0] ?? 0
                ),

                'sessions' => (int) (
                    $metrics[1] ?? 0
                ),
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
        $response = $this->client()->runReport(
            new RunReportRequest([
                'property' => $this->property,

                'date_ranges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],

                'dimension_filter' =>
                    $this->excludeInternalPagesFilter(),

                'dimensions' => collect($dimensions)
                    ->map(
                        fn (string $name) =>
                            new Dimension([
                                'name' => $name,
                            ])
                    )
                    ->values()
                    ->all(),

                'metrics' => collect($metrics)
                    ->map(
                        fn (string $name) =>
                            new Metric([
                                'name' => $name,
                            ])
                    )
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
            ])
        );

        $items = [];

        foreach ($response->getRows() as $row) {
            $dimensionValues = collect(
                $row->getDimensionValues()
            )
                ->map(
                    fn ($value) => $value->getValue()
                )
                ->all();

            $metricValues = collect(
                $row->getMetricValues()
            )
                ->map(
                    fn ($value) => $value->getValue()
                )
                ->all();

            $items[] = $mapper(
                $dimensionValues,
                $metricValues
            );
        }

        return $items;
    }

    private function excludeInternalPagesFilter(): ?FilterExpression
    {
        $prefixes = config(
            'google-analytics.excluded_path_prefixes',
            []
        );

        if (empty($prefixes)) {
            return null;
        }

        $expressions = collect($prefixes)
            ->filter()
            ->map(
                fn (string $prefix) =>
                    new FilterExpression([
                        'not_expression' =>
                            new FilterExpression([
                                'filter' => new Filter([
                                    'field_name' => 'pagePath',

                                    'string_filter' =>
                                        new StringFilter([
                                            'match_type' =>
                                                StringFilter\MatchType::BEGINS_WITH,

                                            'value' => $prefix,
                                        ]),
                                ]),
                            ]),
                    ])
            )
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
        $authMode = strtolower(
            trim(
                (string) config(
                    'google-analytics.auth_mode',
                    'service_account'
                )
            )
        );

        if ($authMode === 'oauth') {
            $credentials = new UserRefreshCredentials(
                [
                    'https://www.googleapis.com/auth/analytics.readonly',
                ],
                [
                    'client_id' => (string) config(
                        'google-analytics.client_id'
                    ),

                    'client_secret' => (string) config(
                        'google-analytics.client_secret'
                    ),

                    'refresh_token' => (string) config(
                        'google-analytics.refresh_token'
                    ),
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

    private function ensureConfigured(): void
    {
        $propertyId = trim(
            (string) config(
                'google-analytics.property_id'
            )
        );

        if ($propertyId === '') {
            throw new RuntimeException(
                'GOOGLE_ANALYTICS_PROPERTY_ID não foi configurado.'
            );
        }

        $authMode = strtolower(
            trim(
                (string) config(
                    'google-analytics.auth_mode',
                    'service_account'
                )
            )
        );

        if ($authMode === 'oauth') {
            $clientId = trim(
                (string) config(
                    'google-analytics.client_id'
                )
            );

            $clientSecret = trim(
                (string) config(
                    'google-analytics.client_secret'
                )
            );

            $refreshToken = trim(
                (string) config(
                    'google-analytics.refresh_token'
                )
            );

            if ($clientId === '') {
                throw new RuntimeException(
                    'GOOGLE_ANALYTICS_CLIENT_ID não foi configurado.'
                );
            }

            if ($clientSecret === '') {
                throw new RuntimeException(
                    'GOOGLE_ANALYTICS_CLIENT_SECRET não foi configurado.'
                );
            }

            if ($refreshToken === '') {
                throw new RuntimeException(
                    'GOOGLE_ANALYTICS_REFRESH_TOKEN não foi configurado.'
                );
            }

            return;
        }

        if (
            $this->credentialsPath === '' ||
            ! is_file($this->credentialsPath)
        ) {
            throw new RuntimeException(
                'O arquivo de credenciais da conta de serviço do Google Analytics não foi encontrado.'
            );
        }

        if (! is_readable($this->credentialsPath)) {
            throw new RuntimeException(
                'O arquivo de credenciais do Google Analytics não pode ser lido.'
            );
        }
    }

    private function cacheResult(
        string $key,
        array $result
    ): void {
        if ($this->cacheSeconds <= 0) {
            return;
        }

        Cache::put(
            $key,
            $result,
            now()->addSeconds($this->cacheSeconds)
        );
    }

    private function logFailure(
        string $context,
        Throwable $exception,
        array $extra = [],
        bool $reportException = true
    ): void {
        Log::error(
            'Falha ao consultar o Google Analytics.',
            array_merge(
                [
                    'context' => $context,
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                    'code' => $exception->getCode(),
                    'property' => $this->property,

                    'auth_mode' => config(
                        'google-analytics.auth_mode'
                    ),

                    'credentials_path' =>
                        $this->credentialsPath,
                ],
                $extra
            )
        );

        if ($reportException) {
            report($exception);
        }
    }

    private function emptyMetrics(): array
    {
        return [
            'activeUsers' => 0,
            'sessions' => 0,
            'screenPageViews' => 0,
            'eventCount' => 0,
            'keyEvents' => 0,
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

                'start_date' => now()
                    ->subDays($days - 1)
                    ->toDateString(),

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

    private function calculateGrowth(
        int|float $current,
        int|float $previous
    ): string {
        if ((float) $previous === 0.0) {
            return (float) $current > 0
                ? '+100'
                : '0';
        }

        $growth = (
            ($current - $previous) /
            $previous
        ) * 100;

        return ($growth >= 0 ? '+' : '')
            .round($growth, 1);
    }

    private function calculateConversion(
        int|float $conversions,
        int|float $visitors
    ): string {
        if ((float) $visitors === 0.0) {
            return '0.0';
        }

        return (string) round(
            ($conversions / $visitors) * 100,
            1
        );
    }
}
