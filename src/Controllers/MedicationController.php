<?php
namespace App\Controllers;

use App\Models\Medication;
use App\Repositories\MedicationRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MedicationController
{
    private $repository;

    public function __construct()
    {
        $this->repository = new MedicationRepository();
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $limit = (int) ($queryParams['limit'] ?? 100);
            $offset = (int) ($queryParams['offset'] ?? 0);
            
            $medications = $this->repository->findAll($limit, $offset);
            $total = $this->repository->count();
            
            $data = [
                'success' => true,
                'data' => array_map(fn($med) => $med->toArray(), $medications),
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'pages' => ceil($total / $limit)
                ]
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $medication = $this->repository->findById($id);
            
            if (!$medication) {
                return $this->errorResponse($response, 'Medicamento no encontrado', 404);
            }
            
            $data = [
                'success' => true,
                'data' => $medication->toArray()
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody();
            
            if (empty($body)) {
                return $this->errorResponse($response, 'Datos del medicamento requeridos', 400);
            }
            
            $medication = new Medication($body);
            
            if (!$medication->validate()) {
                return $this->errorResponse($response, 'Datos del medicamento inválidos', 400);
            }
            
            $id = $this->repository->create($medication);
            $medication->setId($id);
            
            $data = [
                'success' => true,
                'message' => 'Medicamento creado exitosamente',
                'data' => $medication->toArray()
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $body = $request->getParsedBody();
            
            if (empty($body)) {
                return $this->errorResponse($response, 'Datos del medicamento requeridos', 400);
            }
            
            $existingMedication = $this->repository->findById($id);
            if (!$existingMedication) {
                return $this->errorResponse($response, 'Medicamento no encontrado', 404);
            }
            
            $body['id'] = $id;
            $medication = new Medication($body);
            
            if (!$medication->validate()) {
                return $this->errorResponse($response, 'Datos del medicamento inválidos', 400);
            }
            
            $success = $this->repository->update($medication);
            
            if (!$success) {
                return $this->errorResponse($response, 'Error al actualizar el medicamento', 500);
            }
            
            $data = [
                'success' => true,
                'message' => 'Medicamento actualizado exitosamente',
                'data' => $medication->toArray()
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            
            $medication = $this->repository->findById($id);
            if (!$medication) {
                return $this->errorResponse($response, 'Medicamento no encontrado', 404);
            }
            
            $success = $this->repository->delete($id);
            
            if (!$success) {
                return $this->errorResponse($response, 'Error al eliminar el medicamento', 500);
            }
            
            $data = [
                'success' => true,
                'message' => 'Medicamento eliminado exitosamente'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function search(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $term = $queryParams['q'] ?? '';
            
            if (empty($term)) {
                return $this->errorResponse($response, 'Término de búsqueda requerido', 400);
            }
            
            $medications = $this->repository->search($term);
            
            $data = [
                'success' => true,
                'data' => array_map(fn($med) => $med->toArray(), $medications),
                'total' => count($medications)
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function expiringSoon(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $days = (int) ($queryParams['days'] ?? 30);
            
            $medications = $this->repository->findExpiringSoon($days);
            
            $data = [
                'success' => true,
                'data' => array_map(fn($med) => $med->toArray(), $medications),
                'total' => count($medications),
                'days' => $days
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function lowStock(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $threshold = (int) ($queryParams['threshold'] ?? 10);
            
            $medications = $this->repository->findLowStock($threshold);
            
            $data = [
                'success' => true,
                'data' => array_map(fn($med) => $med->toArray(), $medications),
                'total' => count($medications),
                'threshold' => $threshold
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    public function updateQuantity(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $body = $request->getParsedBody();
            
            if (!isset($body['quantity']) || !is_numeric($body['quantity'])) {
                return $this->errorResponse($response, 'Cantidad requerida y debe ser numérica', 400);
            }
            
            $quantity = (int) $body['quantity'];
            if ($quantity < 0) {
                return $this->errorResponse($response, 'La cantidad no puede ser negativa', 400);
            }
            
            $medication = $this->repository->findById($id);
            if (!$medication) {
                return $this->errorResponse($response, 'Medicamento no encontrado', 404);
            }
            
            $success = $this->repository->updateQuantity($id, $quantity);
            
            if (!$success) {
                return $this->errorResponse($response, 'Error al actualizar la cantidad', 500);
            }
            
            $medication->setQuantity($quantity);
            
            $data = [
                'success' => true,
                'message' => 'Cantidad actualizada exitosamente',
                'data' => $medication->toArray()
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, $e->getMessage(), 500);
        }
    }

    private function errorResponse(Response $response, string $message, int $status): Response
    {
        $data = [
            'success' => false,
            'error' => $message,
            'status' => $status
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
=======
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

namespace App\Controllers;

use App\Auth\JWTAuth;
use App\Models\Medication;

class MedicationController
{
    private $auth;
    private $medicationModel;

    public function __construct()
    {
        $this->auth = new JWTAuth();
        $this->medicationModel = new Medication();
    }

    public function index()
    {
        try {
            $this->auth->requireAuth();
            
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $search = $_GET['search'] ?? null;
            $category_id = $_GET['category_id'] ?? null;

            $result = $this->medicationModel->getAll($page, $limit, $search, $category_id);
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $this->auth->requireAuth();
            
            $medication = $this->medicationModel->getById($id);
            
            if ($medication) {
                echo json_encode([
                    'success' => true,
                    'data' => $medication
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Medicamento no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $this->auth->requireAuth('pharmacist');
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validación básica
            if (!isset($input['name']) || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre del medicamento es requerido']);
                return;
            }

            $medication = $this->medicationModel->create($input);
            
            echo json_encode([
                'success' => true,
                'data' => $medication,
                'message' => 'Medicamento creado exitosamente'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        try {
            $this->auth->requireAuth('pharmacist');
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre del medicamento es requerido']);
                return;
            }

            $medication = $this->medicationModel->update($id, $input);
            
            if ($medication) {
                echo json_encode([
                    'success' => true,
                    'data' => $medication,
                    'message' => 'Medicamento actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Medicamento no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->auth->requireAuth('pharmacist');
            
            $result = $this->medicationModel->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Medicamento eliminado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Medicamento no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function search()
    {
        try {
            $this->auth->requireAuth();
            
            $query = $_GET['q'] ?? '';
            
            if (empty($query)) {
                http_response_code(400);
                echo json_encode(['error' => 'Término de búsqueda requerido']);
                return;
            }

            $medications = $this->medicationModel->search($query);
            
            echo json_encode([
                'success' => true,
                'data' => $medications
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function byCategory($categoryId)
    {
        try {
            $this->auth->requireAuth();
            
            $medications = $this->medicationModel->getByCategory($categoryId);
            
            echo json_encode([
                'success' => true,
                'data' => $medications
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}