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
    }
}