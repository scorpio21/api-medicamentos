<?php
/**
 * Script de prueba para la API de Control de Medicamentos
 * 
 * Este script demuestra cómo usar los diferentes endpoints de la API
 */

// Configuración
$baseUrl = 'http://localhost:8000/api/v1';
$username = 'admin';
$password = 'password';

echo "🧪 Probando API de Control de Medicamentos\n";
echo "==========================================\n\n";

// Función para hacer peticiones HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true)
    ];
}

// Función para mostrar resultados
function showResult($title, $result) {
    echo "📋 $title\n";
    echo "HTTP Code: {$result['code']}\n";
    if ($result['data']) {
        echo "Respuesta: " . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Respuesta: {$result['body']}\n";
    }
    echo "─" . str_repeat("─", 50) . "\n\n";
}

try {
    // 1. Login
    echo "🔐 1. Autenticación\n";
    $loginData = ['username' => $username, 'password' => $password];
    $loginResult = makeRequest("$baseUrl/auth/login", 'POST', $loginData);
    showResult('Login', $loginResult);
    
    if ($loginResult['code'] !== 200 || !isset($loginResult['data']['token'])) {
        echo "❌ Error en login. Verifica que la API esté ejecutándose y las credenciales sean correctas.\n";
        exit(1);
    }
    
    $token = $loginResult['data']['token'];
    echo "✅ Token obtenido: " . substr($token, 0, 50) . "...\n\n";
    
    // 2. Obtener perfil
    echo "👤 2. Obtener Perfil\n";
    $profileResult = makeRequest("$baseUrl/auth/profile", 'GET', null, $token);
    showResult('Perfil del Usuario', $profileResult);
    
    // 3. Obtener categorías
    echo "📂 3. Obtener Categorías\n";
    $categoriesResult = makeRequest("$baseUrl/categories", 'GET', null, $token);
    showResult('Lista de Categorías', $categoriesResult);
    
    // 4. Obtener medicamentos
    echo "💊 4. Obtener Medicamentos\n";
    $medicationsResult = makeRequest("$baseUrl/medications", 'GET', null, $token);
    showResult('Lista de Medicamentos', $medicationsResult);
    
    // 5. Crear un medicamento de prueba
    echo "➕ 5. Crear Medicamento de Prueba\n";
    $newMedication = [
        'name' => 'Ibuprofeno 600mg Test',
        'generic_name' => 'Ibuprofeno',
        'category_id' => 3, // Antiinflamatorios
        'active_ingredient' => 'Ibuprofeno',
        'dosage_form' => 'Tableta',
        'strength' => '600mg',
        'manufacturer' => 'Laboratorio Test',
        'description' => 'Medicamento de prueba para la API',
        'requires_prescription' => false
    ];
    
    $createMedResult = makeRequest("$baseUrl/medications", 'POST', $newMedication, $token);
    showResult('Crear Medicamento', $createMedResult);
    
    if ($createMedResult['code'] === 200 && isset($createMedResult['data']['data']['id'])) {
        $medicationId = $createMedResult['data']['data']['id'];
        echo "✅ Medicamento creado con ID: $medicationId\n\n";
        
        // 6. Crear inventario para el medicamento
        echo "📦 6. Crear Inventario\n";
        $newInventory = [
            'medication_id' => $medicationId,
            'batch_number' => 'BATCH-001',
            'expiration_date' => date('Y-m-d', strtotime('+2 years')),
            'quantity' => 500,
            'unit_price' => 0.50,
            'supplier' => 'Proveedor Test',
            'location' => 'Estante A-1'
        ];
        
        $createInvResult = makeRequest("$baseUrl/inventory", 'POST', $newInventory, $token);
        showResult('Crear Inventario', $createInvResult);
        
        if ($createInvResult['code'] === 200 && isset($createInvResult['data']['data']['id'])) {
            $inventoryId = $createInvResult['data']['data']['id'];
            echo "✅ Inventario creado con ID: $inventoryId\n\n";
            
            // 7. Agregar stock
            echo "➕ 7. Agregar Stock\n";
            $addStockData = [
                'quantity' => 100,
                'reason' => 'Prueba de la API - Agregado de stock'
            ];
            
            $addStockResult = makeRequest("$baseUrl/inventory/$inventoryId/add-stock", 'POST', $addStockData, $token);
            showResult('Agregar Stock', $addStockResult);
            
            // 8. Obtener inventario
            echo "📋 8. Obtener Inventario\n";
            $getInvResult = makeRequest("$baseUrl/inventory", 'GET', null, $token);
            showResult('Lista de Inventario', $getInvResult);
            
            // 9. Buscar medicamentos
            echo "🔍 9. Buscar Medicamentos\n";
            $searchResult = makeRequest("$baseUrl/medications/search?q=ibuprofeno", 'GET', null, $token);
            showResult('Búsqueda de Medicamentos', $searchResult);
            
            // 10. Obtener stock bajo
            echo "⚠️  10. Obtener Stock Bajo\n";
            $lowStockResult = makeRequest("$baseUrl/inventory/low-stock?threshold=1000", 'GET', null, $token);
            showResult('Stock Bajo', $lowStockResult);
            
            // 11. Obtener próximos a vencer
            echo "⏰ 11. Obtener Próximos a Vencer\n";
            $expiringResult = makeRequest("$baseUrl/inventory/expiring-soon?days=365", 'GET', null, $token);
            showResult('Próximos a Vencer', $expiringResult);
        }
    }
    
    // 12. Obtener transacciones (si hay inventario)
    echo "📊 12. Obtener Transacciones\n";
    if (isset($inventoryId)) {
        $transactionsResult = makeRequest("$baseUrl/inventory/$inventoryId", 'GET', null, $token);
        showResult('Detalle de Inventario con Transacciones', $transactionsResult);
    }
    
    echo "🎉 ¡Pruebas completadas exitosamente!\n";
    echo "La API está funcionando correctamente.\n\n";
    
    echo "📚 Próximos pasos:\n";
    echo "- Revisa la documentación completa en API_DOCUMENTATION.md\n";
    echo "- Explora más endpoints según tus necesidades\n";
    echo "- Integra la API en tu aplicación frontend\n";
    
} catch (Exception $e) {
    echo "❌ Error durante las pruebas: " . $e->getMessage() . "\n";
    echo "Verifica que la API esté ejecutándose en http://localhost:8000\n";
}