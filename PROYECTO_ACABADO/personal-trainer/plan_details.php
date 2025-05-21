<?php
require_once 'php/config.php';
require_once 'php/auth_helper.php';
require_once 'php/plan_functions.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Solo permitir acceso a clientes
if ($_SESSION['role'] !== ROLE_CLIENT) {
    header('Location: dashboard.php');
    exit();
}

// Verificar que se proporcionó un ID de plan
if (!isset($_GET['id'])) {
    header('Location: plans.php');
    exit();
}

$planId = (int)$_GET['id'];
$userId = $_SESSION['usuario_id'];
$plan = getPlanDetails($planId);

// Si el plan no existe, redirigir
if (!$plan) {
    header('Location: plans.php');
    exit();
}

// Procesar la selección del plan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insertar en client_plans
        $conn = conectarDB();
        $stmt = $conn->prepare("INSERT INTO client_plans (client_id, plan_id, status, start_date) 
                               VALUES (?, ?, 'active', CURDATE())");
        $stmt->bind_param("ii", $userId, $planId);
        $stmt->execute();
        
        header('Location: plans.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = "Error al seleccionar el plan. Por favor, inténtalo de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Plan - TrainSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6B35',
                        secondary: '#2E4057',
                        light: '#F7F7F7',
                        dark: '#333333',
                    },
                    fontFamily: {
                        sans: ['Montserrat', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-light">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <a href="plans.php" class="inline-flex items-center text-secondary hover:text-primary mb-6">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Planes
            </a>

            <!-- Plan Details -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-6">
                    <h1 class="text-2xl font-bold font-heading"><?php echo htmlspecialchars($plan['name']); ?></h1>
                    <p class="mt-2 text-white text-opacity-90"><?php echo htmlspecialchars($plan['description']); ?></p>
                </div>

                <div class="p-6">
                    <!-- Plan Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <h3 class="text-gray-500 text-sm mb-1">Nivel</h3>
                            <p class="font-medium text-secondary">
                            <?php 
                            $levelInSpanish = [
                                'beginner' => 'Principiante',
                                'intermediate' => 'Intermedio',
                                'advanced' => 'Avanzado'
                            ];
                            echo htmlspecialchars($levelInSpanish[$plan['difficulty_level']]); 
                            ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm mb-1">Duración</h3>
                            <p class="font-medium text-secondary">
                                <?php echo htmlspecialchars($plan['duration_weeks']); ?> semanas
                            </p>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm mb-1">Entrenador</h3>
                            <p class="font-medium text-secondary">
                                <?php echo htmlspecialchars($plan['trainer_name']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Exercises -->
                    <h2 class="text-xl font-bold text-secondary mb-4">Ejercicios del Plan</h2>
                    <div class="space-y-6">
                        <?php
                        $currentDay = '';
                        foreach ($plan['exercises'] as $exercise):
                            if ($exercise['day'] !== $currentDay):
                                if ($currentDay !== '') echo '</div>'; // Close previous day's div
                                $currentDay = $exercise['day'];
                        ?>
                        <div class="border-t border-gray-200 pt-4 first:border-0 first:pt-0">
                            <h3 class="font-bold text-primary mb-3"><?php echo ucfirst($exercise['day']); ?></h3>
                        <?php endif; ?>
                            
                            <div class="flex items-start space-x-4 mb-3">
                                <div class="flex-1">
                                    <p class="font-medium text-secondary"><?php echo htmlspecialchars($exercise['exercise_name']); ?></p>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span class="mr-4"><?php echo $exercise['sets']; ?> series</span>
                                        <span class="mr-4"><?php echo $exercise['reps']; ?> reps</span>
                                        <?php if (!empty($exercise['notes'])): ?>
                                            <span class="text-primary"><?php echo htmlspecialchars($exercise['notes']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; 
                        if ($currentDay !== '') echo '</div>'; // Close last day's div
                        ?>
                    </div>

                    <!-- Select Plan Button -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="text-center">
                            <button type="submit" class="bg-primary text-white px-8 py-3 rounded-md hover:bg-opacity-90 transition duration-300">
                                Seleccionar Este Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
