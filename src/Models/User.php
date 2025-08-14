<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $limit = 20)
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "
                SELECT id, username, email, full_name, role, is_active, created_at, updated_at
                FROM users 
                WHERE is_active = 1
                ORDER BY username 
                LIMIT ? OFFSET ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            $users = $stmt->fetchAll();

            // Obtener total de registros para paginación
            $countSql = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];

            return [
                'data' => $users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            error_log('Error getting users: ' . $e->getMessage());
            throw new \Exception('Error al obtener usuarios');
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, full_name, role, is_active, created_at, updated_at
                FROM users 
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting user by ID: ' . $e->getMessage());
            throw new \Exception('Error al obtener usuario');
        }
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // Verificar si el username o email ya existen
            $checkStmt = $this->db->prepare("
                SELECT id FROM users WHERE username = ? OR email = ?
            ");
            $checkStmt->execute([$data['username'], $data['email']]);
            
            if ($checkStmt->fetch()) {
                $this->db->rollback();
                throw new \Exception('El nombre de usuario o email ya existe');
            }

            $stmt = $this->db->prepare("
                INSERT INTO users (
                    username, email, password_hash, full_name, role
                ) VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['full_name'],
                $data['role'] ?? 'assistant'
            ]);

            $userId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getById($userId);
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error creating user: ' . $e->getMessage());
            throw new \Exception('Error al crear usuario: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $updateFields = [];
            $params = [];

            if (isset($data['full_name'])) {
                $updateFields[] = 'full_name = ?';
                $params[] = $data['full_name'];
            }

            if (isset($data['email'])) {
                $updateFields[] = 'email = ?';
                $params[] = $data['email'];
            }

            if (isset($data['role'])) {
                $updateFields[] = 'role = ?';
                $params[] = $data['role'];
            }

            if (empty($updateFields)) {
                $this->db->rollback();
                throw new \Exception('No hay campos para actualizar');
            }

            $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
            $params[] = $id;

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ? AND is_active = 1";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result && $stmt->rowCount() > 0) {
                $this->db->commit();
                return $this->getById($id);
            }

            $this->db->rollback();
            return false;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error updating user: ' . $e->getMessage());
            throw new \Exception('Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            throw new \Exception('Error al eliminar usuario');
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            $this->db->beginTransaction();

            // Verificar contraseña actual
            $stmt = $this->db->prepare("
                SELECT password_hash FROM users WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                $this->db->rollback();
                return false;
            }

            // Actualizar contraseña
            $updateStmt = $this->db->prepare("
                UPDATE users SET 
                    password_hash = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND is_active = 1
            ");
            
            $result = $updateStmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $userId
            ]);

            if ($result && $updateStmt->rowCount() > 0) {
                $this->db->commit();
                return true;
            }

            $this->db->rollback();
            return false;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Error changing password: ' . $e->getMessage());
            throw new \Exception('Error al cambiar contraseña');
        }
    }

    public function getByRole($role)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, full_name, role, is_active, created_at, updated_at
                FROM users 
                WHERE role = ? AND is_active = 1
                ORDER BY username
            ");
            $stmt->execute([$role]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting users by role: ' . $e->getMessage());
            throw new \Exception('Error al obtener usuarios por rol');
        }
    }
}