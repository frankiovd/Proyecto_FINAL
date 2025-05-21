<?php
require_once 'php/config.php';
require_once 'php/auth_helper.php';
require_once 'php/dashboard_helper.php';
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

// Obtener datos del usuario
$userId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];
$activePlan = getClientActivePlan($userId);
$trainer = getClientTrainer($userId);
$notificationCount = getUserNotifications($userId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Planes - TrainSmart</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="index.html" class="text-2xl font-bold text-primary font-heading">TrainSmart</a>
                <div class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-secondary hover:text-primary font-medium transition duration-300">Panel</a>
                    <a href="plans.php" class="text-primary font-medium transition duration-300">Planes</a>
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
                <a href="dashboard.php" class="block py-2 text-secondary hover:text-primary font-medium">Panel</a>
                <a href="plans.php" class="block py-2 text-primary font-medium">Planes</a>
                <a href="progress.php" class="block py-2 text-secondary hover:text-primary font-medium">Progreso</a>
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
            <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Mis Planes</h1>
            <p class="text-gray-600">Aquí puedes ver y gestionar tus planes de entrenamiento.</p>
        </div>

        <?php if ($activePlan): ?>
        <!-- Active Plan -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Plan Activo</h2>
            </div>
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="font-bold text-primary text-lg mb-1"><?php echo htmlspecialchars($activePlan['name']); ?></h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($activePlan['description']); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            Activo
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Nivel</p>
                        <p class="font-medium text-secondary">
                            <?php echo ucfirst(htmlspecialchars($activePlan['difficulty_level'])); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Duración</p>
                        <p class="font-medium text-secondary">
                            <?php echo htmlspecialchars($activePlan['duration_weeks']); ?> semanas
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Entrenador</p>
                        <p class="font-medium text-secondary">
                            <?php echo htmlspecialchars($activePlan['trainer_name']); ?>
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-bold text-secondary mb-4">Progreso del Plan</h4>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div class="bg-primary rounded-full h-2" style="width: 45%"></div>
                    </div>
                    <p class="text-sm text-gray-600">Semana 6 de 12</p>
                </div>
            </div>
        </div>

        <!-- Weekly Schedule -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Horario Semanal</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach ($days as $day):
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-bold text-secondary mb-2"><?php echo $day; ?></h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <i class="fas fa-dumbbell text-primary mr-2"></i>
                                <span class="text-gray-600">Entrenamiento de Fuerza</span>
                            </div>
                            <div class="flex items-center">
                                <i class="far fa-clock text-primary mr-2"></i>
                                <span class="text-gray-600">60 minutos</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- No Active Plan -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Planes Disponibles</h2>
            </div>
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary mb-2">No tienes un plan activo</h3>
                    <p class="text-gray-600 mb-6">Explora nuestros planes de entrenamiento y comienza tu viaje fitness.</p>
                </div>
                
                <!-- Available Plans -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-primary transition duration-300">
                        <h4 class="font-bold text-secondary mb-2">Plan Básico</h4>
                        <p class="text-gray-600 mb-4">Ideal para principiantes</p>
                        <button class="w-full bg-primary text-white rounded-md py-2 hover:bg-opacity-90 transition duration-300">
                            Ver Detalles
                        </button>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-primary transition duration-300">
                        <h4 class="font-bold text-secondary mb-2">Plan Intermedio</h4>
                        <p class="text-gray-600 mb-4">Para usuarios con experiencia</p>
                        <button class="w-full bg-primary text-white rounded-md py-2 hover:bg-opacity-90 transition duration-300">
                            Ver Detalles
                        </button>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-primary transition duration-300">
                        <h4 class="font-bold text-secondary mb-2">Plan Avanzado</h4>
                        <p class="text-gray-600 mb-4">Máximo rendimiento</p>
                        <button class="w-full bg-primary text-white rounded-md py-2 hover:bg-opacity-90 transition duration-300">
                            Ver Detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
