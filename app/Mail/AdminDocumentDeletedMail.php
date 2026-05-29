<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminDocumentDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $documentName,
        public readonly int|string|null $documentId,
        public readonly string $deletedByName,
        public readonly ?string $deletedByEmail,
        public readonly int|string|null $categoryId,
        public readonly ?string $categoryName,
        public readonly int|string|null $year,
        public readonly CarbonInterface $deletedAt,
    ) {
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
            ]);
    }
}
