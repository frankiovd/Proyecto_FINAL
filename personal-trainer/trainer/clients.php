<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    header('Location: ../login.php');
    exit();
}

// Obtener información del entrenador
$trainerId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];

// Obtener todos los clientes del entrenador
$clients = getTrainerClients($trainerId);

// Procesar formulario de agregar cliente
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_client':
                if (isset($_POST['client_email'])) {
                    $clientEmail = trim($_POST['client_email']);
                    
                    // Buscar usuario por email
                    $conn = conectarDB();
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->bind_param("s", $clientEmail);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $client = $result->fetch_assoc();
                        addClientToTrainer($trainerId, $client['id']);
                        $message = "Cliente agregado exitosamente.";
                    } else {
                        throw new Exception("No se encontró ningún usuario con ese email.");
                    }
                }
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - TrainSmart</title>
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
                    <a href="clients.php" class="text-primary font-medium transition duration-300">Clientes</a>
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
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
                <a href="clients.php" class="block py-2 text-primary font-medium">Clientes</a>
                <a href="plans.php" class="block py-2 text-secondary hover:text-primary font-medium">Planes</a>
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
        <?php if ($message && strpos($message, 'Error') === false): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($message && strpos($message, 'Error') === 0): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <!-- Page Title -->
        <div class="flex justify-between items-center bg-white rounded-lg shadow-md p-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Gestión de Clientes</h1>
                <p class="text-gray-600">Administra y haz seguimiento de tus clientes.</p>
            </div>
            <button onclick="document.getElementById('addClientModal').classList.remove('hidden')"
                    class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                <i class="fas fa-plus mr-2"></i> Agregar Cliente
            </button>
        </div>

        <!-- Clients List -->
        <div class="mt-8">
            <div class="align-middle min-w-full overflow-x-auto shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Plan Actual
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progreso
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (isset($client['photo_url'])): ?>
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="<?php echo htmlspecialchars($client['photo_url']); ?>" 
                                                 alt="">
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($client['name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($client['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $clientPlan = getClientActivePlan($client['id']);
                                    if ($clientPlan): 
                                    ?>
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($clientPlan['name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Desde: <?php echo date('d/m/Y', strtotime($clientPlan['start_date'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">Sin plan asignado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-primary h-2.5 rounded-full" style="width: 45%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500">45% completado</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="client_details.php?id=<?php echo $client['id']; ?>" 
                                       class="text-primary hover:text-primary-dark mr-3">Ver detalles</a>
                                    <a href="assign_plan.php?client_id=<?php echo $client['id']; ?>" 
                                       class="text-primary hover:text-primary-dark">Asignar Plan</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="clients.php" method="POST">
                    <input type="hidden" name="action" value="add_client">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Agregar Nuevo Cliente
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Ingresa el email del cliente para agregarlo a tu lista.
                                    </p>
                                    <input type="email" 
                                           name="client_email" 
                                           required
                                           class="mt-4 shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
                                           placeholder="cliente@ejemplo.com">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                            Agregar
                        </button>
                        <button type="button" 
                                onclick="document.getElementById('addClientModal').classList.add('hidden')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
        });
    </script>
</body>
</html>
