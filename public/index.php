<?php
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