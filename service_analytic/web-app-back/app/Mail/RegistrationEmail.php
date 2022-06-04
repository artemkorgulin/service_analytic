<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $url;
    public User $user;

    /**
     * Create a new message instance.
     *
     * @param  string  $url
     * @param  User  $user
     */
    public function __construct(string $url, User $user)
    {
        $this->url = $url;
        $this->user = $user;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build(): static
    {
        return $this->from(config('mail.from.address'))
            ->to($this->user->email, $this->user->name)
            ->subject('Подтверждение Email в системе SellerExpert')
            ->view('emails.registration_email', ['url' => $this->url])
            ->text('emails.plain.registration_email', ['url' => $this->url]);
    }
}
