<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Inventory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $limit = 20, $medication_id = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $where = "WHERE i.is_active = 1";
            $params = [];

            if ($medication_id) {
                $where .= " AND i.medication_id = ?";
                $params[] = $medication_id;
            }

            $sql = "
                SELECT i.*, m.name as medication_name, m.generic_name, m.dosage_form, m.strength
                FROM inventory i 
                JOIN medications m ON i.medication_id = m.id 
                {$where}
                ORDER BY m.name, i.expiration_date 
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $inventory = $stmt->fetchAll();

            // Obtener total de registros para paginación
            $countSql = "
                SELECT COUNT(*) as total 
                FROM inventory i 
                {$where}
            ";
            $countStmt = $this->db->prepare($countSql);
            $countParams = array_slice($params, 0, -2);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];

            return [
                'data' => $inventory,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            error_log('Error getting inventory: ' . $e->getMessage());
            throw new \Exception('Error al obtener inventario');
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, m.name as medication_name, m.generic_name, m.dosage_form, m.strength
                FROM inventory i 
                JOIN medications m ON i.medication_id = m.id 
                WHERE i.id = ? AND i.is_active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting inventory by ID: ' . $e->getMessage());
            throw new \Exception('Error al obtener inventario');
        }
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO inventory (
                    medication_id, batch_number, expiration_date, quantity, 
                    unit_price, supplier, location
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['medication_id'],
                $data['batch_number'] ?? null,
                $data['expiration_date'],
                $data['quantity'],
                $data['unit_price'],
                $data['supplier'] ?? null,
                $data['location'] ?? null
            ]);

            $inventoryId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getById($inventoryId);
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error creating inventory: ' . $e->getMessage());
            throw new \Exception('Error al crear inventario');
        }
    }

    public function update($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE inventory SET 
                    medication_id = ?, batch_number = ?, expiration_date = ?, quantity = ?,
                    unit_price = ?, supplier = ?, location = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND is_active = 1
            ");

            $result = $stmt->execute([
                $data['medication_id'],
                $data['batch_number'] ?? null,
                $data['expiration_date'],
                $data['quantity'],
                $data['unit_price'],
                $data['supplier'] ?? null,
                $data['location'] ?? null,
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
            error_log('Error updating inventory: ' . $e->getMessage());
            throw new \Exception('Error al actualizar inventario');
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE inventory SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Error deleting inventory: ' . $e->getMessage());
            throw new \Exception('Error al eliminar inventario');
        }
    }

    public function addStock($id, $quantity, $userId, $reason = null)
    {
        try {
            $this->db->beginTransaction();

            // Actualizar cantidad en inventario
            $stmt = $this->db->prepare("
                UPDATE inventory SET 
                    quantity = quantity + ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$quantity, $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Registrar transacción
                $transactionStmt = $this->db->prepare("
                    INSERT INTO inventory_transactions (
                        inventory_id, user_id, transaction_type, quantity, reason
                    ) VALUES (?, ?, 'in', ?, ?)
                ");
                
                $transactionStmt->execute([$id, $userId, $quantity, $reason]);
                
                $this->db->commit();
                return true;
            }

            $this->db->rollback();
            return false;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error adding stock: ' . $e->getMessage());
            throw new \Exception('Error al agregar stock');
        }
    }

    public function removeStock($id, $quantity, $userId, $reason = null)
    {
        try {
            $this->db->beginTransaction();

            // Verificar stock disponible
            $checkStmt = $this->db->prepare("
                SELECT quantity FROM inventory WHERE id = ? AND is_active = 1
            ");
            $checkStmt->execute([$id]);
            $currentStock = $checkStmt->fetch();

            if (!$currentStock || $currentStock['quantity'] < $quantity) {
                $this->db->rollback();
                throw new \Exception('Stock insuficiente');
            }

            // Actualizar cantidad en inventario
            $stmt = $this->db->prepare("
                UPDATE inventory SET 
                    quantity = quantity - ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$quantity, $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Registrar transacción
                $transactionStmt = $this->db->prepare("
                    INSERT INTO inventory_transactions (
                        inventory_id, user_id, transaction_type, quantity, reason
                    ) VALUES (?, ?, 'out', ?, ?)
                ");
                
                $transactionStmt->execute([$id, $userId, $quantity, $reason]);
                
                $this->db->commit();
                return true;
            }

            $this->db->rollback();
            return false;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error removing stock: ' . $e->getMessage());
            throw new \Exception('Error al remover stock: ' . $e->getMessage());
        }
    }

    public function getLowStock($threshold = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, m.name as medication_name, m.generic_name
                FROM inventory i 
                JOIN medications m ON i.medication_id = m.id 
                WHERE i.quantity <= ? AND i.is_active = 1
                ORDER BY i.quantity ASC
            ");
            
            $stmt->execute([$threshold]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting low stock: ' . $e->getMessage());
            throw new \Exception('Error al obtener stock bajo');
        }
    }

    public function getExpiringSoon($days = 30)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, m.name as medication_name, m.generic_name
                FROM inventory i 
                JOIN medications m ON i.medication_id = m.id 
                WHERE i.expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) 
                AND i.expiration_date >= CURDATE()
                AND i.is_active = 1
                ORDER BY i.expiration_date ASC
            ");
            
            $stmt->execute([$days]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting expiring soon: ' . $e->getMessage());
            throw new \Exception('Error al obtener medicamentos próximos a vencer');
        }
    }

    public function getTransactions($inventoryId, $limit = 50)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT it.*, u.username, u.full_name
                FROM inventory_transactions it
                JOIN users u ON it.user_id = u.id
                WHERE it.inventory_id = ?
                ORDER BY it.transaction_date DESC
                LIMIT ?
            ");
            
            $stmt->execute([$inventoryId, $limit]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting transactions: ' . $e->getMessage());
            throw new \Exception('Error al obtener transacciones');
        }
    }
}