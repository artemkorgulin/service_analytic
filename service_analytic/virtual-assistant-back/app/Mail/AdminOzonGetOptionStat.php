<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOzonGetOptionStat extends Mailable
{
    use Queueable, SerializesModels;


    private $changeStat = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($changeStat)
    {
        $this->changeStat = $changeStat;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $changeStat = $this->changeStat;
        return $this->view('email.ozon-popularity-options',compact('changeStat'));
    }
}
