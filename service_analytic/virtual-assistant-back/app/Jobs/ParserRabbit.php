<?php

namespace App\Jobs;

use App\Services\Amqp\Channel;
use App\Services\Amqp\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpAmqpLib\Message\AMQPMessage;

class ParserRabbit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $messages;

    public array $config;

    /**
     * Create a new job instance.
     *
     * @param  string|array  $messages
     * @param  array  $config
     */
    public function __construct(string|array $messages, array $config)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        $this->messages = $messages;

        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $connection = new Connection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['password']);
        $channel = new Channel($connection, $this->config['queue']);

        foreach ($this->messages as $message) {
            $msg = new AMQPMessage(
                $message,
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $channel->push($msg);
        }
    }
}
