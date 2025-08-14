<?php

namespace App\Models;

use App\Database;
use PDO;
use Exception;

class Medicamento
{
    private $db;
    private $table = 'medicamentos';
    
    public $id;
    public $nombre;
    public $descripcion;
    public $presentacion;
    public $dosis_recomendada;
    public $stock;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Obtener todos los medicamentos
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Obtener medicamento por ID
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if ($row) {
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->presentacion = $row['presentacion'];
            $this->dosis_recomendada = $row['dosis_recomendada'];
            $this->stock = $row['stock'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }
    
    // Crear nuevo medicamento
    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (nombre, descripcion, presentacion, dosis_recomendada, stock) 
                  VALUES (:nombre, :descripcion, :presentacion, :dosis_recomendada, :stock)";
        
        $stmt = $this->db->prepare($query);
        
        // Validar datos requeridos
        if (empty($data['nombre']) || empty($data['presentacion']) || empty($data['dosis_recomendada'])) {
            throw new Exception("Faltan campos requeridos: nombre, presentacion, dosis_recomendada");
        }
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':presentacion', $data['presentacion']);
        $stmt->bindParam(':dosis_recomendada', $data['dosis_recomendada']);
        $stmt->bindParam(':stock', $data['stock']);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return $this->getById($this->id);
        }
        
        return false;
    }
    
    // Actualizar medicamento
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['nombre', 'descripcion', 'presentacion', 'dosis_recomendada', 'stock'])) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            throw new Exception("No hay campos válidos para actualizar");
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam(":$key", $params[$key]);
        }
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    // Eliminar medicamento
    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Buscar medicamentos por nombre
    public function search($nombre)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE nombre LIKE :nombre ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $searchTerm = "%$nombre%";
        $stmt->bindParam(':nombre', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Verificar si existe medicamento por nombre
    public function existsByName($nombre, $excludeId = null)
    {
        $query = "SELECT id FROM " . $this->table . " WHERE nombre = :nombre";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}