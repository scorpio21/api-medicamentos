<?php

namespace App\Controllers;

use App\Auth\JWTAuth;
use App\Models\Inventory;

class InventoryController
{
    private $auth;
    private $inventoryModel;

    public function __construct()
    {
        $this->auth = new JWTAuth();
        $this->inventoryModel = new Inventory();
    }

    public function index()
    {
        try {
            $this->auth->requireAuth();
            
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $medication_id = $_GET['medication_id'] ?? null;

            $result = $this->inventoryModel->getAll($page, $limit, $medication_id);
            
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
            
            $inventory = $this->inventoryModel->getById($id);
            
            if ($inventory) {
                echo json_encode([
                    'success' => true,
                    'data' => $inventory
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Inventario no encontrado'
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
            if (!isset($input['medication_id']) || !isset($input['quantity']) || !isset($input['unit_price'])) {
                http_response_code(400);
                echo json_encode(['error' => 'medication_id, quantity y unit_price son requeridos']);
                return;
            }

            if (!isset($input['expiration_date'])) {
                http_response_code(400);
                echo json_encode(['error' => 'expiration_date es requerido']);
                return;
            }

            $inventory = $this->inventoryModel->create($input);
            
            echo json_encode([
                'success' => true,
                'data' => $inventory,
                'message' => 'Inventario creado exitosamente'
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
            
            if (!isset($input['medication_id']) || !isset($input['quantity']) || !isset($input['unit_price'])) {
                http_response_code(400);
                echo json_encode(['error' => 'medication_id, quantity y unit_price son requeridos']);
                return;
            }

            $inventory = $this->inventoryModel->update($id, $input);
            
            if ($inventory) {
                echo json_encode([
                    'success' => true,
                    'data' => $inventory,
                    'message' => 'Inventario actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Inventario no encontrado'
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
            
            $result = $this->inventoryModel->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Inventario eliminado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Inventario no encontrado'
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

    public function addStock($id)
    {
        try {
            $this->auth->requireAuth('pharmacist');
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['quantity']) || $input['quantity'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'quantity debe ser mayor a 0']);
                return;
            }

            $user = $this->auth->requireAuth();
            $reason = $input['reason'] ?? 'Agregado de stock';
            
            $result = $this->inventoryModel->addStock($id, $input['quantity'], $user->user_id, $reason);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Stock agregado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo agregar el stock'
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

    public function removeStock($id)
    {
        try {
            $this->auth->requireAuth('pharmacist');
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['quantity']) || $input['quantity'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'quantity debe ser mayor a 0']);
                return;
            }

            $user = $this->auth->requireAuth();
            $reason = $input['reason'] ?? 'Remoción de stock';
            
            $result = $this->inventoryModel->removeStock($id, $input['quantity'], $user->user_id, $reason);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Stock removido exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo remover el stock'
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

    public function getLowStock()
    {
        try {
            $this->auth->requireAuth();
            
            $threshold = $_GET['threshold'] ?? 10;
            $inventory = $this->inventoryModel->getLowStock($threshold);
            
            echo json_encode([
                'success' => true,
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getExpiringSoon()
    {
        try {
            $this->auth->requireAuth();
            
            $days = $_GET['days'] ?? 30;
            $inventory = $this->inventoryModel->getExpiringSoon($days);
            
            echo json_encode([
                'success' => true,
                'data' => $inventory
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