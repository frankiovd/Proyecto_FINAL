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

// Verificar ID del cliente
if (!isset($_GET['client_id'])) {
    header('Location: clients.php');
    exit();
}

$clientId = intval($_GET['client_id']);
$trainerId = $_SESSION['usuario_id'];
$message = '';

// Procesar formulario de asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    try {
        $conn = conectarDB();
        
        // Desactivar planes actuales
        $stmt = $conn->prepare("UPDATE client_plans SET status = 'inactive' WHERE client_id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        
        // Asignar nuevo plan
        $stmt = $conn->prepare("INSERT INTO client_plans (client_id, plan_id, start_date, status) VALUES (?, ?, NOW(), 'active')");
        $stmt->bind_param("ii", $clientId, $_POST['plan_id']);
        $stmt->execute();
        
        $message = "Plan asignado exitosamente.";
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

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
    
    // Obtener planes disponibles del entrenador
    $stmt = $conn->prepare("SELECT * FROM training_plans WHERE trainer_id = ? AND status = 'active'");
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $plans = [];
    while ($plan = $result->fetch_assoc()) {
        $plans[] = $plan;
    }
    
} catch (Exception $e) {
    error_log("Error al obtener información: " . $e->getMessage());
    header('Location: clients.php?error=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Plan - FitLife</title>
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
        <!-- Page Title -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Asignar Plan de Entrenamiento</h1>
                    <p class="text-gray-600">Cliente: <?php echo htmlspecialchars($client['name']); ?></p>
                </div>
                <a href="client_details.php?id=<?php echo $clientId; ?>" 
                   class="text-primary hover:text-primary-dark transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-md <?php echo strpos($message, 'Error') === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($plans)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <p class="text-gray-600 mb-4">No tienes planes de entrenamiento disponibles.</p>
                <a href="plans.php" class="inline-block bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                    <i class="fas fa-plus mr-2"></i> Crear Nuevo Plan
                </a>
            </div>
        <?php else: ?>
            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($plans as $plan): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <form action="assign_plan.php?client_id=<?php echo $clientId; ?>" method="POST" class="h-full flex flex-col">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            
                            <h3 class="text-xl font-semibold text-secondary mb-2">
                                <?php echo htmlspecialchars($plan['name']); ?>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 flex-grow">
                                <?php echo htmlspecialchars($plan['description']); ?>
                            </p>
                            
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-signal mr-2"></i>
                                    <span>Dificultad: <?php echo htmlspecialchars($plan['difficulty_level']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span>Duración: <?php echo htmlspecialchars($plan['duration_weeks']); ?> semanas</span>
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    class="mt-6 w-full bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                                Asignar Plan
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
