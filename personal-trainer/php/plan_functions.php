<?php
require_once 'config.php';

/**
 * Create a new training plan
 */
function createTrainingPlan($trainerId, $data) {
    $conn = conectarDB();
    
    try {
        $conn->begin_transaction();
        
        // Insert basic plan info
        $stmt = $conn->prepare("INSERT INTO training_plans (
            trainer_id,
            name,
            description,
            difficulty_level,
            duration_weeks,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->bind_param("isssi",
            $trainerId,
            $data['name'],
            $data['description'],
            $data['difficulty_level'],
            $data['duration_weeks']
        );
        
        $stmt->execute();
        $planId = $conn->insert_id;
        
        // Insert exercises if provided
        if (isset($data['exercises']) && is_array($data['exercises'])) {
            $stmt = $conn->prepare("INSERT INTO plan_exercises (
                plan_id,
                exercise_name,
                sets,
                reps,
                day,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($data['exercises'] as $exercise) {
                $stmt->bind_param("isssss",
                    $planId,
                    $exercise['name'],
                    $exercise['sets'],
                    $exercise['reps'],
                    $exercise['day'],
                    $exercise['notes']
                );
                $stmt->execute();
            }
        }
        
        $conn->commit();
        return $planId;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get plan details including exercises
 */
function getPlanDetails($planId) {
    $conn = conectarDB();
    
    try {
        // Get plan info
        $stmt = $conn->prepare("SELECT p.*, u.name as trainer_name 
                               FROM training_plans p
                               INNER JOIN users u ON p.trainer_id = u.id
                               WHERE p.id = ?");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        
        if (!$plan) {
            return null;
        }
        
        // Get exercises
        $stmt = $conn->prepare("SELECT * FROM plan_exercises WHERE plan_id = ? ORDER BY day ASC");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plan['exercises'] = [];
        while ($exercise = $result->fetch_assoc()) {
            $plan['exercises'][] = $exercise;
        }
        
        return $plan;
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get client's progress in a plan
 */
function getClientPlanProgress($clientId, $planId) {
    $conn = conectarDB();
    
    try {
        // Get total exercises
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM plan_exercises WHERE plan_id = ?");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];
        
        // Get completed exercises
        $stmt = $conn->prepare("SELECT COUNT(*) as completed 
                               FROM exercise_progress 
                               WHERE client_id = ? AND plan_id = ? AND status = 'completed'");
        $stmt->bind_param("ii", $clientId, $planId);
        $stmt->execute();
        $completed = $stmt->get_result()->fetch_assoc()['completed'];
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? ($completed / $total) * 100 : 0
        ];
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Mark exercise as completed
 */
function markExerciseCompleted($clientId, $planId, $exerciseId) {
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("INSERT INTO exercise_progress (
            client_id, plan_id, exercise_id, status, completed_at
        ) VALUES (?, ?, ?, 'completed', NOW())
        ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()");
        
        $stmt->bind_param("iii", $clientId, $planId, $exerciseId);
        return $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get user's active training plan
 */
function getUserActivePlan($userId) {
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("SELECT p.*, cp.start_date 
                               FROM client_plans cp
                               INNER JOIN training_plans p ON cp.plan_id = p.id
                               WHERE cp.client_id = ? AND cp.status = 'active'
                               LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        
        if (!$plan) {
            return null;
        }
        
        // Get exercises for the plan
        $stmt = $conn->prepare("SELECT * FROM plan_exercises WHERE plan_id = ? ORDER BY day ASC");
        $stmt->bind_param("i", $plan['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plan['exercises'] = [];
        while ($exercise = $result->fetch_assoc()) {
            $plan['exercises'][] = $exercise;
        }
        
        return $plan;
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get all available training plans
 */
function getAllTrainingPlans() {
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("SELECT p.*, u.name as trainer_name 
                               FROM training_plans p
                               INNER JOIN users u ON p.trainer_id = u.id
                               WHERE p.status = 'active'
                               ORDER BY p.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plans = [];
        while ($plan = $result->fetch_assoc()) {
            $plans[] = $plan;
        }
        
        return $plans;
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Delete a training plan and its exercises
 */
function deletePlan($planId, $trainerId) {
    $conn = conectarDB();
    
    try {
        $conn->begin_transaction();
        
        // Verify plan ownership
        $stmt = $conn->prepare("SELECT id FROM training_plans WHERE id = ? AND trainer_id = ?");
        $stmt->bind_param("ii", $planId, $trainerId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Plan not found or unauthorized");
        }
        
        // Delete plan exercises
        $stmt = $conn->prepare("DELETE FROM plan_exercises WHERE plan_id = ?");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        
        // Delete plan
        $stmt = $conn->prepare("DELETE FROM training_plans WHERE id = ?");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get plan details for editing
 */
function getPlanForEdit($planId, $trainerId) {
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("SELECT p.* FROM training_plans p 
                               WHERE p.id = ? AND p.trainer_id = ?");
        $stmt->bind_param("ii", $planId, $trainerId);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();
        
        if (!$plan) {
            throw new Exception("Plan not found or unauthorized");
        }
        
        // Get exercises
        $stmt = $conn->prepare("SELECT * FROM plan_exercises WHERE plan_id = ?");
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plan['exercises'] = [];
        while ($exercise = $result->fetch_assoc()) {
            $plan['exercises'][] = $exercise;
        }
        
        return $plan;
    } catch (Exception $e) {
        throw $e;
    }
}
?>
