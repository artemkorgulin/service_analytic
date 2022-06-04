<?php

namespace App\Services\Amqp;

class Channel
{
    private $channel;
    private $queue;
    private $connection;

    /**
     * @param Connection $connection
     * @param string $queue
     */
    public function __construct(
        Connection $connection,
        string $queue)
    {
        $this->connection = $connection;
        $this->channel = $connection->getConnection()->channel();
        $this->queue = $queue;
        $this->channel->queue_declare($queue, false, true, false, false);
    }

    /**
     * @param $message
     * @param $exchange
     * @return void
     */
    public function push($message, $exchange = '')
    {
        $this->channel->basic_publish($message, $exchange, $this->queue);
    }

    function __destruct()
    {
        $this->channel->close();
    }
}
