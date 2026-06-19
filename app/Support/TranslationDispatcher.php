<?php

namespace App\Support;

use App\Jobs\TranslatePublicContentJob;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Publication;
use App\Models\Setting;

class TranslationDispatcher
{
    public static function enabled(): bool
    {
        return (bool) config('services.openai.auto_translate_public_content')
            && filled(config('services.openai.api_key'));
    }

    public static function publication(Publication $publication): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('publication', $publication->id)
            ->afterCommit();

        if ($publication->media_id) {
            TranslatePublicContentJob::dispatch('media', (int) $publication->media_id)
                ->afterCommit();
        }
    }

    public static function media(Media $media): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('media', $media->id)
            ->afterCommit();
    }

    public static function document(Document $document): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('document', $document->id)
            ->afterCommit();
    }

    public static function documentCategory(DocumentCategory $category): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('document_category', $category->id)
            ->afterCommit();
    }

    public static function partner(Partner $partner): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('partner', $partner->id)
            ->afterCommit();
    }

    public static function setting(Setting $setting): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('setting', $setting->id)
            ->afterCommit();
    }

    public static function keyword(Keyword $keyword): void
    {
        if (! self::enabled()) {
            return;
        }

        TranslatePublicContentJob::dispatch('keyword', $keyword->id)
            ->afterCommit();
    }
}
