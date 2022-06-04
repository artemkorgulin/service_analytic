<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
            ->to($this->user->email, $this->user->name)
            ->subject('Ваш кабинет SellerExpert готов к работе!')
            ->view('emails.invite_email', ['user' => $this->user])
            ->text('emails.plain.invite_email', ['user' => $this->user]);
    }
}
