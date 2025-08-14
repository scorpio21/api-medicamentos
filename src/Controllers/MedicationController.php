<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Validation;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MedicationController
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function listMedications(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $searchTerm = trim((string)($queryParams['search'] ?? ''));
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = min(100, max(1, (int)($queryParams['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $pdo = $this->database->getConnection();

        if ($searchTerm !== '') {
            $stmt = $pdo->prepare(
                'SELECT * FROM medications
                 WHERE name LIKE :q OR description LIKE :q OR dosage LIKE :q OR lot_number LIKE :q
                 ORDER BY id DESC
                 LIMIT :limit OFFSET :offset'
            );
            $stmt->bindValue(':q', '%' . $searchTerm . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare(
                'SELECT * FROM medications
                 ORDER BY id DESC
                 LIMIT :limit OFFSET :offset'
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $items = $stmt->fetchAll();

        if ($searchTerm !== '') {
            $countStmt = $pdo->prepare(
                'SELECT COUNT(*) AS total FROM medications
                 WHERE name LIKE :q OR description LIKE :q OR dosage LIKE :q OR lot_number LIKE :q'
            );
            $countStmt->bindValue(':q', '%' . $searchTerm . '%', PDO::PARAM_STR);
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();
        } else {
            $total = (int)$pdo->query('SELECT COUNT(*) AS total FROM medications')->fetchColumn();
        }

        $payload = [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
            ],
        ];

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getMedicationById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)($args['id'] ?? 0);
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM medications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $medication = $stmt->fetch();

        if (!$medication) {
            return $this->jsonError($response, 404, 'Medicamento no encontrado');
        }

        $response->getBody()->write(json_encode($medication, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createMedication(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array)$request->getParsedBody();
        try {
            $name = Validation::requireString($data, 'name');
            $description = Validation::optionalString($data, 'description');
            $dosage = Validation::optionalString($data, 'dosage');
            $stock = Validation::nonNegativeInt($data, 'stock', 0);
            $expirationDate = Validation::optionalDate($data, 'expiration_date');
            $lotNumber = Validation::optionalString($data, 'lot_number');
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($response, 422, $e->getMessage());
        }

        $now = (new \DateTimeImmutable())->format('c');

        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO medications (name, description, dosage, stock, expiration_date, lot_number, created_at, updated_at)
             VALUES (:name, :description, :dosage, :stock, :expiration_date, :lot_number, :created_at, :updated_at)'
        );
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':dosage' => $dosage,
            ':stock' => $stock,
            ':expiration_date' => $expirationDate,
            ':lot_number' => $lotNumber,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $id = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM medications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $created = $stmt->fetch();

        $response->getBody()->write(json_encode($created, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function updateMedication(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)($args['id'] ?? 0);
        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare('SELECT * FROM medications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            return $this->jsonError($response, 404, 'Medicamento no encontrado');
        }

        $data = (array)$request->getParsedBody();
        try {
            $name = array_key_exists('name', $data) ? Validation::requireString($data, 'name') : $existing['name'];
            $description = array_key_exists('description', $data) ? Validation::optionalString($data, 'description') : $existing['description'];
            $dosage = array_key_exists('dosage', $data) ? Validation::optionalString($data, 'dosage') : $existing['dosage'];
            $stock = array_key_exists('stock', $data) ? Validation::nonNegativeInt($data, 'stock', (int)$existing['stock']) : (int)$existing['stock'];
            $expirationDate = array_key_exists('expiration_date', $data) ? Validation::optionalDate($data, 'expiration_date') : $existing['expiration_date'];
            $lotNumber = array_key_exists('lot_number', $data) ? Validation::optionalString($data, 'lot_number') : $existing['lot_number'];
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($response, 422, $e->getMessage());
        }

        $now = (new \DateTimeImmutable())->format('c');

        $updateStmt = $pdo->prepare(
            'UPDATE medications SET
                name = :name,
                description = :description,
                dosage = :dosage,
                stock = :stock,
                expiration_date = :expiration_date,
                lot_number = :lot_number,
                updated_at = :updated_at
             WHERE id = :id'
        );
        $updateStmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':dosage' => $dosage,
            ':stock' => $stock,
            ':expiration_date' => $expirationDate,
            ':lot_number' => $lotNumber,
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        $stmt = $pdo->prepare('SELECT * FROM medications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $updated = $stmt->fetch();

        $response->getBody()->write(json_encode($updated, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function deleteMedication(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)($args['id'] ?? 0);
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM medications WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $response->withStatus(204);
    }

    private function jsonError(ResponseInterface $response, int $status, string $message): ResponseInterface
    {
        $payload = ['error' => $message, 'status' => $status];
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}