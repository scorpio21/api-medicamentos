<?php

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