<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentCategoryTranslation;
use App\Models\DocumentTranslation;
use App\Models\Keyword;
use App\Models\KeywordTranslation;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\Publication;
use App\Models\PublicationTranslation;
use App\Models\Setting;
use App\Models\SettingTranslation;
use App\Services\TranslationService;
use App\Support\RichTextSanitizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class TranslatePublicContentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(
        public string $type,
        public int $id
    ) {}

    public function handle(TranslationService $translator): void
    {
        match ($this->type) {
            'publication' => $this->translatePublication($translator),
            'media' => $this->translateMedia($translator),
            'document' => $this->translateDocument($translator),
            'document_category' => $this->translateDocumentCategory($translator),
            'partner' => $this->translatePartner($translator),
            'setting' => $this->translateSetting($translator),
            'keyword' => $this->translateKeyword($translator),
            default => null,
        };
    }

    private function translatePublication(TranslationService $translator): void
    {
        $publication = Publication::query()
            ->with('translations')
            ->find($this->id);

        if (! $publication) {
            return;
        }

        $pt = $publication->translationFor(PublicationTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = PublicationTranslation::firstOrNew([
            'publication_id' => $publication->id,
            'locale' => PublicationTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'title' => $pt->title,
            'slug' => $this->uniquePublicationSlug($pt->slug ?: $pt->title, $publication->id),
            'content' => $pt->content,
            'summary' => $pt->summary,
            'translation_status' => PublicationTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $title = $translator->translatePlainTextToEnglish($pt->title);
            $content = $translator->translateHtmlToEnglish($pt->content);
            $summary = $pt->summary
                ? $translator->translatePlainTextToEnglish($pt->summary)
                : null;

            $en->update([
                'title' => $title ?: $pt->title,
                'slug' => $this->uniquePublicationSlug($title ?: $pt->title, $publication->id),
                'content' => RichTextSanitizer::clean($content ?: $pt->content),
                'summary' => $summary,
                'translation_status' => PublicationTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => PublicationTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function translateMedia(TranslationService $translator): void
    {
        $media = Media::query()
            ->with('translations')
            ->find($this->id);

        if (! $media) {
            return;
        }

        $pt = $media->translationFor(MediaTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = MediaTranslation::firstOrNew([
            'media_id' => $media->id,
            'locale' => MediaTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'alt_text' => $pt->alt_text,
            'caption' => $pt->caption,
            'translation_status' => MediaTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $en->update([
                'alt_text' => $pt->alt_text
                    ? $translator->translatePlainTextToEnglish($pt->alt_text)
                    : null,
                'caption' => $pt->caption
                    ? $translator->translatePlainTextToEnglish($pt->caption)
                    : null,
                'translation_status' => MediaTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => MediaTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function translateDocument(TranslationService $translator): void
    {
        $document = Document::query()
            ->with('translations')
            ->find($this->id);

        if (! $document) {
            return;
        }

        $pt = $document->translationFor(DocumentTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = DocumentTranslation::firstOrNew([
            'document_id' => $document->id,
            'locale' => DocumentTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'title' => $pt->title,
            'translation_status' => DocumentTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $en->update([
                'title' => $translator->translatePlainTextToEnglish($pt->title) ?: $pt->title,
                'translation_status' => DocumentTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => DocumentTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function translateDocumentCategory(TranslationService $translator): void
    {
        $category = DocumentCategory::query()
            ->with('translations')
            ->find($this->id);

        if (! $category) {
            return;
        }

        $pt = $category->translationFor(DocumentCategoryTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = DocumentCategoryTranslation::firstOrNew([
            'document_category_id' => $category->id,
            'locale' => DocumentCategoryTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'name' => $pt->name,
            'description' => $pt->description,
            'translation_status' => DocumentCategoryTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $en->update([
                'name' => $translator->translatePlainTextToEnglish($pt->name) ?: $pt->name,
                'description' => $pt->description
                    ? $translator->translatePlainTextToEnglish($pt->description)
                    : null,
                'translation_status' => DocumentCategoryTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => DocumentCategoryTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function translatePartner(TranslationService $translator): void
    {
        $partner = Partner::query()
            ->with('translations')
            ->find($this->id);

        if (! $partner) {
            return;
        }

        $pt = $partner->translationFor(PartnerTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = PartnerTranslation::firstOrNew([
            'partner_id' => $partner->id,
            'locale' => PartnerTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'logo_alt' => $pt->logo_alt,
            'translation_status' => PartnerTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $en->update([
                'logo_alt' => $pt->logo_alt
                    ? $translator->translatePlainTextToEnglish($pt->logo_alt)
                    : null,
                'translation_status' => PartnerTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => PartnerTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function translateSetting(TranslationService $translator): void
    {
        $setting = Setting::query()
            ->with('translations')
            ->find($this->id);

        if (! $setting || ! $setting->isTranslatablePublicValue()) {
            return;
        }

        $pt = $setting->translationFor(SettingTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = SettingTranslation::firstOrNew([
            'setting_id' => $setting->id,
            'locale' => SettingTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'value' => $pt->value,
            'translation_status' => SettingTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $en->update([
                'value' => $pt->value
                    ? $translator->translatePlainTextToEnglish($pt->value)
                    : null,
                'translation_status' => SettingTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);

            Setting::clearCache();
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => SettingTranslation::STATUS_FAILED,
            ]);

            Setting::clearCache();

            report($exception);

            throw $exception;
        }
    }

    private function translateKeyword(TranslationService $translator): void
    {
        $keyword = Keyword::query()
            ->with('translations')
            ->find($this->id);

        if (! $keyword) {
            return;
        }

        $pt = $keyword->translationFor(KeywordTranslation::LOCALE_PT_BR);

        if (! $pt) {
            return;
        }

        $en = KeywordTranslation::firstOrNew([
            'keyword_id' => $keyword->id,
            'locale' => KeywordTranslation::LOCALE_EN,
        ]);

        if ($this->isFresh($pt, $en)) {
            return;
        }

        $en->fill([
            'word' => $pt->word,
            'translation_status' => KeywordTranslation::STATUS_PENDING,
            'translated_at' => null,
        ])->save();

        try {
            $translatedWord = $translator->translatePlainTextToEnglish($pt->word) ?: $pt->word;

            $en->update([
                'word' => $this->uniqueKeywordWord($translatedWord, $keyword->id),
                'translation_status' => KeywordTranslation::STATUS_TRANSLATED,
                'translated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $en->update([
                'translation_status' => KeywordTranslation::STATUS_FAILED,
            ]);

            report($exception);

            throw $exception;
        }
    }

    private function isFresh($pt, $en): bool
    {
        if (! $en->exists) {
            return false;
        }

        if ($en->translation_status !== 'translated') {
            return false;
        }

        if (! $en->translated_at) {
            return false;
        }

        return $pt->updated_at
            ? $en->translated_at->greaterThanOrEqualTo($pt->updated_at)
            : true;
    }

    private function uniquePublicationSlug(string $value, int $publicationId): string
    {
        $base = Str::slug(Str::limit($value, 70, '')) ?: 'publication-' . $publicationId;

        $slug = $base;
        $counter = 2;

        while (
            PublicationTranslation::query()
                ->where('locale', PublicationTranslation::LOCALE_EN)
                ->where('slug', $slug)
                ->where('publication_id', '!=', $publicationId)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function uniqueKeywordWord(string $value, int $keywordId): string
    {
        $word = trim($value);

        if ($word === '') {
            return 'keyword-' . $keywordId;
        }

        $candidate = $word;
        $counter = 2;

        while (
            KeywordTranslation::query()
                ->where('locale', KeywordTranslation::LOCALE_EN)
                ->where('word', $candidate)
                ->where('keyword_id', '!=', $keywordId)
                ->exists()
        ) {
            $candidate = $word . ' ' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
