<?php

namespace App\Jobs\User;

use App\Models\User;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewUserEmailSubscriber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public string $ip;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $ip = null)
    {
        $this->user = $user;
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $token = config('event_master.token');
            $apiUrl = config('event_master.url_v1').'/notification_schemas';
            Http::withHeaders([
                'Authorization-Web-App' => $token,
            ])->post(
                $apiUrl,
                [
                    'user' => ['id' => $this->user->id],
                    'type_id' => 2,
                    'way_code' => 'email',
                    'ip' => $this->ip
                ]
            );
            Http::withHeaders([
                'Authorization-Web-App' => $token,
            ])->post(
                $apiUrl,
                [
                    'user' => ['id' => $this->user->id],
                    'type_id' => 6,
                    'way_code' => 'email',
                    'ip' => $this->ip
                ]
            );
            Http::withHeaders([
                'Authorization-Web-App' => $token,
            ])->post(
                $apiUrl,
                [
                    'user' => ['id' => $this->user->id],
                    'type_id' => 7,
                    'way_code' => 'email',
                    'ip' => $this->ip
                ]
            );
        } catch (Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }
    }
}
