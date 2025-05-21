<?php
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

    $conn->commit();

    // Start session
    session_start();
    $_SESSION['usuario_id'] = $userId;
    $_SESSION['usuario'] = $data['email'];
    $_SESSION['nombre'] = $data['name'];
    $_SESSION['photo_url'] = $data['photoURL'];
    $_SESSION['role'] = $userRole;

    // Determine redirect based on role
    $redirect = '/personal-trainer/dashboard.php';
    if ($userRole === ROLE_TRAINER) {
        $redirect = '/personal-trainer/trainer/dashboard.php';
    } elseif ($userRole === ROLE_ADMIN) {
        $redirect = '/personal-trainer/admin/dashboard.php';
    }

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
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor',
        'message' => $e->getMessage()
    ]);
}
?>
