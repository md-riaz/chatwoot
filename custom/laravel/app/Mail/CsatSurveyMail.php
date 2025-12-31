<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CsatSurveyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $surveyUrl
    ) {}

    public function build(): self
    {
        return $this->subject(sprintf('%s: We value your feedback', config('app.name')))
            ->view('emails.csat-survey');
    }
}
