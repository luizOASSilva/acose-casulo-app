<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\MediaFile;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\Keyword;
use App\Observers\DocumentObserver;
use App\Observers\MediaFileObserver;
use App\Observers\PartnerObserver;
use App\Observers\SettingObserver;
use App\Observers\KeywordObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerObservers();
    }

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

    protected function registerObservers(): void
    {
        Partner::observe(PartnerObserver::class);
        Document::observe(DocumentObserver::class);
        MediaFile::observe(MediaFileObserver::class);
        Setting::observe(SettingObserver::class);
        Keyword::observe(KeywordObserver::class);
    }
}
