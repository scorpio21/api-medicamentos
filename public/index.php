<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Router.php';

// Configurar headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
declare(strict_types=1);

use App\Database;
use App\Controllers\MedicationController;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$database = new Database();
$database->initialize();

$controller = new MedicationController($database);

$app->get('/medicamentos', [$controller, 'listMedications']);
$app->get('/medicamentos/{id}', [$controller, 'getMedicationById']);
$app->post('/medicamentos', [$controller, 'createMedication']);
$app->put('/medicamentos/{id}', [$controller, 'updateMedication']);
$app->delete('/medicamentos/{id}', [$controller, 'deleteMedication']);

$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'name' => 'API Medicamentos',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /medicamentos',
            'GET /medicamentos/{id}',
            'POST /medicamentos',
            'PUT /medicamentos/{id}',
            'DELETE /medicamentos/{id}',
        ],
    ], JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $router = new App\Router();
    $router->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}

// Obtener la ruta de la URL
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/api/v1';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio de logs si no existe
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

try {
    // Enrutamiento de la API
    switch ($path) {
        // Autenticación
        case '/auth/login':
            if ($method === 'POST') {
                $controller = new \App\Controllers\AuthController();
                $controller->login();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/auth/refresh':
            if ($method === 'POST') {
                $controller = new \App\Controllers\AuthController();
                $controller->refresh();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/auth/profile':
            if ($method === 'GET') {
                $controller = new \App\Controllers\AuthController();
                $controller->profile();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/auth/change-password':
            if ($method === 'POST') {
                $controller = new \App\Controllers\AuthController();
                $controller->changePassword();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        // Medicamentos
        case '/medications':
            $controller = new \App\Controllers\MedicationController();
            
            if ($method === 'GET') {
                $controller->index();
            } elseif ($method === 'POST') {
                $controller->store();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case (preg_match('/^\/medications\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\MedicationController();
            $id = $matches[1];
            
            if ($method === 'GET') {
                $controller->show($id);
            } elseif ($method === 'PUT') {
                $controller->update($id);
            } elseif ($method === 'DELETE') {
                $controller->destroy($id);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/medications/search':
            if ($method === 'GET') {
                $controller = new \App\Controllers\MedicationController();
                $controller->search();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case (preg_match('/^\/medications\/category\/(\d+)$/', $path, $matches) ? true : false):
            if ($method === 'GET') {
                $controller = new \App\Controllers\MedicationController();
                $categoryId = $matches[1];
                $controller->byCategory($categoryId);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        // Inventario
        case '/inventory':
            $controller = new \App\Controllers\InventoryController();
            
            if ($method === 'GET') {
                $controller->index();
            } elseif ($method === 'POST') {
                $controller->store();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case (preg_match('/^\/inventory\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\InventoryController();
            $id = $matches[1];
            
            if ($method === 'GET') {
                $controller->show($id);
            } elseif ($method === 'PUT') {
                $controller->update($id);
            } elseif ($method === 'DELETE') {
                $controller->destroy($id);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case (preg_match('/^\/inventory\/(\d+)\/add-stock$/', $path, $matches) ? true : false):
            if ($method === 'POST') {
                $controller = new \App\Controllers\InventoryController();
                $id = $matches[1];
                $controller->addStock($id);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case (preg_match('/^\/inventory\/(\d+)\/remove-stock$/', $path, $matches) ? true : false):
            if ($method === 'POST') {
                $controller = new \App\Controllers\InventoryController();
                $id = $matches[1];
                $controller->removeStock($id);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/inventory/low-stock':
            if ($method === 'GET') {
                $controller = new \App\Controllers\InventoryController();
                $controller->getLowStock();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        case '/inventory/expiring-soon':
            if ($method === 'GET') {
                $controller = new \App\Controllers\InventoryController();
                $controller->getExpiringSoon();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        // Categorías
        case '/categories':
            if ($method === 'GET') {
                $controller = new \App\Controllers\CategoryController();
                $controller->index();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;

        // Ruta no encontrada
        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Ruta no encontrada',
                'path' => $path,
                'method' => $method
            ]);
            break;
    }

} catch (\Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Ha ocurrido un error'
    ]);
}