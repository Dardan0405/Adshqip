<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdvertiserEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $mailMessage,
        public ?string $actionUrl = null,
    ) {
    }

    public function build(): static
    {
        return $this
            ->subject($this->mailSubject)
            ->view('emails.advertiser-event')
            ->with([
                'mailMessage' => $this->mailMessage,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}
