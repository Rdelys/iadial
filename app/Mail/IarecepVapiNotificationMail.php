<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IarecepVapiNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public array $data,
    ) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.iarecep-vapi')
            ->with(['rows' => $this->data]);
    }
}