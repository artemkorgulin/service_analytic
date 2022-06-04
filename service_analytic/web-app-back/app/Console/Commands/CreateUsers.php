<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FtpService;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:users
                {--input_name= : Имя входяшего файла на  FTP  }
                {--output_name=out_users.csv : Имя исходящего файла }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create users';

    /**
     * @var string
     */
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
     */
    public function handle()
    {
        $inputName = $this->option('input_name');
        $outputName = $this->option('output_name');

        if (!$inputName) {
            $this->info('Name is required');

            return CommandAlias::FAILURE;
        }

        $timeStart = microtime(true);
        $this->info('Add users');

        /** @var  $file */
        $file = fopen(storage_path('app/') . $outputName, "w");

        foreach ((new FtpService())->getRows($inputName, $this->connection) as $row) {

            if (!$row) {
                continue;
            }

            $row = explode(',', $row);
            $passwd = Str::random(8);
            $user = new User();

            try {
                $user->email = $row[0];
                $user->name = strlen($row[1]) !== 0  ? trim($row[1]) : 'Ф.И.О.';
                $user->email_verified_at = now();
                $user->password = Hash::make($passwd);
                $user->first_login = 1;
                $user->phone = $row[2] ?? null;
                $user->api_token = Str::random(60);
                $user->save();

                fputcsv($file, [$user->name, $user->email, $passwd, $user->phone]);

            } catch (\Exception $exception) {
                report($exception);
                $this->error($exception->getMessage());
            }
        }
        fclose($file);

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $this->info('Add users finished '.$time);

        return CommandAlias::SUCCESS;
    }
}
