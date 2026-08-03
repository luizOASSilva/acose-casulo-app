<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DonationGiftApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logoPath;

    public ?string $logoUrl;

    public function __construct(
        public Donation $donation
    ) {
        $logoPath = public_path('logo.svg');

        $this->logoPath = file_exists($logoPath)
            ? $logoPath
            : null;

        $this->logoUrl = null;
    }

    public function build(): self
    {
        return $this
            ->subject('Doação confirmada — seu brinde está sendo preparado')
            ->view('emails.donation-gift-approved')
            ->with([
                'donation' => $this->donation,
                'logoPath' => $this->logoPath,
                'logoUrl' => $this->logoUrl,
            ]);
    }
}
