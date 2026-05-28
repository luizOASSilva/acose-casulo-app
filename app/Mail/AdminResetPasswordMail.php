<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminResetPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logoPath = null;
    public ?string $logoUrl = null;

    public function __construct(
        public Admin $admin,
        public string $resetUrl
    ) {
        $this->logoPath = Setting::emailLogoPath();
        $this->logoUrl = Setting::emailLogoUrl();
    }

    public function build(): self
    {
        return $this
            ->subject('Redefinição de senha do painel administrativo')
            ->view('emails.admin-reset-password');
    }
}
