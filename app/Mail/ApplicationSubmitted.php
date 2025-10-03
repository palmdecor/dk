<?php

namespace App\Mail;

use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LoanApplication $application)
    {
    }

    public function build(): self
    {
        return $this->subject(__('mail.application_submitted_subject'))
            ->markdown('mail.application-submitted', [
                'application' => $this->application,
            ]);
    }
}
