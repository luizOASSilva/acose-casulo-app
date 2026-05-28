<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\AdminEmailChangeRequest;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmAdminEmailChangeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logoPath = null;
    public ?string $logoUrl = null;

    public function __construct(
        public AdminEmailChangeRequest $request,
        public Admin $targetAdmin,
        public Admin $masterAdmin,
        public string $confirmationUrl
    ) {
        $this->logoPath = Setting::emailLogoPath();
        $this->logoUrl = Setting::emailLogoUrl();
    }

    public function build(): self
    {
        return $this
            ->subject('Confirme a alteração de e-mail de administrador')
            ->view('emails.admin-confirm-email-change');
    }
}
