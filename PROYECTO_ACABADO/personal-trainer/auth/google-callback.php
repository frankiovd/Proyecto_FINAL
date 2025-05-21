<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';

// Enable CORS and set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }

    // Validate required fields
    $requiredFields = ['email', 'name', 'uid', 'provider'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }

    // Connect to database
    $conn = conectarDB();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE email = ? OR firebase_uid = ?");
        $stmt->bind_param("ss", $data['email'], $data['uid']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing user
            $user = $result->fetch_assoc();
            $stmt = $conn->prepare("UPDATE users SET 
                name = ?,
                firebase_uid = ?,
                photo_url = ?,
                last_login = NOW(),
                updated_at = NOW()
                WHERE id = ?");
            $stmt->bind_param("sssi", 
                $data['name'],
                $data['uid'],
                $data['photoURL'],
                $user['id']
            );
            $stmt->execute();
            $userId = $user['id'];
            $userRole = $user['role'];
        } else {
            // Check if email domain is for trainers
            $isTrainerDomain = strpos($data['email'], '@fitlife.com') !== false;
            $defaultRole = $isTrainerDomain ? ROLE_TRAINER : ROLE_CLIENT;

            // Create new user
            $stmt = $conn->prepare("INSERT INTO users (
                email,
                name,
                firebase_uid,
                photo_url,
                auth_provider,
                role,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $stmt->bind_param("ssssss",
                $data['email'],
                $data['name'],
                $data['uid'],
                $data['photoURL'],
                $data['provider'],
                $defaultRole
            );
            $stmt->execute();
            $userId = $conn->insert_id;
            $userRole = $defaultRole;

            // If it's a trainer, create default trainer settings
            if ($isTrainerDomain) {
                $stmt = $conn->prepare("INSERT INTO trainer_settings (
                    trainer_id,
                    max_clients,
                    created_at,
                    updated_at
                ) VALUES (?, 10, NOW(), NOW())");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
            }
        }

        // Commit transaction
        $conn->commit();

        // Start session
        session_start();
        $_SESSION['usuario_id'] = $userId;
        $_SESSION['usuario'] = $data['email'];
        $_SESSION['nombre'] = $data['name'];
        $_SESSION['photo_url'] = $data['photoURL'];
        $_SESSION['role'] = $userRole;

        // Determine redirect based on role
        $redirect = '../dashboard.php';
        if ($userRole === ROLE_TRAINER) {
            $redirect = '../trainer/dashboard.php';
        }
        // Admin users will use the regular dashboard for now

        // Return success response
        echo json_encode([
            'success' => true,
            'redirect' => $redirect,
            'user' => [
                'id' => $userId,
                'email' => $data['email'],
                'name' => $data['name'],
                'role' => $userRole
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor',
        'message' => $e->getMessage()
    ]);
}
