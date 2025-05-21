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
    
    try {
        // Get measurements from the last 12 months
        $stmt = $conn->prepare("SELECT weight, body_fat, muscle_mass, measurement_date 
                              FROM progress_measurements 
                              WHERE user_id = ? 
                              AND measurement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                              ORDER BY measurement_date ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $weight_data = [];
        $body_fat_data = [];
        $muscle_mass_data = [];
        $dates = [];
        
        while ($row = $result->fetch_assoc()) {
            $weight_data[] = $row['weight'];
            $body_fat_data[] = $row['body_fat'];
            $muscle_mass_data[] = $row['muscle_mass'];
            // Convert date to month abbreviation in Spanish
            $date = new DateTime($row['measurement_date']);
            $month = $date->format('M');
            $dates[] = strtr($month, [
                'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
                'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
            ]);
        }
        
        $stmt->close();
        $conn->close();
        
        // If no data found, return default values
        if (empty($weight_data)) {
            return [
                'weight_data' => [80],
                'body_fat_data' => [20],
                'muscle_mass_data' => [60],
                'dates' => [strtr(date('M'), [
                    'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
                    'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                    'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
                ])]
            ];
        }
        
        return [
            'weight_data' => $weight_data,
            'body_fat_data' => $body_fat_data,
            'muscle_mass_data' => $muscle_mass_data,
            'dates' => $dates
        ];
        
    } catch (Exception $e) {
        error_log("Error getting progress data: " . $e->getMessage());
        // Return default values if there's an error
        return [
            'weight_data' => [80],
            'body_fat_data' => [20],
            'muscle_mass_data' => [60],
            'dates' => [strtr(date('M'), [
                'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
                'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
            ])]
        ];
    }
}

// Get user's nutrition data
function getUserNutritionData($userId) {
    $conn = conectarDB();
    
    try {
        // Get user's nutrition goals
        $stmt = $conn->prepare("SELECT * FROM nutrition_goals WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If no goals set, use default values
        if ($result->num_rows === 0) {
            $goals = [
                'calories_target' => 2400,
                'protein_target' => 150,
                'water_target' => 3.0
            ];
            
            // Insert default goals
            $stmt = $conn->prepare("INSERT INTO nutrition_goals (user_id, calories_target, protein_target, water_target) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $userId, $goals['calories_target'], $goals['protein_target'], $goals['water_target']);
            $stmt->execute();
        } else {
            $goals = $result->fetch_assoc();
        }
        
        // Get today's nutrition logs
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT calories_consumed, protein_consumed, water_consumed FROM nutrition_logs WHERE user_id = ? AND log_date = ?");
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If no logs for today, use 0 as current values
        if ($result->num_rows === 0) {
            $current = [
                'calories_consumed' => 0,
                'protein_consumed' => 0,
                'water_consumed' => 0
            ];
        } else {
            $row = $result->fetch_assoc();
            $current = [
                'calories_consumed' => (int)$row['calories_consumed'],
                'protein_consumed' => (int)$row['protein_consumed'],
                'water_consumed' => (float)$row['water_consumed']
            ];
        }
        
        $stmt->close();
        $conn->close();
        
        return [
            'calories' => [
                'current' => $current['calories_consumed'],
                'target' => $goals['calories_target']
            ],
            'protein' => [
                'current' => $current['protein_consumed'],
                'target' => $goals['protein_target']
            ],
            'water' => [
                'current' => $current['water_consumed'],
                'target' => $goals['water_target']
            ]
        ];
    } catch (Exception $e) {
        error_log("Error getting nutrition data: " . $e->getMessage());
        // Return default values if there's an error
        return [
            'calories' => ['current' => 0, 'target' => 2400],
            'protein' => ['current' => 0, 'target' => 150],
            'water' => ['current' => 0, 'target' => 3]
        ];
    }
}

// Get user's overall progress
function getUserOverallProgress($userId) {
    $conn = conectarDB();
    // TODO: Implement actual progress calculation
    return 75; // Percentage
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
