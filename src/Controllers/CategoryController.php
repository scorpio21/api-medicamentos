<?php

namespace App\Controllers;

use App\Auth\JWTAuth;
use App\Models\Category;

class CategoryController
{
    private $auth;
    private $categoryModel;

    public function __construct()
    {
        $this->auth = new JWTAuth();
        $this->categoryModel = new Category();
    }

    public function index()
    {
        try {
            $this->auth->requireAuth();
            
            $categories = $this->categoryModel->getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $categories
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
            
            $category = $this->categoryModel->getById($id);
            
            if ($category) {
                echo json_encode([
                    'success' => true,
                    'data' => $category
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
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
            
            if (!isset($input['name']) || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre de la categoría es requerido']);
                return;
            }

            $category = $this->categoryModel->create($input);
            
            echo json_encode([
                'success' => true,
                'data' => $category,
                'message' => 'Categoría creada exitosamente'
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
                echo json_encode(['error' => 'El nombre de la categoría es requerido']);
                return;
            }

            $category = $this->categoryModel->update($id, $input);
            
            if ($category) {
                echo json_encode([
                    'success' => true,
                    'data' => $category,
                    'message' => 'Categoría actualizada exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
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
            
            $result = $this->categoryModel->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
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
}