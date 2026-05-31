<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\AdminCreationRequest;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmAdminCreationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logoPath = null;
    public ?string $logoUrl = null;
    public string $confirmationUrl;

    public function __construct(
        public AdminCreationRequest $creationRequest,
        public Admin $masterAdmin,
        string $confirmationUrl
    ) {
        $this->logoPath = Setting::emailLogoPath();
        $this->logoUrl = Setting::emailLogoUrl();

        $this->confirmationUrl = $this->resolveConfirmationUrl($confirmationUrl);
    }

    public function build(): self
    {
        return $this
            ->subject('Confirme a criação de um novo administrador')
            ->view('emails.admin-confirm-creation');
    }

    private function resolveConfirmationUrl(string $fallbackUrl): string
    {
        $token = $this->creationRequest->token ?? null;

        if (! filled($token)) {
            return $this->absoluteUrl($fallbackUrl);
        }

        return $this->frontendUrl(
            '/admin/confirmar-criacao-admin?token=' . urlencode((string) $token)
        );
    }

    private function frontendUrl(string $path): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        if (! $frontendUrl) {
            $frontendUrl = rtrim((string) config('app.url'), '/');
        }

        return $frontendUrl . '/' . ltrim($path, '/');
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $this->frontendUrl($url);
    }
}
