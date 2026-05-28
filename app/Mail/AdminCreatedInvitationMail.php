<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCreatedInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $loginUrl;
    public ?string $logoPath = null;
    public ?string $logoUrl = null;

    public function __construct(
        public Admin $admin
    ) {
        $panelSlug = config('app.panel_slug');

        $this->loginUrl = rtrim(config('app.frontend_url', config('app.url')), '/')
            . '/acesso/'
            . urlencode((string) $panelSlug);

        $this->logoPath = Setting::emailLogoPath();
        $this->logoUrl = Setting::emailLogoUrl();
    }

    public function build(): self
    {
        return $this
            ->subject('Seu acesso administrativo foi criado')
            ->view('emails.admin-created-invitation');
    }
}
