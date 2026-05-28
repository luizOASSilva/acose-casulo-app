<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Article;
use App\Models\Document;
use App\Models\MediaFile;
use App\Models\Partner;
use App\Models\Setting;
use App\Observers\ActivityObserver;
use App\Observers\ArticleObserver;
use App\Observers\DocumentObserver;
use App\Observers\MediaFileObserver;
use App\Observers\PartnerObserver;
use App\Observers\SettingObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerObservers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        Article::observe(ArticleObserver::class);
        Activity::observe(ActivityObserver::class);
        Partner::observe(PartnerObserver::class);
        Document::observe(DocumentObserver::class);
        MediaFile::observe(MediaFileObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
