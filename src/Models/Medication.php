<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Medication
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $limit = 20, $search = null, $category_id = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $where = "WHERE m.is_active = 1";
            $params = [];

            if ($search) {
                $where .= " AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.active_ingredient LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if ($category_id) {
                $where .= " AND m.category_id = ?";
                $params[] = $category_id;
            }

            $sql = "
                SELECT m.*, mc.name as category_name 
                FROM medications m 
                LEFT JOIN medication_categories mc ON m.category_id = mc.id 
                {$where}
                ORDER BY m.name 
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $medications = $stmt->fetchAll();

            // Obtener total de registros para paginación
            $countSql = "
                SELECT COUNT(*) as total 
                FROM medications m 
                {$where}
            ";
            $countStmt = $this->db->prepare($countSql);
            $countParams = array_slice($params, 0, -2);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];

            return [
                'data' => $medications,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            error_log('Error getting medications: ' . $e->getMessage());
            throw new \Exception('Error al obtener medicamentos');
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, mc.name as category_name 
                FROM medications m 
                LEFT JOIN medication_categories mc ON m.category_id = mc.id 
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting medication by ID: ' . $e->getMessage());
            throw new \Exception('Error al obtener medicamento');
        }
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO medications (
                    name, generic_name, category_id, active_ingredient, 
                    dosage_form, strength, manufacturer, description, 
                    side_effects, contraindications, requires_prescription
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['name'],
                $data['generic_name'] ?? null,
                $data['category_id'] ?? null,
                $data['active_ingredient'] ?? null,
                $data['dosage_form'] ?? null,
                $data['strength'] ?? null,
                $data['manufacturer'] ?? null,
                $data['description'] ?? null,
                $data['side_effects'] ?? null,
                $data['contraindications'] ?? null,
                $data['requires_prescription'] ?? false
            ]);

            $medicationId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getById($medicationId);
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error creating medication: ' . $e->getMessage());
            throw new \Exception('Error al crear medicamento');
        }
    }

    public function update($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE medications SET 
                    name = ?, generic_name = ?, category_id = ?, active_ingredient = ?,
                    dosage_form = ?, strength = ?, manufacturer = ?, description = ?,
                    side_effects = ?, contraindications = ?, requires_prescription = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND is_active = 1
            ");

            $result = $stmt->execute([
                $data['name'],
                $data['generic_name'] ?? null,
                $data['category_id'] ?? null,
                $data['active_ingredient'] ?? null,
                $data['dosage_form'] ?? null,
                $data['strength'] ?? null,
                $data['manufacturer'] ?? null,
                $data['description'] ?? null,
                $data['side_effects'] ?? null,
                $data['contraindications'] ?? null,
                $data['requires_prescription'] ?? false,
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
            error_log('Error updating medication: ' . $e->getMessage());
            throw new \Exception('Error al actualizar medicamento');
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE medications SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Error deleting medication: ' . $e->getMessage());
            throw new \Exception('Error al eliminar medicamento');
        }
    }

    public function getByCategory($categoryId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM medications 
                WHERE category_id = ? AND is_active = 1 
                ORDER BY name
            ");
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting medications by category: ' . $e->getMessage());
            throw new \Exception('Error al obtener medicamentos por categoría');
        }
    }

    public function search($query)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, mc.name as category_name 
                FROM medications m 
                LEFT JOIN medication_categories mc ON m.category_id = mc.id 
                WHERE m.is_active = 1 
                AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.active_ingredient LIKE ?)
                ORDER BY m.name
                LIMIT 50
            ");
            
            $searchTerm = "%{$query}%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error searching medications: ' . $e->getMessage());
            throw new \Exception('Error al buscar medicamentos');
        }
    }
}