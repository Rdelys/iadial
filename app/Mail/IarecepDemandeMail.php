<?php
// app/Mail/IarecepDemandeMail.php

namespace App\Mail;

use App\Models\IarecepTest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IarecepDemandeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public IarecepTest $test,
        public ?string $phone = null,
        public ?string $message = null,
    ) {}

    public function build()
    {
        return $this->subject("Nouvelle demande IADial – {$this->test->company_name}")
            ->view('emails.iarecep-demande')
            ->with([
                'test' => $this->test,
                'phone' => $this->phone,
                'message' => $this->message,
            ]);
    }
}