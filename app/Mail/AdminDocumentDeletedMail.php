<?php

namespace App\Mail;

use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AdminDocumentDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logoPath = null;

    public ?string $logoUrl = null;

    public function __construct(
        public readonly string $documentName,
        public readonly int|string|null $documentId,
        public readonly string $deletedByName,
        public readonly ?string $deletedByEmail,
        public readonly int|string|null $categoryId,
        public readonly ?string $categoryName,
        public readonly int|string|null $year,
        public readonly CarbonInterface $deletedAt,
        public readonly ?string $auditUrl = null,
    ) {
        $this->prepareLogo();
    }

    public function build(): self
    {
        return $this
            ->subject('Documento de transparência removido')
            ->view('emails.admin-document-deleted')
            ->with([
                'documentName' => $this->documentName,
                'documentId' => $this->documentId,
                'deletedByName' => $this->deletedByName,
                'deletedByEmail' => $this->deletedByEmail,
                'categoryId' => $this->categoryId,
                'categoryName' => $this->categoryName,
                'year' => $this->year,
                'deletedAt' => $this->deletedAt,
                'auditUrl' => $this->auditUrl,
                'logoPath' => $this->logoPath,
                'logoUrl' => $this->logoUrl,
            ]);
    }

    private function prepareLogo(): void
    {
        $logo = Setting::query()
            ->where('key', 'site_logo_url')
            ->value('value');

        if (! $logo) {
            return;
        }

        if (
            str_starts_with($logo, 'http://') ||
            str_starts_with($logo, 'https://') ||
            str_starts_with($logo, 'data:')
        ) {
            $this->logoUrl = $logo;

            return;
        }

        $path = ltrim($logo, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($path)) {
            $this->logoPath = Storage::disk('public')->path($path);

            return;
        }

        $this->logoUrl = $logo;
    }
}
