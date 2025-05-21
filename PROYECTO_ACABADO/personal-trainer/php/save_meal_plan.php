<?php
require_once 'config.php';
require_once 'nutritionix_helper.php';
require_once 'auth_helper.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Obtener y validar los datos de entrada
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['client_id']) || !isset($data['breakfast']) || !isset($data['lunch']) || !isset($data['dinner']) || !isset($data['snacks'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos del plan incompletos']);
    exit();
}

try {
    // Extraer client_id y crear el plan de comidas
    $clientId = $data['client_id'];
    unset($data['client_id']); // Remover client_id del array de datos
    
    // Guardar el plan de comidas
    $success = saveMealPlan($clientId, $data);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el plan']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}
