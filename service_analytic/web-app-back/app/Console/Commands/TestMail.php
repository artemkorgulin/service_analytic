<?php

namespace App\Console\Commands;

use App\Mail\TestEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail {--email=: test mail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test send mail';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');

        if (!$email) {
            $this->error('Не задан адрес получателя. Параметр --email');
        }

        try {
            Mail::send(new TestEmail($email));
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }
}
