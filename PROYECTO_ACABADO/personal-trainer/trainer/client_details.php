<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';
require_once '../php/dashboard_helper.php';
require_once '../php/plan_functions.php';

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    header('Location: ../login.php');
    exit();
}

// Verificar ID del cliente
if (!isset($_GET['id'])) {
    header('Location: clients.php');
    exit();
}

$clientId = intval($_GET['id']);
$trainerId = $_SESSION['usuario_id'];

// Obtener información del cliente
try {
    $conn = conectarDB();
    $stmt = $conn->prepare("SELECT u.* FROM users u 
                           JOIN user_trainers ut ON u.id = ut.user_id 
                           WHERE ut.trainer_id = ? AND u.id = ? AND ut.status = 'active'");
    $stmt->bind_param("ii", $trainerId, $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: clients.php');
        exit();
    }
    
    $client = $result->fetch_assoc();

    if (!is_array($client) || !array_key_exists('name', $client)) {
        error_log("Client data missing or invalid for client ID: $clientId");
        header('Location: clients.php?error=1');
        exit();
    }

$clientPlan = getUserActivePlan($clientId);
    $progress = getUserOverallProgress($clientId);
    $nutritionData = getUserNutritionData($clientId);
    $progressData = getUserProgressData($clientId);
    
    
} catch (Exception $e) {
    error_log("Error al obtener detalles del cliente: " . $e->getMessage());
    header('Location: clients.php?error=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Cliente - FitLife</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="../index.html" class="text-2xl font-bold text-primary font-heading">FitLife</a>
                <div class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-secondary hover:text-primary font-medium transition duration-300">Panel</a>
                    <a href="clients.php" class="text-primary font-medium transition duration-300">Clientes</a>
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="nutrition.php" class="text-secondary hover:text-primary font-medium transition duration-300">Nutrición</a>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center">
                            <?php if (isset($_SESSION['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['photo_url']); ?>" 
                                     alt="Foto de perfil" 
                                     class="h-8 w-8 rounded-full">
                            <?php endif; ?>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                        </div>
                    </div>
                    <a href="../logout.php" class="ml-4 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Client Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <?php if (isset($client['photo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($client['photo_url']); ?>" 
                             alt="Foto de perfil" 
                             class="h-16 w-16 rounded-full">
                    <?php endif; ?>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-secondary font-heading">
                            <?php echo htmlspecialchars($client['name']); ?>
                        </h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($client['email']); ?></p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="assign_plan.php?client_id=<?php echo $clientId; ?>" 
                       class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-dumbbell mr-2"></i> Asignar Plan
                    </a>
                    <a href="nutrition.php?client_id=<?php echo $clientId; ?>" 
                       class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-utensils mr-2"></i> Plan Nutricional
                    </a>
                </div>
            </div>
        </div>

        <!-- Progress Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Current Plan -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Plan Actual</h2>
                <?php if ($clientPlan): ?>
                    <div class="space-y-2">
                        <p class="font-medium"><?php echo htmlspecialchars($clientPlan['name']); ?></p>
                        <p class="text-sm text-gray-600">
                            Desde: <?php echo date('d/m/Y', strtotime($clientPlan['start_date'])); ?>
                        </p>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1"><?php echo $progress; ?>% completado</p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">Sin plan asignado</p>
                <?php endif; ?>
            </div>

            <!-- Nutrition Progress -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Nutrición</h2>
                <div class="space-y-4">
                    <!-- Calories -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Calorías</span>
                            <span><?php echo $nutritionData['calories']['current']; ?> / <?php echo $nutritionData['calories']['target']; ?> kcal</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary h-2.5 rounded-full" 
                                 style="width: <?php echo ($nutritionData['calories']['current'] / $nutritionData['calories']['target'] * 100); ?>%">
                            </div>
                        </div>
                    </div>
                    <!-- Protein -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Proteína</span>
                            <span><?php echo $nutritionData['protein']['current']; ?> / <?php echo $nutritionData['protein']['target']; ?>g</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary h-2.5 rounded-full" 
                                 style="width: <?php echo ($nutritionData['protein']['current'] / $nutritionData['protein']['target'] * 100); ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Progress -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Progreso de Peso</h2>
                <div class="space-y-2">
                    <?php 
                    $weightData = $progressData['weight_data'];
                    $lastWeight = end($weightData);
                    $previousWeight = prev($weightData);
                    $weightDiff = $lastWeight - $previousWeight;
                    ?>
                    <p class="text-3xl font-bold text-secondary"><?php echo $lastWeight; ?> kg</p>
                    <p class="text-sm <?php echo $weightDiff <= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $weightDiff <= 0 ? '↓' : '↑'; ?> 
                        <?php echo abs($weightDiff); ?> kg desde la última semana
                    </p>
                </div>
            </div>
        </div>

        <!-- Training Schedule -->
        <?php if ($clientPlan): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-secondary mb-4">Horario de Entrenamiento</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php
                $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                foreach ($days as $day):
                    $exercises = getDayTrainingPlan($clientId, $day);
                ?>
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-secondary mb-2 capitalize"><?php echo $day; ?></h3>
                    <?php if (!empty($exercises)): ?>
                        <ul class="space-y-2">
                        <?php foreach ($exercises as $exercise): ?>
                            <li class="text-sm text-gray-600">
                                • <?php echo htmlspecialchars($exercise['exercise_name']); ?>
                                <span class="text-gray-500">
                                    (<?php echo $exercise['sets']; ?>x<?php echo $exercise['reps']; ?>)
                                </span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Descanso</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Meal Plans -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-secondary">Planes Nutricionales</h2>
            </div>
            <div id="client-meal-plans" class="space-y-4">
                <?php
                require_once '../php/nutritionix_helper.php';
                $mealPlans = getMealPlans($clientId);
                
                if (!empty($mealPlans)):
                    foreach ($mealPlans as $plan):
                ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-semibold">Plan del <?php echo date('d/m/Y', strtotime($plan['created_at'])); ?></h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium mb-2">Desayuno</h4>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($plan['plan_data']['breakfast'] as $food): ?>
                                        <li><?php echo htmlspecialchars($food['food_name']); ?> - 
                                            <?php echo $food['serving_qty']; ?> <?php echo $food['serving_unit']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Almuerzo</h4>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($plan['plan_data']['lunch'] as $food): ?>
                                        <li><?php echo htmlspecialchars($food['food_name']); ?> - 
                                            <?php echo $food['serving_qty']; ?> <?php echo $food['serving_unit']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Cena</h4>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($plan['plan_data']['dinner'] as $food): ?>
                                        <li><?php echo htmlspecialchars($food['food_name']); ?> - 
                                            <?php echo $food['serving_qty']; ?> <?php echo $food['serving_unit']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Snacks</h4>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($plan['plan_data']['snacks'] as $food): ?>
                                        <li><?php echo htmlspecialchars($food['food_name']); ?> - 
                                            <?php echo $food['serving_qty']; ?> <?php echo $food['serving_unit']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p class="text-gray-500">No hay planes nutricionales asignados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
