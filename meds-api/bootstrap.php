<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Base path
const BASE_PATH = __DIR__;

// Autoload simple (manual requires)
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Router.php';
require_once BASE_PATH . '/src/MedicationController.php';

// Basic CORS for local development and simple integrations
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	echo json_encode(['status' => 'ok']);
	exit;
}

/**
 * Read JSON body as associative array.
 */
function readJsonBody(): array {
	$raw = file_get_contents('php://input');
	if ($raw === false || $raw === '') {
		return [];
	}
	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : [];
}

/**
 * Send a JSON response with HTTP status code.
 */
function sendJson(int $statusCode, array $payload): void {
	http_response_code($statusCode);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Ensure database and schema exist
\App\Database::initialize();