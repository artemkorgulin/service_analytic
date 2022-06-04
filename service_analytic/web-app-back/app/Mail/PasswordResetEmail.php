<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable implements ShouldQueue
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
            ->subject('Восстановление пароля в системе SellerExpert')
            ->view('emails.password_reset', ['url' => $this->url])
            ->text('emails.plain.password_reset', ['url' => $this->url]);
    }
}
