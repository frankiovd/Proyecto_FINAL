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

// Obtener y validar el ID del cliente
if (!isset($_GET['client_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente no proporcionado']);
    exit();
}

$clientId = intval($_GET['client_id']);

try {
    // Obtener los planes nutricionales del cliente
    $plans = getMealPlans($clientId);
    echo json_encode($plans);
} catch (Exception $e) {
    error_log("Error al obtener planes nutricionales: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los planes nutricionales']);
}
