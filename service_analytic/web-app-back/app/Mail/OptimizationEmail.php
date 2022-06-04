<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OptimizationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $message;

    /**
     * Create a new message instance.
     *
     * @param  string  $message
     * @param  string  $subject
     */
    public function __construct(string $message, string $subject)
    {
        $this->message = $message;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
            ->to(config('mail.optimization_to'))
            ->subject($this->subject)
            ->view('emails.optimization', ['message' => $this->message, 'subject' => $this->subject])
            ->text('emails.plain.optimization', ['message' => strip_tags($this->message), 'subject' => $this->subject]);
    }
}
