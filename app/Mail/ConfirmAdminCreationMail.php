<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\AdminCreationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmAdminCreationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public AdminCreationRequest $creationRequest,
        public Admin $masterAdmin,
        public string $confirmationUrl
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Confirme a criação de um novo administrador')
            ->view('emails.admin-confirm-creation');
    }
}
