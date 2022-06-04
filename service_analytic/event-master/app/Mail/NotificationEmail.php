<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $message;
    public string $userEmail;

    /**
     * Create a new message instance.
     *
     * @param string $userEmail
     * @param string $message
     * @param string $subject
     */
    public function __construct(string $userEmail, string $message, string $subject)
    {
        $this->userEmail = $userEmail;
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
            ->to($this->userEmail)
            ->subject($this->subject)
            ->view('emails.notification')
            ->with([
                'notification' => $this->message,
                'subject' => $this->subject,
            ])
            ->text('emails.plain.notification');
    }
}
