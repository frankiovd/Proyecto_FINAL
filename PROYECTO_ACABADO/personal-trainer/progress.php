<?php
require_once 'php/config.php';
require_once 'php/auth_helper.php';
require_once 'php/dashboard_helper.php';

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

// Obtener datos del usuario
$userId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];
$progressData = getUserProgressData($userId);
$notificationCount = getUserNotifications($userId);

// Procesar el formulario de registro de progreso
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_progress'])) {
    try {
        $conn = conectarDB();
        
        // Verificar si ya existe un registro para esta fecha
        $stmt = $conn->prepare("SELECT id FROM progress_measurements WHERE user_id = ? AND measurement_date = ?");
        $stmt->bind_param("is", $userId, $_POST['measurement_date']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar registro existente
            $stmt = $conn->prepare("UPDATE progress_measurements SET 
                                  weight = ?,
                                  body_fat = ?,
                                  muscle_mass = ?
                                  WHERE user_id = ? AND measurement_date = ?");
            $stmt->bind_param("dddis", 
                $_POST['weight'],
                $_POST['body_fat'],
                $_POST['muscle_mass'],
                $userId,
                $_POST['measurement_date']
            );
        } else {
            // Insertar nuevo registro
            $stmt = $conn->prepare("INSERT INTO progress_measurements 
                                  (user_id, weight, body_fat, muscle_mass, measurement_date)
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iddds", 
                $userId,
                $_POST['weight'],
                $_POST['body_fat'],
                $_POST['muscle_mass'],
                $_POST['measurement_date']
            );
        }
        
        if ($stmt->execute()) {
            $message = "Medidas registradas correctamente";
            $messageType = "success";
            // Refresh progress data after successful update
            $progressData = getUserProgressData($userId);
        } else {
            throw new Exception("Error al guardar las medidas");
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Progreso - TrainSmart</title>
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
                    <a href="dashboard.php" class="text-secondary hover:text-primary font-medium transition duration-300">Panel</a>
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="progress.php" class="text-primary font-medium transition duration-300">Progreso</a>
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
                <a href="dashboard.php" class="block py-2 text-secondary hover:text-primary font-medium">Panel</a>
                <a href="plans.php" class="block py-2 text-secondary hover:text-primary font-medium">Planes</a>
                <a href="progress.php" class="block py-2 text-primary font-medium">Progreso</a>
                <a href="nutrition.php" class="block py-2 text-secondary hover:text-primary font-medium">Nutrición</a>
                <a href="settings.php" class="block py-2 text-secondary hover:text-primary font-medium">Ajustes</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="profile.php" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="logout.php" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Mi Progreso</h1>
            <p class="text-gray-600">Seguimiento detallado de tus métricas y objetivos.</p>
        </div>

        <!-- Log Progress Form -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Registrar Medidas</h2>
            </div>
            <?php if ($message): ?>
            <div class="p-4 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Peso (kg)</label>
                            <input type="number" id="weight" name="weight" min="30" max="200" step="0.1" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                        <div>
                            <label for="body_fat" class="block text-sm font-medium text-gray-700 mb-1">Grasa Corporal (%)</label>
                            <input type="number" id="body_fat" name="body_fat" min="3" max="50" step="0.1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                        <div>
                            <label for="muscle_mass" class="block text-sm font-medium text-gray-700 mb-1">Masa Muscular (kg)</label>
                            <input type="number" id="muscle_mass" name="muscle_mass" min="20" max="100" step="0.1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="measurement_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="measurement_date" name="measurement_date" 
                               value="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                               required>
                    </div>
                    <div>
                        <button type="submit" name="log_progress" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Progress Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Main Progress Chart -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-4">
                    <h2 class="text-xl font-bold font-heading">Evolución General</h2>
                </div>
                <div class="p-6">
                    <canvas id="mainProgressChart"></canvas>
                </div>
            </div>

            <!-- Body Composition -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-4">
                    <h2 class="text-xl font-bold font-heading">Composición Corporal</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
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
                                    <div class="bg-primary rounded-full h-2" style="width: 75%"></div>
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
                                    <div class="bg-primary rounded-full h-2" style="width: 65%"></div>
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
                                    <div class="bg-primary rounded-full h-2" style="width: 85%"></div>
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
                </div>
            </div>
        </div>

        <!-- Progress Details -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Training Progress -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-4">
                    <h2 class="text-xl font-bold font-heading">Progreso de Entrenamiento</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600 mb-2">Sesiones Completadas</p>
                            <div class="text-3xl font-bold text-secondary">24</div>
                            <div class="text-sm text-green-500">+3 esta semana</div>
                        </div>
                        <div>
                            <p class="text-gray-600 mb-2">Asistencia</p>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: 85%"></div>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">85% de asistencia</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals Progress -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-4">
                    <h2 class="text-xl font-bold font-heading">Objetivos</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Peso Objetivo</span>
                                <span>70 kg</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: 75%"></div>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">75% completado</div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">% Grasa Objetivo</span>
                                <span>15%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: 60%"></div>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">60% completado</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Records -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-primary text-white p-4">
                    <h2 class="text-xl font-bold font-heading">Records Personales</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Press de Banca</p>
                                <p class="text-sm text-gray-500">80 kg</p>
                            </div>
                            <span class="text-green-500 text-sm">+5 kg</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Sentadilla</p>
                                <p class="text-sm text-gray-500">120 kg</p>
                            </div>
                            <span class="text-green-500 text-sm">+10 kg</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Peso Muerto</p>
                                <p class="text-sm text-gray-500">140 kg</p>
                            </div>
                            <span class="text-green-500 text-sm">+15 kg</span>
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
        const ctx = document.getElementById('mainProgressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($progressData['dates']); ?>,
                datasets: [
                    {
                        label: 'Peso (kg)',
                        data: <?php echo json_encode($progressData['weight_data']); ?>,
                        borderColor: '#FF6B35',
                        backgroundColor: 'rgba(255, 107, 53, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Grasa Corporal (%)',
                        data: <?php echo json_encode($progressData['body_fat_data']); ?>,
                        borderColor: '#FF6B35',
                        backgroundColor: 'rgba(255, 107, 53, 0.1)',
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Masa Muscular (kg)',
                        data: <?php echo json_encode($progressData['muscle_mass_data']); ?>,
                        borderColor: '#FF6B35',
                        backgroundColor: 'rgba(255, 107, 53, 0.1)',
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
