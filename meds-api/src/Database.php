<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database {
	private static ?PDO $connection = null;

	public static function getConnection(): PDO {
		if (self::$connection !== null) {
			return self::$connection;
		}

		$databaseDirectory = BASE_PATH . '/data';
		$databasePath = $databaseDirectory . '/database.sqlite';

		if (!is_dir($databaseDirectory)) {
			mkdir($databaseDirectory, 0777, true);
		}

		$dsn = 'sqlite:' . $databasePath;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		try {
			self::$connection = new PDO($dsn, null, null, $options);
		} catch (PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
			exit;
		}

		return self::$connection;
	}

	public static function initialize(): void {
		$conn = self::getConnection();
		// Create tables if they do not exist
		$conn->exec(
			'CREATE TABLE IF NOT EXISTS medications (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name TEXT NOT NULL,
				dosage TEXT,
				stock INTEGER NOT NULL DEFAULT 0,
				notes TEXT,
				created_at TEXT NOT NULL,
				updated_at TEXT NOT NULL
			)'
		);

		// Simple index for searches by name
		$conn->exec('CREATE INDEX IF NOT EXISTS idx_medications_name ON medications(name)');
	}
}