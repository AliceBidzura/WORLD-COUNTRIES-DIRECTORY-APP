<?php

namespace App\Rdb;

class SqlHelper
{
    private string $host;
    private string $user;
    private string $password;
    private string $dbName;
    private int $port;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'db';
        $this->user = $_ENV['DB_USER'] ?? 'world_user';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'world_pass';
        $this->dbName = $_ENV['DB_NAME'] ?? 'world_db';
        $this->port = (int)($_ENV['DB_PORT'] ?? 3306);

        $this->pingDb();
    }

    public function openDbConnection(): \mysqli
    {
        $conn = new \mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->dbName,
            $this->port
        );

        if ($conn->connect_errno) {
            throw new \Exception("DB connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    private function pingDb(): void
    {
        $conn = $this->openDbConnection();
        $conn->close();
    }
}