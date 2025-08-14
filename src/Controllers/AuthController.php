<?php

namespace App\Controllers;

use App\Auth\JWTAuth;
use App\Models\User;

class AuthController
{
    private $auth;
    private $userModel;

    public function __construct()
    {
        $this->auth = new JWTAuth();
        $this->userModel = new User();
    }

    public function login()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Usuario y contraseña son requeridos']);
                return;
            }

            $username = $input['username'];
            $password = $input['password'];

            $token = $this->auth->authenticate($username, $password);
            
            if ($token) {
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'message' => 'Autenticación exitosa'
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Credenciales inválidas'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    public function refresh()
    {
        try {
            $headers = getallheaders();
            $token = null;

            if (isset($headers['Authorization'])) {
                $token = str_replace('Bearer ', '', $headers['Authorization']);
            }

            if (!$token) {
                http_response_code(400);
                echo json_encode(['error' => 'Token requerido']);
                return;
            }

            $newToken = $this->auth->refreshToken($token);
            
            if ($newToken) {
                echo json_encode([
                    'success' => true,
                    'token' => $newToken,
                    'message' => 'Token renovado exitosamente'
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Token inválido o expirado'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    public function profile()
    {
        try {
            $user = $this->auth->requireAuth();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'full_name' => $user->full_name
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    public function changePassword()
    {
        try {
            $user = $this->auth->requireAuth();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Contraseña actual y nueva son requeridas']);
                return;
            }

            $result = $this->userModel->changePassword(
                $user->user_id,
                $input['current_password'],
                $input['new_password']
            );

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Contraseña cambiada exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Contraseña actual incorrecta'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }
}