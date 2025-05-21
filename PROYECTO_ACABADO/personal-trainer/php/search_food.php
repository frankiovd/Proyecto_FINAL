<?php
require_once 'config.php';
require_once 'nutritionix_helper.php';
require_once 'auth_helper.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Obtener y validar los datos de entrada
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['query'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Consulta no proporcionada']);
    exit();
}

// Inicializar API de Nutritionix
$nutritionix = new NutritionixAPI('3438a51b', 'f1934d6d8683aa2cfbd47e870ef61fa1');

try {
    // Realizar búsqueda
    $results = $nutritionix->searchFood($data['query']);
    
    if (isset($results['error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al buscar alimentos']);
        exit();
    }

    // Devolver resultados
    echo json_encode($results['common'] ?? []);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}
