<?php

namespace App\Services\Amqp;

use PhpAmqpLib\Connection\AMQPLazyConnection;

class Connection
{
    private $connection;

    /**
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     */
    public function __construct(
        private string $host,
        private string $port,
        private string $user,
        private string $password)
    {
        $this->connection = new AMQPLazyConnection($this->host, $this->port, $this->user, $this->password);
    }

    /**
     * @throws \Exception
     */
    function __destruct()
    {
        $this->connection->close();
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
