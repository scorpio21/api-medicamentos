<?php

namespace App\Controllers;

use App\Models\Medicamento;
use Exception;

class MedicamentoController
{
    private $medicamento;
    
    public function __construct()
    {
        $this->medicamento = new Medicamento();
    }
    
    // GET /api/medicamentos
    public function index()
    {
        try {
            $medicamentos = $this->medicamento->getAll();
            $this->sendResponse(200, [
                'success' => true,
                'data' => $medicamentos,
                'count' => count($medicamentos)
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error al obtener medicamentos',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // GET /api/medicamentos/{id}
    public function show($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'ID debe ser un número'
                ]);
                return;
            }
            
            $medicamento = $this->medicamento->getById($id);
            
            if (!$medicamento) {
                $this->sendResponse(404, [
                    'success' => false,
                    'message' => 'Medicamento no encontrado'
                ]);
                return;
            }
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $medicamento
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error al obtener medicamento',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // POST /api/medicamentos
    public function store()
    {
        try {
            $data = $this->getJsonInput();
            
            // Validaciones
            $validation = $this->validateMedicamento($data);
            if (!$validation['valid']) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validation['errors']
                ]);
                return;
            }
            
            // Verificar si ya existe
            if ($this->medicamento->existsByName($data['nombre'])) {
                $this->sendResponse(409, [
                    'success' => false,
                    'message' => 'Ya existe un medicamento con ese nombre'
                ]);
                return;
            }
            
            $nuevoMedicamento = $this->medicamento->create($data);
            
            if ($nuevoMedicamento) {
                $this->sendResponse(201, [
                    'success' => true,
                    'message' => 'Medicamento creado exitosamente',
                    'data' => $nuevoMedicamento
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'message' => 'Error al crear medicamento'
                ]);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error al crear medicamento',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // PUT /api/medicamentos/{id}
    public function update($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'ID debe ser un número'
                ]);
                return;
            }
            
            // Verificar si existe
            if (!$this->medicamento->getById($id)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'message' => 'Medicamento no encontrado'
                ]);
                return;
            }
            
            $data = $this->getJsonInput();
            
            // Validar datos si se proporcionan
            if (!empty($data)) {
                $validation = $this->validateMedicamento($data, true);
                if (!$validation['valid']) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'message' => 'Datos inválidos',
                        'errors' => $validation['errors']
                    ]);
                    return;
                }
                
                // Verificar nombre único si se está actualizando
                if (isset($data['nombre']) && $this->medicamento->existsByName($data['nombre'], $id)) {
                    $this->sendResponse(409, [
                        'success' => false,
                        'message' => 'Ya existe un medicamento con ese nombre'
                    ]);
                    return;
                }
            }
            
            $medicamentoActualizado = $this->medicamento->update($id, $data);
            
            if ($medicamentoActualizado) {
                $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Medicamento actualizado exitosamente',
                    'data' => $medicamentoActualizado
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'message' => 'Error al actualizar medicamento'
                ]);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error al actualizar medicamento',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // DELETE /api/medicamentos/{id}
    public function destroy($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'ID debe ser un número'
                ]);
                return;
            }
            
            // Verificar si existe
            if (!$this->medicamento->getById($id)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'message' => 'Medicamento no encontrado'
                ]);
                return;
            }
            
            if ($this->medicamento->delete($id)) {
                $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Medicamento eliminado exitosamente'
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'message' => 'Error al eliminar medicamento'
                ]);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error al eliminar medicamento',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // GET /api/medicamentos/search?nombre=termino
    public function search()
    {
        try {
            $nombre = $_GET['nombre'] ?? '';
            
            if (empty($nombre)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Parámetro nombre es requerido'
                ]);
                return;
            }
            
            $medicamentos = $this->medicamento->search($nombre);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $medicamentos,
                'count' => count($medicamentos)
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Error en la búsqueda',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function validateMedicamento($data, $partial = false)
    {
        $errors = [];
        
        // Campos requeridos solo en creación completa
        if (!$partial) {
            if (empty($data['nombre'])) {
                $errors[] = 'El nombre es requerido';
            }
            if (empty($data['presentacion'])) {
                $errors[] = 'La presentación es requerida';
            }
            if (empty($data['dosis_recomendada'])) {
                $errors[] = 'La dosis recomendada es requerida';
            }
        }
        
        // Validaciones de formato
        if (isset($data['nombre'])) {
            if (strlen($data['nombre']) < 2 || strlen($data['nombre']) > 255) {
                $errors[] = 'El nombre debe tener entre 2 y 255 caracteres';
            }
        }
        
        if (isset($data['stock'])) {
            if (!is_numeric($data['stock']) || $data['stock'] < 0) {
                $errors[] = 'El stock debe ser un número mayor o igual a 0';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    private function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}