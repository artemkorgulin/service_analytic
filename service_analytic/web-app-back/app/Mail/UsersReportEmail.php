<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class UsersReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public stdClass $report;

    /**
     * Create a new message instance.
     *
     * @param  stdClass  $report
     */
    public function __construct(stdClass $report)
    {
        $this->report = $report;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build(): static
    {
        $data = ['report' => $this->report, 'reportDate' => Carbon::yesterday()->toDateString()];

        return $this->from(config('mail.from.address'))
            ->to($this->report->email)
            ->view('emails.users_report', $data)
            ->view('emails.plain.users_report', $data);
    }
}
