<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Keyword;
use App\Models\MediaFile;
use App\Models\Partner;
use App\Models\Setting;
use App\Observers\DocumentObserver;
use App\Observers\KeywordObserver;
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
     * Configure application defaults.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        $allowDestructiveCommands = filter_var(
            env('ALLOW_DESTRUCTIVE_DB_COMMANDS', false),
            FILTER_VALIDATE_BOOL
        );

        DB::prohibitDestructiveCommands(
            app()->isProduction() && ! $allowDestructiveCommands
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null
        );
    }

    protected function registerObservers(): void
    {
        Partner::observe(PartnerObserver::class);
        Document::observe(DocumentObserver::class);
        MediaFile::observe(MediaFileObserver::class);
        Setting::observe(SettingObserver::class);
        Keyword::observe(KeywordObserver::class);
    }
}
