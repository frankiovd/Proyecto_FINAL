<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Get user's next training session
function getNextTrainingSession($userId) {
    $conn = conectarDB();
    // TODO: Implement actual session scheduling
    return [
        'date' => date('Y-m-d', strtotime('+1 day')),
        'time' => '18:00'
    ];
}

// Get user's completed and remaining sessions
function getUserSessionsInfo($userId) {
    $conn = conectarDB();
    // TODO: Implement actual session tracking
    return [
        'completed' => 24,
        'remaining' => 8
    ];
}

// Get user's training plan for a specific day
function getDayTrainingPlan($userId, $day) {
    $conn = conectarDB();
    
    $query = "SELECT pe.* FROM plan_exercises pe
              JOIN client_plans cp ON pe.plan_id = cp.plan_id
              WHERE cp.client_id = ? AND cp.status = 'active'
              AND pe.day = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userId, $day);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exercises = [];
    while ($row = $result->fetch_assoc()) {
        $exercises[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $exercises;
}

// Get user's progress data
function getUserProgressData($userId) {
    $conn = conectarDB();
    // TODO: Implement actual progress tracking
    return [
        'weight_data' => [80, 79.5, 78.8, 78.2, 77.5, 77, 76.5, 76, 75.8, 75.5, 75.2, 75],
        'body_fat_data' => [22, 21.5, 21, 20.5, 20, 19.5, 19, 18.7, 18.5, 18.2, 18, 17.8],
        'muscle_mass_data' => [58, 58.2, 58.5, 58.8, 59, 59.3, 59.5, 59.8, 60, 60.2, 60.5, 60.8],
        'dates' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
    ];
}

// Get user's nutrition data
function getUserNutritionData($userId) {
    $conn = conectarDB();
    // TODO: Implement actual nutrition tracking
    return [
        'calories' => [
            'current' => 1800,
            'target' => 2400
        ],
        'protein' => [
            'current' => 120,
            'target' => 150
        ],
        'water' => [
            'current' => 1.8,
            'target' => 3
        ]
    ];
}

// Get user's overall progress
function getUserOverallProgress($userId) {
    $conn = conectarDB();
    // TODO: Implement actual progress calculation
    return 75; // Percentage
}

// Get user's notifications
function getUserNotifications($userId) {
    $conn = conectarDB();
    // TODO: Implement actual notifications
    return 3; // Number of unread notifications
}

// Add client to trainer
function addClientToTrainer($trainerId, $clientId) {
    $conn = conectarDB();
    try {
        // Check if relationship already exists
        $stmt = $conn->prepare("SELECT status FROM user_trainers 
                               WHERE trainer_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trainerId, $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $current = $result->fetch_assoc();
            if ($current['status'] === 'active') {
                throw new Exception("Este cliente ya está asignado a tu lista.");
            }
            
            // Reactivate relationship if inactive
            $stmt = $conn->prepare("UPDATE user_trainers SET status = 'active' 
                                   WHERE trainer_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $trainerId, $clientId);
            $stmt->execute();
        } else {
            // Create new relationship
            $stmt = $conn->prepare("INSERT INTO user_trainers (trainer_id, user_id, status) 
                                   VALUES (?, ?, 'active')");
            $stmt->bind_param("ii", $trainerId, $clientId);
            $stmt->execute();
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error adding client to trainer: " . $e->getMessage());
        throw $e;
    }
}

// Convert English day names to Spanish
function translateDayToSpanish($englishDay) {
    $days = [
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miercoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sabado',
        'sunday' => 'domingo'
    ];
    return $days[strtolower($englishDay)] ?? $englishDay;
}

// Get default training plan structure
function getDefaultTrainingPlan() {
    return [
        'monday' => [
            'type' => 'Fuerza - Tren Superior',
            'exercises' => [
                ['name' => 'Press de banca', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s'],
                ['name' => 'Remo con barra', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s'],
                ['name' => 'Press militar', 'sets' => 3, 'reps' => '10-12', 'rest' => '60s'],
                ['name' => 'Curl de bíceps', 'sets' => 3, 'reps' => '10-12', 'rest' => '60s'],
                ['name' => 'Extensiones de tríceps', 'sets' => 3, 'reps' => '10-12', 'rest' => '60s'],
            ]
        ],
        'tuesday' => [
            'type' => 'Cardio y Core',
            'exercises' => [
                ['name' => 'Carrera continua', 'sets' => 1, 'reps' => '20 min', 'rest' => '-'],
                ['name' => 'Plancha', 'sets' => 3, 'reps' => '45s', 'rest' => '30s'],
                ['name' => 'Crunch abdominal', 'sets' => 3, 'reps' => '15', 'rest' => '30s'],
                ['name' => 'Mountain climbers', 'sets' => 3, 'reps' => '30s', 'rest' => '30s'],
            ]
        ],
        // ... Add other days similarly
    ];
}
?>
