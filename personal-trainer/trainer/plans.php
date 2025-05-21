<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';
require_once '../php/plan_functions.php';

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    header('Location: ../login.php');
    exit();
}

// Obtener información del entrenador
$trainerId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];

// Procesar eliminación de plan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'delete' && isset($_POST['plan_id'])) {
            $planId = intval($_POST['plan_id']);
            deletePlan($planId, $trainerId);
            $message = "Plan eliminado exitosamente.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Obtener planes del entrenador
$plans = getAllTrainingPlans();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Planes - TrainSmart</title>
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
                <a href="../index.html" class="text-2xl font-bold text-primary font-heading">TrainSmart</a>
                <div class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-secondary hover:text-primary font-medium transition duration-300">Panel</a>
                    <a href="clients.php" class="text-secondary hover:text-primary font-medium transition duration-300">Clientes</a>
                    <a href="plans.php" class="text-primary font-medium transition duration-300">Planes</a>
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
                            <a href="../profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="../settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ajustes</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                    <a href="../messages.php" class="text-secondary hover:text-primary transition duration-300 relative">
                        <i class="fas fa-bell text-xl"></i>
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
                <a href="clients.php" class="block py-2 text-secondary hover:text-primary font-medium">Clientes</a>
                <a href="plans.php" class="block py-2 text-primary font-medium">Planes</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="../profile.php" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="../settings.php" class="block py-2 text-secondary hover:text-primary font-medium">Ajustes</a>
                <a href="../logout.php" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Success Message -->
        <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">Plan creado exitosamente.</span>
        </div>
        <?php endif; ?>

        <!-- Page Title -->
        <div class="flex justify-between items-center bg-white rounded-lg shadow-md p-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Gestión de Planes</h1>
                <p class="text-gray-600">Administra los planes de entrenamiento para tus clientes.</p>
            </div>
            <a href="create_plan.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300 inline-block">
                <i class="fas fa-plus mr-2"></i> Nuevo Plan
            </a>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($plans as $plan): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-secondary text-lg">
                            <?php echo htmlspecialchars($plan['name']); ?>
                        </h3>
                        <span class="inline-block bg-<?php echo $plan['status'] === 'active' ? 'green' : 'gray'; ?>-100 text-<?php echo $plan['status'] === 'active' ? 'green' : 'gray'; ?>-800 px-2 py-1 rounded-full text-xs font-medium">
                            <?php echo ucfirst($plan['status']); ?>
                        </span>
                    </div>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars($plan['description']); ?>
                    </p>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-gray-500 text-sm">Nivel</p>
                            <p class="font-medium text-secondary">
                                <?php echo ucfirst(htmlspecialchars($plan['difficulty_level'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Duración</p>
                            <p class="font-medium text-secondary">
                                <?php echo htmlspecialchars($plan['duration_weeks']); ?> semanas
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="create_plan.php?edit=<?php echo $plan['id']; ?>" 
                           class="flex-1 bg-secondary text-white px-3 py-2 rounded-md hover:bg-opacity-90 transition duration-300 text-center">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </a>
                        <form action="plans.php" method="POST" class="flex-1" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este plan?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <button type="submit" class="w-full bg-red-500 text-white px-3 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                                <i class="fas fa-trash-alt mr-2"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Add New Plan Card -->
            <a href="create_plan.php" class="bg-white rounded-lg shadow-md overflow-hidden border-2 border-dashed border-gray-300">
                <div class="p-6 flex flex-col items-center justify-center h-full text-center cursor-pointer hover:bg-gray-50 transition duration-300">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-plus text-2xl text-primary"></i>
                    </div>
                    <h3 class="font-bold text-secondary text-lg mb-2">Crear Nuevo Plan</h3>
                    <p class="text-gray-600">Añade un nuevo plan de entrenamiento</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <a href="../index.html" class="text-2xl font-bold text-primary font-heading">TrainSmart</a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Add Plan button click handler
            const addPlanButtons = document.querySelectorAll('.add-plan-btn');
            addPlanButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Handle add plan action
                    console.log('Add plan clicked');
                });
            });
        });
    </script>
</body>
</html>
