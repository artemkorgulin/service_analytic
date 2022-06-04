<?php

namespace App\Console\Commands;

use App\Mail\InviteEmail;
use App\Models\User;
use App\Services\FtpService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendEmailRegistaration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:emailRegistration
                {--input_name= : Имя входяшего файла на  FTP }';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Рассылка писем с регистрационными данными';
    private string $connection;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = 'ftp';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $inputName = $this->option('input_name');

        if (!$inputName) {
            $this->info('Name is required');

            return CommandAlias::FAILURE;
        }

        $timeStart = microtime(true);

        $this->info('Send mails');

        foreach ((new FtpService())->getRows($inputName, $this->connection) as $row) {

            if (!$row) {
                continue;
            }

            $row = explode(',', $row);
            $user = new User();

            try {
                $user->name = $row[0];
                $user->email = $row[1];
                $user->password = $row[2];

                $this->send($user);
            } catch (\Exception $exception) {
                report($exception);
                $this->error($exception->getMessage());
            }
        }

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $this->info('Send mails finished '.$time);

        return CommandAlias::SUCCESS;
    }

    /**
     * Отправить письмо с учетными данными
     *
     * @param  User  $user
     * @return void
     */
    public function send(User $user): void
    {
        try {
            Mail::send(new InviteEmail($user));
            sleep(1);
            $this->info($user->name.' '.$user->email);
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());
        }
    }
}
