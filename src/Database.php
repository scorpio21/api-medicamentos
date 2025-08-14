<?php
declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

final class Database
{
    private ?PDO $pdo = null;
    private string $databaseFilePath;

    public function __construct(?string $databaseFilePath = null)
    {
        $envPath = getenv('DB_PATH') ?: null;
        $this->databaseFilePath = $databaseFilePath
            ?? $envPath
            ?? __DIR__ . '/../var/database.sqlite';
    }

    public function getConnection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $directory = dirname($this->databaseFilePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new RuntimeException('No se pudo crear el directorio de datos: ' . $directory);
            }
        }

        $this->pdo = new PDO('sqlite:' . $this->databaseFilePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $this->pdo;
    }

    public function initialize(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS medications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                dosage TEXT,
                stock INTEGER NOT NULL DEFAULT 0,
                expiration_date TEXT,
                lot_number TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
    }
}