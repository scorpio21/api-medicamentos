<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Database\Database;
use PDO;

class JWTAuth
{
    private $db;
    private $secret;
    private $expiration;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->secret = $_ENV['JWT_SECRET'] ?? 'default_secret_key';
        $this->expiration = $_ENV['JWT_EXPIRATION'] ?? 3600;
    }

    public function authenticate($username, $password)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password_hash, full_name, role, is_active 
                FROM users 
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                return $this->generateToken($user);
            }

            return false;
        } catch (\Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return false;
        }
    }

    public function generateToken($user)
    {
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'iat' => time(),
            'exp' => time() + $this->expiration
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            
            // Verificar si el usuario sigue activo en la base de datos
            $stmt = $this->db->prepare("SELECT id, is_active FROM users WHERE id = ?");
            $stmt->execute([$decoded->user_id]);
            $user = $stmt->fetch();

            if ($user && $user['is_active']) {
                return $decoded;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function refreshToken($token)
    {
        $decoded = $this->validateToken($token);
        if ($decoded) {
            $user = [
                'id' => $decoded->user_id,
                'username' => $decoded->username,
                'email' => $decoded->email,
                'role' => $decoded->role,
                'full_name' => $decoded->full_name
            ];
            return $this->generateToken($user);
        }
        return false;
    }

    public function requireAuth($role = null)
    {
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            exit;
        }

        $decoded = $this->validateToken($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido o expirado']);
            exit;
        }

        if ($role && $decoded->role !== $role && $decoded->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado. Rol requerido: ' . $role]);
            exit;
        }

        return $decoded;
    }
}