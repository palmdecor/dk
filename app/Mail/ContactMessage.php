<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function build(): self
    {
        return $this->subject(__('mail.contact_subject', ['name' => $this->data['name']]))
            ->view('mail.contact')
            ->with(['data' => $this->data]);
    }
}
