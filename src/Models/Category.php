<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM medication_categories 
                WHERE is_active = 1 
                ORDER BY name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting categories: ' . $e->getMessage());
            throw new \Exception('Error al obtener categorías');
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM medication_categories 
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting category by ID: ' . $e->getMessage());
            throw new \Exception('Error al obtener categoría');
        }
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO medication_categories (name, description) 
                VALUES (?, ?)
            ");

            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);

            $categoryId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getById($categoryId);
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error creating category: ' . $e->getMessage());
            throw new \Exception('Error al crear categoría');
        }
    }

    public function update($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE medication_categories SET 
                    name = ?, description = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND is_active = 1
            ");

            $result = $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $id
            ]);

            if ($result && $stmt->rowCount() > 0) {
                $this->db->commit();
                return $this->getById($id);
            }

            $this->db->rollback();
            return false;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error updating category: ' . $e->getMessage());
            throw new \Exception('Error al actualizar categoría');
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE medication_categories SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Error deleting category: ' . $e->getMessage());
            throw new \Exception('Error al eliminar categoría');
        }
    }
}