<?php

namespace App;

use App\Controllers\MedicamentoController;
use App\Database;

class Router
{
    private $routes = [];
    
    public function __construct()
    {
        // Inicializar base de datos y crear tablas si no existen
        try {
            Database::getInstance()->createTables();
        } catch (Exception $e) {
            // Silenciar error si las tablas ya existen
        }
        
        $this->setupRoutes();
    }
    
    private function setupRoutes()
    {
        // Rutas para medicamentos
        $this->addRoute('GET', '/api/medicamentos', 'MedicamentoController', 'index');
        $this->addRoute('GET', '/api/medicamentos/search', 'MedicamentoController', 'search');
        $this->addRoute('GET', '/api/medicamentos/{id}', 'MedicamentoController', 'show');
        $this->addRoute('POST', '/api/medicamentos', 'MedicamentoController', 'store');
        $this->addRoute('PUT', '/api/medicamentos/{id}', 'MedicamentoController', 'update');
        $this->addRoute('DELETE', '/api/medicamentos/{id}', 'MedicamentoController', 'destroy');
        
        // Ruta de información de la API
        $this->addRoute('GET', '/api', 'Router', 'apiInfo');
    }
    
    private function addRoute($method, $path, $controller, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Normalizar path
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                $this->callController($route['controller'], $route['action'], $params);
                return;
            }
        }
        
        // Ruta no encontrada
        $this->sendNotFound();
    }
    
    private function matchPath($routePath, $requestPath)
    {
        // Convertir {id} a regex
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath)
    {
        $params = [];
        
        // Extraer parámetros de la URL
        $routeParts = explode('/', $routePath);
        $requestParts = explode('/', $requestPath);
        
        for ($i = 0; $i < count($routeParts); $i++) {
            if (isset($routeParts[$i]) && preg_match('/\{(\w+)\}/', $routeParts[$i], $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $requestParts[$i] ?? null;
            }
        }
        
        return $params;
    }
    
    private function callController($controllerName, $action, $params = [])
    {
        if ($controllerName === 'Router') {
            $this->$action();
            return;
        }
        
        $controllerClass = "App\\Controllers\\$controllerName";
        
        if (!class_exists($controllerClass)) {
            $this->sendError(500, 'Controlador no encontrado');
            return;
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $action)) {
            $this->sendError(500, 'Método no encontrado');
            return;
        }
        
        // Pasar parámetros al método
        if (!empty($params)) {
            call_user_func_array([$controller, $action], array_values($params));
        } else {
            $controller->$action();
        }
    }
    
    public function apiInfo()
    {
        $info = [
            'name' => 'API Control de Medicamentos',
            'version' => '1.0.0',
            'description' => 'API REST para el control y gestión de medicamentos',
            'endpoints' => [
                'GET /api/medicamentos' => 'Obtener todos los medicamentos',
                'GET /api/medicamentos/{id}' => 'Obtener medicamento por ID',
                'GET /api/medicamentos/search?nombre=texto' => 'Buscar medicamentos por nombre',
                'POST /api/medicamentos' => 'Crear nuevo medicamento',
                'PUT /api/medicamentos/{id}' => 'Actualizar medicamento',
                'DELETE /api/medicamentos/{id}' => 'Eliminar medicamento'
            ],
            'campos_medicamento' => [
                'nombre' => 'string (requerido, único)',
                'descripcion' => 'string (opcional)',
                'presentacion' => 'string (requerido)',
                'dosis_recomendada' => 'string (requerido)',
                'stock' => 'integer (opcional, default: 0)'
            ]
        ];
        
        http_response_code(200);
        echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    private function sendNotFound()
    {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'available_endpoints' => [
                'GET /api',
                'GET /api/medicamentos',
                'GET /api/medicamentos/{id}',
                'GET /api/medicamentos/search',
                'POST /api/medicamentos',
                'PUT /api/medicamentos/{id}',
                'DELETE /api/medicamentos/{id}'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    private function sendError($code, $message)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}