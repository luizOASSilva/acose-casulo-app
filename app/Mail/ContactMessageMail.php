<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address(
                    $this->data['email'],
                    $this->data['name']
                ),
            ],
            subject: 'Novo contato pelo site: ' . $this->data['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            with: [
                'data' => $this->data,
                'logoPath' => $this->logoPath(),
                'logoUrl' => $this->logoUrl(),
            ],
        );
    }

    private function logoPath(): ?string
    {
        $path = public_path('images/logo.png');

        if (file_exists($path)) {
            return $path;
        }

        $path = public_path('logo.png');

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    private function logoUrl(): ?string
    {
        $logoUrl = Setting::query()
            ->where('key', 'site_logo_url')
            ->value('value');

        if (filled($logoUrl)) {
            return (string) $logoUrl;
        }

        return null;
    }
}
