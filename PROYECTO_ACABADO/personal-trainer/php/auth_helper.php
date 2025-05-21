<?php
// Roles de usuario
define('ROLE_CLIENT', 'client');
define('ROLE_TRAINER', 'trainer');
define('ROLE_ADMIN', 'admin');

// Obtener el plan activo del cliente
function getClientActivePlan($userId) {
    $conn = conectarDB();
    
    $query = "SELECT cp.*, tp.name, tp.description, tp.difficulty_level, tp.duration_weeks, 
              u.name as trainer_name 
              FROM client_plans cp 
              JOIN training_plans tp ON cp.plan_id = tp.id 
              LEFT JOIN users u ON tp.trainer_id = u.id 
              WHERE cp.client_id = ? AND cp.status = 'active'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $plan = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $plan;
}

// Obtener el entrenador asignado al cliente
function getClientTrainer($userId) {
    $conn = conectarDB();
    
    $query = "SELECT u.* FROM users u 
              JOIN user_trainers ut ON u.id = ut.trainer_id 
              WHERE ut.user_id = ? AND ut.status = 'active'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trainer = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $trainer;
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Verificar si el usuario tiene un rol específico
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Verificar si el usuario es un entrenador
function isTrainer() {
    return hasRole(ROLE_TRAINER);
}

// Obtener los clientes de un entrenador
function getTrainerClients($trainerId) {
    $conn = conectarDB();
    
    $query = "SELECT u.* FROM users u 
              JOIN user_trainers ut ON u.id = ut.user_id 
              WHERE ut.trainer_id = ? AND ut.status = 'active'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clients = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $clients;
}

// Obtener los planes de un entrenador
function getTrainerPlans($trainerId) {
    $conn = conectarDB();
    
    $query = "SELECT * FROM training_plans WHERE trainer_id = ? AND status = 'active'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $plans = [];
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $plans;
}

// Obtener el ID del usuario actual
function getCurrentUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

// Obtener el nombre del usuario actual
function getCurrentUserName() {
    return $_SESSION['nombre'] ?? null;
}

// Obtener el email del usuario actual
function getCurrentUserEmail() {
    return $_SESSION['usuario'] ?? null;
}

// Agregar un cliente a un entrenador
function addClientToTrainer($trainerId, $clientId) {
    $conn = conectarDB();
    
    // Verificar si ya existe una relación activa
    $checkQuery = "SELECT user_id, trainer_id FROM user_trainers WHERE trainer_id = ? AND user_id = ? AND status = 'active'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $trainerId, $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        throw new Exception("Este cliente ya está asignado a tu lista.");
    }
    
    // Desactivar relaciones anteriores del cliente con otros entrenadores
    $updateQuery = "UPDATE user_trainers SET status = 'inactive' WHERE user_id = ? AND status = 'active'";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    
    // Crear nueva relación
    $insertQuery = "INSERT INTO user_trainers (trainer_id, user_id, status) VALUES (?, ?, 'active')";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $trainerId, $clientId);
    $stmt->execute();
    
    if ($stmt->affected_rows <= 0) {
        $stmt->close();
        $conn->close();
        throw new Exception("Error al agregar el cliente.");
    }
    
    $stmt->close();
    $conn->close();
}

// Obtener las notificaciones del usuario
function getUserNotifications($userId) {
    // Por ahora devolvemos 0 notificaciones mientras se implementa el sistema completo
    return 0;
}
?>
