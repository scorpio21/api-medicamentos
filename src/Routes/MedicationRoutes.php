<?php

namespace App\Routes;

use App\Controllers\MedicationController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class MedicationRoutes
{
    public function register(App $app): void
    {
        $app->group('/api/v1', function (RouteCollectorProxy $group) {
            // Rutas para medicamentos
            $group->group('/medications', function (RouteCollectorProxy $group) {
                // Obtener todos los medicamentos (con paginación)
                $group->get('', [MedicationController::class, 'index']);
                
                // Buscar medicamentos
                $group->get('/search', [MedicationController::class, 'search']);
                
                // Obtener medicamentos próximos a vencer
                $group->get('/expiring-soon', [MedicationController::class, 'expiringSoon']);
                
                // Obtener medicamentos con stock bajo
                $group->get('/low-stock', [MedicationController::class, 'lowStock']);
                
                // Crear nuevo medicamento
                $group->post('', [MedicationController::class, 'store']);
                
                // Rutas que requieren ID
                $group->group('/{id}', function (RouteCollectorProxy $group) {
                    // Obtener medicamento por ID
                    $group->get('', [MedicationController::class, 'show']);
                    
                    // Actualizar medicamento
                    $group->put('', [MedicationController::class, 'update']);
                    
                    // Actualizar solo la cantidad
                    $group->patch('/quantity', [MedicationController::class, 'updateQuantity']);
                    
                    // Eliminar medicamento
                    $group->delete('', [MedicationController::class, 'destroy']);
                });
            });
        });
    }
}