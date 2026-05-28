<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCreatedInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $loginUrl;

    public function __construct(
        public Admin $admin
    ) {
        $this->loginUrl = config('app.frontend_url', config('app.url'))
            . '/admin/login';
    }

    public function build(): self
    {
        return $this
            ->subject('Seu acesso administrativo foi criado')
            ->view('emails.admin-created-invitation');
    }
}

