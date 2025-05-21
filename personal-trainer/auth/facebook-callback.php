<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email'], $data['name'], $data['uid'])) {
        throw new Exception('Datos incompletos');
    }

    $conn = conectarDB();
    $conn->begin_transaction();

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE email = ? OR firebase_uid = ?");
    $stmt->bind_param("ss", $data['email'], $data['uid']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $userRole = $user['role'];
    } else {
        // New user - check if trainer email
        $defaultRole = strpos($data['email'], '@fitlife.com') !== false ? ROLE_TRAINER : ROLE_CLIENT;
        
        $stmt = $conn->prepare("INSERT INTO users (email, name, firebase_uid, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $data['email'], $data['name'], $data['uid'], $defaultRole);
        $stmt->execute();
        
        $userId = $conn->insert_id;
        $userRole = $defaultRole;
    }

    $conn->commit();

    // Set session
    session_start();
    $_SESSION['usuario_id'] = $userId;
    $_SESSION['usuario'] = $data['email'];
    $_SESSION['nombre'] = $data['name'];
    $_SESSION['role'] = $userRole;

    // Return response with correct relative paths
    $redirect = '../dashboard.php';
    if ($userRole === ROLE_TRAINER) {
        $redirect = '../trainer/dashboard.php';
    }
    // Admin users will use the regular dashboard for now

    echo json_encode([
        'success' => true,
        'redirect' => $redirect,
        'user' => ['id' => $userId, 'role' => $userRole]
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
