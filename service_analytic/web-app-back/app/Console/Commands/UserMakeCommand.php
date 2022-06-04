<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Сервисная команда для создания пользователя
 * Не нужна для нормальной работы приложения
 * Class UserMakeCommand
 * @package App\Console\Commands
 */
class UserMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать пользователя';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        do {
            $details = $this->askForUserDetails($details ?? null);
            $name = $details['name'];
            $email = $details['email'];
            $password = $details['password'];
        } while (!$this->confirm("Создать пользователя {$name} <{$email}>?", true));

        $user = User::create(
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'api_token' => Str::random(60),
            ]
        );
        $this->info("Создан новый пользователь #{$user->id}");
    }

    /**
     * @param null $defaults
     * @return array
     */
    protected function askForUserDetails($defaults = null)
    {
        $name = $this->ask('Полное имя пользователя?', $defaults['name'] ?? null);
        $email = $this->askUniqueEmail('Email адрес?', $defaults['email'] ?? null);
        $password = $this->ask('Пароль? (видимый)', $defaults['password'] ?? null);

        return compact('name', 'email', 'password');
    }

    /**
     * @param      $message
     * @param null $default
     * @return string
     */
    protected function askUniqueEmail($message, $default = null)
    {
        do {
            $email = $this->ask($message, $default);
        } while (!$this->checkEmailIsValid($email) || !$this->checkEmailIsUnique($email));

        return $email;
    }

    /**
     * @param $email
     * @return bool
     */
    protected function checkEmailIsValid($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('К сожалению "' . $email . '" не является корректным email адресом!');
            return false;
        }
        return true;
    }

    /**
     * @param $email
     * @return bool
     */
    public function checkEmailIsUnique($email)
    {
        if ($existingUser = User::whereEmail($email)->first()) {
            $this->error('К сожалению email адрес "' . $existingUser->email . '" уже существует для пользователя ' . $existingUser->name . '!');
            return false;
        }
        return true;
    }

}
