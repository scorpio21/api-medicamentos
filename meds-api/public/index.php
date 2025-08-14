<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Router;
use App\MedicationController;

$router = new Router();
$controller = new MedicationController();

// Routes
$router->addRoute('GET', '/medications', [$controller, 'listMedications']);
$router->addRoute('POST', '/medications', [$controller, 'createMedication']);
$router->addRoute('GET', '/medications/(\d+)', [$controller, 'getMedication']);
$router->addRoute('PUT', '/medications/(\d+)', [$controller, 'updateMedication']);
$router->addRoute('DELETE', '/medications/(\d+)', [$controller, 'deleteMedication']);
$router->addRoute('POST', '/medications/(\d+)/adjust_stock', [$controller, 'adjustStock']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);