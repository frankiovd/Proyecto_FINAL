<?php
require_once 'php/config.php';
require_once 'php/auth_helper.php';
require_once 'php/dashboard_helper.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: area-cliente.html');
    exit();
}

// Redirigir según el rol del usuario
$userRole = $_SESSION['role'] ?? '';

switch ($userRole) {
    case ROLE_TRAINER:
        header('Location: trainer/dashboard.php');
        exit();
    case ROLE_CLIENT:
        // Mantener en el dashboard de cliente
        break;
    case ROLE_ADMIN:
        // Admin users will use the regular dashboard for now
        break;
    default:
        // Si no tiene rol asignado, cerrar sesión
        session_destroy();
        header('Location: login.php?error=no_role');
        exit();
}

// Obtener información del usuario
$userId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];
$activePlan = getClientActivePlan($userId);
$trainer = getClientTrainer($userId);

// Obtener datos adicionales
$nextSession = getNextTrainingSession($userId);
$sessionsInfo = getUserSessionsInfo($userId);
$progressData = getUserProgressData($userId);
$nutritionData = getUserNutritionData($userId);
$overallProgress = getUserOverallProgress($userId);
$notificationCount = getUserNotifications($userId);

// Obtener el día actual para resaltar el entrenamiento del día
$currentDay = translateDayToSpanish(strtolower(date('l')));

// Obtener el plan de entrenamiento
$trainingPlan = getDefaultTrainingPlan(); // Por ahora usamos el plan por defecto
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - TrainSmart</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="index.html" class="text-2xl font-bold text-primary font-heading">TrainSmart</a>
                <div class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-primary font-medium transition duration-300">Panel</a>
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="progress.php" class="text-secondary hover:text-primary font-medium transition duration-300">Progreso</a>
                    <a href="nutrition.php" class="text-secondary hover:text-primary font-medium transition duration-300">Nutrición</a>
                    <a href="settings.php" class="text-secondary hover:text-primary font-medium transition duration-300">Ajustes</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <?php if (isset($_SESSION['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['photo_url']); ?>" alt="Usuario" class="w-8 h-8 rounded-full">
                            <?php else: ?>
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario" class="w-8 h-8 rounded-full">
                            <?php endif; ?>
                            <span class="text-secondary font-medium"><?php echo htmlspecialchars($userName); ?></span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ajustes</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                    <a href="messages.php" class="text-secondary hover:text-primary transition duration-300 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <?php if ($notificationCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                            <?php echo $notificationCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-secondary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <a href="dashboard.php" class="block py-2 text-primary font-medium">Panel</a>
                <a href="plans.php" class="block py-2 text-secondary hover:text-primary font-medium">Planes</a>
                <a href="progress.php" class="block py-2 text-secondary hover:text-primary font-medium">Progreso</a>
                <a href="nutrition.php" class="block py-2 text-secondary hover:text-primary font-medium">Nutrición</a>
                <a href="settings.php" class="block py-2 text-secondary hover:text-primary font-medium">Ajustes</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="profile.php" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="logout.php" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-secondary font-heading mb-2">
                        Bienvenido, <?php echo htmlspecialchars($userName); ?>
                    </h1>
                    <p class="text-gray-600">Aquí tienes un resumen de tu progreso y próximas actividades.</p>
                </div>
                <?php if ($activePlan): ?>
                <div class="mt-4 md:mt-0">
                    <span class="inline-block bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-full font-medium">
                        <?php echo htmlspecialchars($activePlan['name']); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Next Session -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-calendar-check text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Próxima Sesión</p>
                        <p class="font-bold text-secondary">
                            <?php echo date('d/m/Y', strtotime($nextSession['date'])); ?>
                        </p>
                        <p class="text-gray-500 text-sm">
                            <?php echo $nextSession['time']; ?> h
                        </p>
                    </div>
                </div>
            </div>

            <!-- Trainer Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-tie text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Tu Entrenador</p>
                        <p class="font-bold text-secondary">
                            <?php echo $trainer ? htmlspecialchars($trainer['name']) : 'Sin asignar'; ?>
                        </p>
                        <?php if ($trainer): ?>
                        <p class="text-gray-500 text-sm">
                            <a href="messages.php" class="text-primary hover:underline">Enviar mensaje</a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sessions Progress -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-dumbbell text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Sesiones Completadas</p>
                        <p class="font-bold text-secondary"><?php echo $sessionsInfo['completed']; ?></p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <?php 
                            $totalSessions = $sessionsInfo['completed'] + $sessionsInfo['remaining'];
                            $progressPercentage = ($sessionsInfo['completed'] / $totalSessions) * 100;
                            ?>
                            <div class="bg-primary rounded-full h-2" style="width: <?php echo $progressPercentage; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Progress -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-chart-line text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Progreso General</p>
                        <p class="font-bold text-secondary"><?php echo $overallProgress; ?>%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-primary rounded-full h-2" style="width: <?php echo $overallProgress; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Today's Workout -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Entrenamiento de Hoy</h2>
                    </div>
                    <div class="p-6">
                        <?php if (isset($trainingPlan[$currentDay]) && !empty($trainingPlan[$currentDay]['exercises'])): ?>
                            <div class="flex items-center mb-4">
                                <div class="bg-primary bg-opacity-10 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-dumbbell text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-secondary">
                                        <?php echo ucfirst($currentDay); ?> - <?php echo $trainingPlan[$currentDay]['type']; ?>
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="py-2 px-3 text-left text-gray-500 font-medium">Ejercicio</th>
                                            <th class="py-2 px-3 text-center text-gray-500 font-medium">Series</th>
                                            <th class="py-2 px-3 text-center text-gray-500 font-medium">Repeticiones</th>
                                            <th class="py-2 px-3 text-center text-gray-500 font-medium">Descanso</th>
                                            <th class="py-2 px-3 text-center text-gray-500 font-medium">Completado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($trainingPlan[$currentDay]['exercises'] as $exercise): ?>
                                            <tr class="border-t border-gray-100">
                                                <td class="py-3 px-3"><?php echo htmlspecialchars($exercise['name']); ?></td>
                                                <td class="py-3 px-3 text-center"><?php echo $exercise['sets']; ?></td>
                                                <td class="py-3 px-3 text-center"><?php echo $exercise['reps']; ?></td>
                                                <td class="py-3 px-3 text-center"><?php echo $exercise['rest']; ?></td>
                                                <td class="py-3 px-3 text-center">
                                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-primary rounded focus:ring-primary">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-6 flex justify-between">
                                <button class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                                    <i class="fas fa-video mr-2"></i> Ver Tutoriales
                                </button>
                                <button class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                                    <i class="fas fa-check-circle mr-2"></i> Marcar como Completado
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-bed text-2xl text-primary"></i>
                                </div>
                                <h3 class="font-bold text-secondary text-xl mb-2">Día de Descanso</h3>
                                <p class="text-gray-600 mb-4">Hoy es tu día de descanso. Aprovecha para recuperarte y prepararte para tu próximo entrenamiento.</p>
                                <div class="flex justify-center">
                                    <a href="recovery.php" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                                        <i class="fas fa-heart mr-2"></i> Consejos de Recuperación
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Progress Summary -->
            <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Resumen de Progreso</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <canvas id="progressChart"></canvas>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Weight Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Peso Actual</span>
                                    <span class="font-bold text-secondary">
                                        <?php echo end($progressData['weight_data']); ?> kg
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 rounded-full h-2" style="width: 75%"></div>
                                    </div>
                                    <?php
                                    $weightChange = end($progressData['weight_data']) - $progressData['weight_data'][0];
                                    $weightChangeClass = $weightChange <= 0 ? 'text-green-500' : 'text-red-500';
                                    ?>
                                    <span class="text-sm <?php echo $weightChangeClass; ?>">
                                        <?php echo $weightChange <= 0 ? '' : '+'; echo $weightChange; ?> kg
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Body Fat Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">% Grasa Corporal</span>
                                    <span class="font-bold text-secondary">
                                        <?php echo end($progressData['body_fat_data']); ?>%
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-500 rounded-full h-2" style="width: 65%"></div>
                                    </div>
                                    <?php
                                    $fatChange = end($progressData['body_fat_data']) - $progressData['body_fat_data'][0];
                                    $fatChangeClass = $fatChange <= 0 ? 'text-green-500' : 'text-red-500';
                                    ?>
                                    <span class="text-sm <?php echo $fatChangeClass; ?>">
                                        <?php echo $fatChange <= 0 ? '' : '+'; echo $fatChange; ?>%
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Muscle Mass Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Masa Muscular</span>
                                    <span class="font-bold text-secondary">
                                        <?php echo end($progressData['muscle_mass_data']); ?> kg
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-500 rounded-full h-2" style="width: 85%"></div>
                                    </div>
                                    <?php
                                    $muscleChange = end($progressData['muscle_mass_data']) - $progressData['muscle_mass_data'][0];
                                    $muscleChangeClass = $muscleChange >= 0 ? 'text-green-500' : 'text-red-500';
                                    ?>
                                    <span class="text-sm <?php echo $muscleChangeClass; ?>">
                                        <?php echo $muscleChange >= 0 ? '+' : ''; echo $muscleChange; ?> kg
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="progress.php" class="block w-full px-4 py-2 bg-primary text-white text-center rounded-md hover:bg-opacity-90 transition duration-300">
                                Ver Progreso Completo
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Nutrition Summary -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Nutrición Hoy</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <!-- Calories -->
                            <div class="text-center">
                                <?php 
                                $caloriesPercentage = ($nutritionData['calories']['current'] / $nutritionData['calories']['target']) * 100;
                                ?>
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-primary relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-primary">
                                        <?php echo round($caloriesPercentage); ?>%
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Calorías</p>
                                <p class="font-bold text-secondary">
                                    <?php echo $nutritionData['calories']['current']; ?>/<?php echo $nutritionData['calories']['target']; ?>
                                </p>
                            </div>
                            
                            <!-- Protein -->
                            <div class="text-center">
                                <?php 
                                $proteinPercentage = ($nutritionData['protein']['current'] / $nutritionData['protein']['target']) * 100;
                                ?>
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-blue-500 relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-blue-500">
                                        <?php echo round($proteinPercentage); ?>%
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Proteínas</p>
                                <p class="font-bold text-secondary">
                                    <?php echo $nutritionData['protein']['current']; ?>/<?php echo $nutritionData['protein']['target']; ?>g
                                </p>
                            </div>
                            
                            <!-- Water -->
                            <div class="text-center">
                                <?php 
                                $waterPercentage = ($nutritionData['water']['current'] / $nutritionData['water']['target']) * 100;
                                ?>
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-green-500 relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-green-500">
                                        <?php echo round($waterPercentage); ?>%
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Agua</p>
                                <p class="font-bold text-secondary">
                                    <?php echo $nutritionData['water']['current']; ?>/<?php echo $nutritionData['water']['target']; ?>L
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="nutrition.php" class="block w-full px-4 py-2 bg-primary text-white text-center rounded-md hover:bg-opacity-90 transition duration-300">
                                Ver Plan Nutricional
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <a href="index.html" class="text-2xl font-bold text-primary font-heading">TrainSmart</a>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <div class="mt-4 md:mt-0">
                    <p class="text-gray-400">&copy; 2023 TrainSmart - Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Custom JS -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // User menu toggle
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');
        
        userMenuButton.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });
        
        // Close user menu when clicking outside
        document.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // Progress Chart
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($progressData['dates']); ?>,
                datasets: [
                    {
                        label: 'Peso (kg)',
                        data: <?php echo json_encode($progressData['weight_data']); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Grasa Corporal (%)',
                        data: <?php echo json_encode($progressData['body_fat_data']); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Masa Muscular (kg)',
                        data: <?php echo json_encode($progressData['muscle_mass_data']); ?>,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</body>
</html>
