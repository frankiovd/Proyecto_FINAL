<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Get user profile information
function getUserProfile($userId) {
    $conn = conectarDB();
    
    $query = "SELECT u.*, ur.role 
              FROM users u 
              LEFT JOIN user_roles ur ON u.id = ur.user_id 
              WHERE u.id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $profile = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $profile;
}

// Update user profile information
function updateUserProfile($userId, $data) {
    $conn = conectarDB();
    
    $query = "UPDATE users SET 
              name = ?,
              email = ?,
              phone = ?,
              updated_at = NOW()
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", 
        $data['name'],
        $data['email'],
        $data['phone'],
        $userId
    );
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Update user profile picture
function updateProfilePicture($userId, $file) {
    $targetDir = "../uploads/profile_pictures/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($fileExtension, $allowedTypes)) {
        throw new Exception('Solo se permiten imágenes JPG, JPEG y PNG');
    }
    
    $fileName = "user_" . $userId . "_" . time() . "." . $fileExtension;
    $targetFile = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $conn = conectarDB();
        
        $query = "UPDATE users SET 
                  photo_url = ?,
                  updated_at = NOW()
                  WHERE id = ?";
                  
        $stmt = $conn->prepare($query);
        $photoUrl = "uploads/profile_pictures/" . $fileName;
        $stmt->bind_param("si", $photoUrl, $userId);
        
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
    
    throw new Exception('Error al subir la imagen');
}

// Get user settings
function getUserSettings($userId) {
    $conn = conectarDB();
    
    $query = "SELECT * FROM user_settings WHERE user_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    // Default settings if none exist
    if (!$settings) {
        $settings = [
            'notifications_enabled' => true,
            'email_notifications' => true,
            'language' => 'es',
            'theme' => 'light'
        ];
    }
    
    return $settings;
}

// Update user settings
function updateUserSettings($userId, $settings) {
    $conn = conectarDB();
    
    $query = "INSERT INTO user_settings 
              (user_id, notifications_enabled, email_notifications, language, theme) 
              VALUES (?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE 
              notifications_enabled = VALUES(notifications_enabled),
              email_notifications = VALUES(email_notifications),
              language = VALUES(language),
              theme = VALUES(theme)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiss", 
        $userId,
        $settings['notifications_enabled'],
        $settings['email_notifications'],
        $settings['language'],
        $settings['theme']
    );
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Update user password
function updateUserPassword($userId, $currentPassword, $newPassword) {
    $conn = conectarDB();
    
    // Verify current password
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($currentPassword, $user['password'])) {
        throw new Exception('La contraseña actual es incorrecta');
    }
    
    // Update to new password
    $query = "UPDATE users SET 
              password = ?,
              updated_at = NOW()
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Get user statistics
function getUserStatistics($userId) {
    $conn = conectarDB();
    
    // Get training sessions stats
    $query = "SELECT 
              COUNT(*) as total_sessions,
              SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
              AVG(CASE WHEN rating IS NOT NULL THEN rating ELSE 0 END) as avg_rating
              FROM training_sessions 
              WHERE user_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    
    // Get achievements
    $query = "SELECT COUNT(*) as total_achievements 
              FROM user_achievements 
              WHERE user_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $achievements = $result->fetch_assoc();
    
    $stats['total_achievements'] = $achievements['total_achievements'];
    
    $stmt->close();
    $conn->close();
    
    return $stats;
}
?>
