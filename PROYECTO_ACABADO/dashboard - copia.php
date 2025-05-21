<?php
// Simulate a logged-in user session
// In a real application, you would check if the user is logged in
// and redirect to login page if not
$logged_in = true;

// Mock user data (in a real app, this would come from a database)
$user = [
    'name' => 'Usuario de Prueba',
    'email' => 'test@example.com',
    'plan' => 'premium',
    'start_date' => '2023-01-15',
    'next_session' => '2023-06-10 18:00',
    'trainer' => 'Carlos Martínez',
    'sessions_completed' => 24,
    'sessions_remaining' => 8,
];

// Mock training plan data
$training_plan = [
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
    'wednesday' => [
        'type' => 'Descanso Activo',
        'exercises' => [
            ['name' => 'Caminata', 'sets' => 1, 'reps' => '30 min', 'rest' => '-'],
            ['name' => 'Estiramientos', 'sets' => 1, 'reps' => '15 min', 'rest' => '-'],
        ]
    ],
    'thursday' => [
        'type' => 'Fuerza - Tren Inferior',
        'exercises' => [
            ['name' => 'Sentadillas', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s'],
            ['name' => 'Peso muerto', 'sets' => 4, 'reps' => '8-10', 'rest' => '90s'],
            ['name' => 'Zancadas', 'sets' => 3, 'reps' => '10 por pierna', 'rest' => '60s'],
            ['name' => 'Elevaciones de gemelos', 'sets' => 3, 'reps' => '15', 'rest' => '60s'],
        ]
    ],
    'friday' => [
        'type' => 'HIIT',
        'exercises' => [
            ['name' => 'Burpees', 'sets' => 4, 'reps' => '30s', 'rest' => '30s'],
            ['name' => 'Saltos al cajón', 'sets' => 4, 'reps' => '30s', 'rest' => '30s'],
            ['name' => 'Jumping jacks', 'sets' => 4, 'reps' => '30s', 'rest' => '30s'],
            ['name' => 'Sprints', 'sets' => 4, 'reps' => '30s', 'rest' => '30s'],
        ]
    ],
    'saturday' => [
        'type' => 'Movilidad y Flexibilidad',
        'exercises' => [
            ['name' => 'Yoga', 'sets' => 1, 'reps' => '45 min', 'rest' => '-'],
            ['name' => 'Estiramientos', 'sets' => 1, 'reps' => '15 min', 'rest' => '-'],
        ]
    ],
    'sunday' => [
        'type' => 'Descanso',
        'exercises' => []
    ],
];

// Mock progress data for charts
$weight_data = [80, 79.5, 78.8, 78.2, 77.5, 77, 76.5, 76, 75.8, 75.5, 75.2, 75];
$body_fat_data = [22, 21.5, 21, 20.5, 20, 19.5, 19, 18.7, 18.5, 18.2, 18, 17.8];
$muscle_mass_data = [58, 58.2, 58.5, 58.8, 59, 59.3, 59.5, 59.8, 60, 60.2, 60.5, 60.8];
$dates = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

// Get current day for highlighting in the training plan
$current_day = strtolower(date('l'));
switch($current_day) {
    case 'monday': $current_day = 'monday'; break;
    case 'tuesday': $current_day = 'tuesday'; break;
    case 'wednesday': $current_day = 'wednesday'; break;
    case 'thursday': $current_day = 'thursday'; break;
    case 'friday': $current_day = 'friday'; break;
    case 'saturday': $current_day = 'saturday'; break;
    case 'sunday': $current_day = 'sunday'; break;
    default: $current_day = 'monday';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - FitLife Entrenamiento Personal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
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
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="index.html" class="text-2xl font-bold text-primary font-heading">FitLife</a>
                <div class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-primary font-medium transition duration-300">Panel</a>
                    <a href="dashboard.php?section=plan" class="text-secondary hover:text-primary font-medium transition duration-300">Mi Plan</a>
                    <a href="dashboard.php?section=progress" class="text-secondary hover:text-primary font-medium transition duration-300">Progreso</a>
                    <a href="dashboard.php?section=nutrition" class="text-secondary hover:text-primary font-medium transition duration-300">Nutrición</a>
                    <a href="dashboard.php?section=settings" class="text-secondary hover:text-primary font-medium transition duration-300">Ajustes</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario" class="w-8 h-8 rounded-full">
                            <span class="text-secondary font-medium"><?php echo explode(' ', $user['name'])[0]; ?></span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden">
                            <a href="dashboard.php?section=profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="dashboard.php?section=settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ajustes</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="index.html" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                    <a href="dashboard.php?section=messages" class="text-secondary hover:text-primary transition duration-300 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
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
                <a href="dashboard.php?section=plan" class="block py-2 text-secondary hover:text-primary font-medium">Mi Plan</a>
                <a href="dashboard.php?section=progress" class="block py-2 text-secondary hover:text-primary font-medium">Progreso</a>
                <a href="dashboard.php?section=nutrition" class="block py-2 text-secondary hover:text-primary font-medium">Nutrición</a>
                <a href="dashboard.php?section=settings" class="block py-2 text-secondary hover:text-primary font-medium">Ajustes</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="dashboard.php?section=profile" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="index.html" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Bienvenido, <?php echo explode(' ', $user['name'])[0]; ?></h1>
                    <p class="text-gray-600">Aquí tienes un resumen de tu progreso y próximas actividades.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-block bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-full font-medium">
                        Plan <?php echo ucfirst($user['plan']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-calendar-check text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Próxima Sesión</p>
                        <p class="font-bold text-secondary"><?php echo date('d/m/Y', strtotime($user['next_session'])); ?></p>
                        <p class="text-gray-500 text-sm"><?php echo date('H:i', strtotime($user['next_session'])); ?> h</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-tie text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Tu Entrenador</p>
                        <p class="font-bold text-secondary"><?php echo $user['trainer']; ?></p>
                        <p class="text-gray-500 text-sm">
                            <a href="dashboard.php?section=messages" class="text-primary hover:underline">Enviar mensaje</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-dumbbell text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Sesiones Completadas</p>
                        <p class="font-bold text-secondary"><?php echo $user['sessions_completed']; ?></p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-primary rounded-full h-2" style="width: <?php echo ($user['sessions_completed'] / ($user['sessions_completed'] + $user['sessions_remaining'])) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-primary bg-opacity-10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-chart-line text-xl text-primary"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Progreso General</p>
                        <p class="font-bold text-secondary">75%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-primary rounded-full h-2" style="width: 75%"></div>
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
                    <div class="bg-secondary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Entrenamiento de Hoy</h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($training_plan[$current_day]['exercises'])): ?>
                            <div class="flex items-center mb-4">
                                <div class="bg-primary bg-opacity-10 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-dumbbell text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-secondary"><?php echo ucfirst($current_day); ?> - <?php echo $training_plan[$current_day]['type']; ?></h3>
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
                                        <?php foreach ($training_plan[$current_day]['exercises'] as $exercise): ?>
                                            <tr class="border-t border-gray-100">
                                                <td class="py-3 px-3"><?php echo $exercise['name']; ?></td>
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
                                <button class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-opacity-90 transition duration-300">
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
                                    <a href="dashboard.php?section=recovery" class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-opacity-90 transition duration-300">
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
                    <div class="bg-secondary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Resumen de Progreso</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <canvas id="progressChart" width="400" height="250"></canvas>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Peso Actual</span>
                                    <span class="font-bold text-secondary"><?php echo end($weight_data); ?> kg</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 rounded-full h-2" style="width: 75%"></div>
                                    </div>
                                    <span class="text-sm text-green-500">-5 kg</span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">% Grasa Corporal</span>
                                    <span class="font-bold text-secondary"><?php echo end($body_fat_data); ?>%</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-500 rounded-full h-2" style="width: 65%"></div>
                                    </div>
                                    <span class="text-sm text-green-500">-4.2%</span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Masa Muscular</span>
                                    <span class="font-bold text-secondary"><?php echo end($muscle_mass_data); ?> kg</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-500 rounded-full h-2" style="width: 85%"></div>
                                    </div>
                                    <span class="text-sm text-green-500">+2.8 kg</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="dashboard.php?section=progress" class="block w-full px-4 py-2 bg-primary text-white text-center rounded-md hover:bg-opacity-90 transition duration-300">
                                Ver Progreso Completo
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Nutrition Summary -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-secondary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Nutrición Hoy</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center">
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-primary relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-primary">75%</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Calorías</p>
                                <p class="font-bold text-secondary">1,800/2,400</p>
                            </div>
                            <div class="text-center">
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-blue-500 relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-blue-500">80%</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Proteínas</p>
                                <p class="font-bold text-secondary">120/150g</p>
                            </div>
                            <div class="text-center">
                                <div class="inline-block w-16 h-16 rounded-full border-4 border-green-500 relative">
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-green-500">60%</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Agua</p>
                                <p class="font-bold text-secondary">1.8/3L</p>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="dashboard.php?section=nutrition" class="block w-full px-4 py-2 bg-secondary text-white text-center rounded-md hover:bg-opacity-90 transition duration-300">
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
                    <a href="index.html" class="text-2xl font-bold text-primary font-heading">FitLife</a>
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
                    <p class="text-gray-400">&copy; 2023 FitLife - Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        const progressChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [
                    {
                        label: 'Peso (kg)',
                        data: <?php echo json_encode($weight_data); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Grasa Corporal (%)',
                        data: <?php echo json_encode($body_fat_data); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Masa Muscular (kg)',
                        data: <?php echo json_encode($muscle_mass_data); ?>,
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
