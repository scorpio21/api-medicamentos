<?php

namespace App\Database;

use PDO;
use PDOException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Database
{
    private static $instance = null;
    private $connection;
    private $logger;

    private function __construct()
    {
        $this->logger = new Logger('database');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/database.log', Logger::DEBUG));
        
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'medication_control';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $this->logger->info('Database connection established successfully');
        } catch (PDOException $e) {
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollback();
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}