<?php

use Slim\Factory\AppFactory;
use DI\Container;
use App\Routes\MedicationRoutes;
use App\Middleware\CorsMiddleware;
use App\Middleware\ErrorMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar el contenedor de dependencias
$container = new Container();

// Configurar la aplicación Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// Agregar middleware
$app->add(new CorsMiddleware());
$app->add(new ErrorMiddleware());

// Configurar rutas
$medicationRoutes = new MedicationRoutes();
$medicationRoutes->register($app);

// Ejecutar la aplicación
$app->run();