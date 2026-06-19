<?php

namespace App\Console\Commands;

use App\Jobs\TranslatePublicContentJob;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Publication;
use App\Models\Setting;
use Illuminate\Console\Command;

class DispatchMissingTranslations extends Command
{
    protected $signature = 'translations:dispatch-missing
        {--sync : Executa os jobs imediatamente, sem fila}';

    protected $description = 'Dispara tradução automática em inglês para conteúdo público existente.';

    public function handle(): int
    {
        $sync = (bool) $this->option('sync');

        $this->dispatchFor('publication', Publication::query()->pluck('id')->all(), $sync);
        $this->dispatchFor('media', Media::query()->pluck('id')->all(), $sync);
        $this->dispatchFor('document_category', DocumentCategory::query()->pluck('id')->all(), $sync);
        $this->dispatchFor('document', Document::query()->pluck('id')->all(), $sync);
        $this->dispatchFor('partner', Partner::query()->pluck('id')->all(), $sync);
        $this->dispatchFor(
            'setting',
            Setting::query()
                ->where('is_public', true)
                ->pluck('id')
                ->all(),
            $sync
        );
        $this->dispatchFor('keyword', Keyword::query()->pluck('id')->all(), $sync);

        $this->info('Traduções despachadas.');

        return self::SUCCESS;
    }

    private function dispatchFor(string $type, array $ids, bool $sync): void
    {
        foreach ($ids as $id) {
            $this->line("Dispatch {$type}: {$id}");

            if ($sync) {
                TranslatePublicContentJob::dispatchSync($type, (int) $id);

                continue;
            }

            TranslatePublicContentJob::dispatch($type, (int) $id);
        }
    }
}
