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
$nutritionData = getUserNutritionData($userId);
$notificationCount = getUserNotifications($userId);

// Procesar el formulario de registro nutricional
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_nutrition'])) {
    try {
        $conn = conectarDB();
        
        // Verificar si ya existe un registro para esta fecha
        $stmt = $conn->prepare("SELECT id FROM nutrition_logs WHERE user_id = ? AND log_date = ?");
        $stmt->bind_param("is", $userId, $_POST['log_date']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar registro existente
            $stmt = $conn->prepare("UPDATE nutrition_logs SET 
                                  calories_consumed = ?,
                                  protein_consumed = ?,
                                  water_consumed = ?
                                  WHERE user_id = ? AND log_date = ?");
            $stmt->bind_param("iidis", 
                $_POST['calories'],
                $_POST['protein'],
                $_POST['water'],
                $userId,
                $_POST['log_date']
            );
        } else {
            // Insertar nuevo registro
            $stmt = $conn->prepare("INSERT INTO nutrition_logs 
                                  (user_id, calories_consumed, protein_consumed, water_consumed, log_date)
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiids", 
                $userId,
                $_POST['calories'],
                $_POST['protein'],
                $_POST['water'],
                $_POST['log_date']
            );
        }
        
        if ($stmt->execute()) {
            $message = "Registro nutricional guardado correctamente";
            $messageType = "success";
            // Refresh nutrition data after successful update
            $nutritionData = getUserNutritionData($userId);
        } else {
            throw new Exception("Error al guardar el registro");
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mi Nutrición - TrainSmart</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
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
                    <a href="progress.php" class="text-secondary hover:text-primary font-medium transition duration-300">Progreso</a>
                    <a href="nutrition.php" class="text-primary font-medium transition duration-300">Nutrición</a>
                    <a href="settings.php" class="text-secondary hover:text-primary font-medium transition duration-300">Ajustes</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <?php if (isset($_SESSION['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['photo_url']); ?>" alt="Usuario" class="w-8 h-8 rounded-full" />
                            <?php else: ?>
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario" class="w-8 h-8 rounded-full" />
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
                <a href="progress.php" class="block py-2 text-secondary hover:text-primary font-medium">Progreso</a>
                <a href="nutrition.php" class="block py-2 text-primary font-medium">Nutrición</a>
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
            <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Mi Nutrición</h1>
            <p class="text-gray-600">Resumen de tu plan nutricional y consumo diario.</p>
        </div>

        <!-- Meal Plans -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Mi Plan Nutricional</h2>
            </div>
            <div class="p-6">
                <div id="meal-plans" class="space-y-6">
                    <?php
                    require_once 'php/nutritionix_helper.php';
                    $mealPlans = getMealPlans($userId);
                    
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

        <!-- Food Search -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Buscar Alimentos</h2>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <div class="flex gap-2">
                        <input type="text" id="food-search" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary" 
                               placeholder="Ej: 100g pollo">
                        <button onclick="searchFood()" 
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                            Buscar
                        </button>
                    </div>
                </div>
                <div id="search-results" class="mt-4 space-y-2"></div>
            </div>
        </div>

        <!-- Log Nutrition Form -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Registrar Consumo Diario</h2>
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
                            <label for="calories" class="block text-sm font-medium text-gray-700 mb-1">Calorías Consumidas</label>
                            <input type="number" id="calories" name="calories" min="0" max="5000" step="1" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                        <div>
                            <label for="protein" class="block text-sm font-medium text-gray-700 mb-1">Proteínas (g)</label>
                            <input type="number" id="protein" name="protein" min="0" max="500" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                        <div>
                            <label for="water" class="block text-sm font-medium text-gray-700 mb-1">Agua (L)</label>
                            <input type="number" id="water" name="water" min="0" max="10" step="0.1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="log_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="log_date" name="log_date" 
                               value="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                               required>
                    </div>
                    <div>
                        <button type="submit" name="log_nutrition" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Nutrition Stats -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary text-white p-4">
                <h2 class="text-xl font-bold font-heading">Resumen Nutricional</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Calories -->
                    <div class="text-center">
                        <?php 
                        $caloriesPercentage = ($nutritionData['calories']['current'] / $nutritionData['calories']['target']) * 100;
                        ?>
                        <div class="inline-block w-32 h-32 rounded-full border-8 border-primary relative">
                            <span class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-primary">
                                <?php echo round($caloriesPercentage); ?>%
                            </span>
                        </div>
                        <p class="mt-4 text-lg text-gray-600">Calorías</p>
                        <p class="font-bold text-secondary text-xl">
                            <?php echo $nutritionData['calories']['current']; ?>/<?php echo $nutritionData['calories']['target']; ?>
                        </p>
                        <p class="text-sm text-gray-500">kcal</p>
                    </div>

                    <!-- Protein -->
                    <div class="text-center">
                        <?php 
                        $proteinPercentage = ($nutritionData['protein']['current'] / $nutritionData['protein']['target']) * 100;
                        ?>
                        <div class="inline-block w-32 h-32 rounded-full border-8 border-primary relative">
                            <span class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-primary">
                                <?php echo round($proteinPercentage); ?>%
                            </span>
                        </div>
                        <p class="mt-4 text-lg text-gray-600">Proteínas</p>
                        <p class="font-bold text-secondary text-xl">
                            <?php echo $nutritionData['protein']['current']; ?>/<?php echo $nutritionData['protein']['target']; ?>
                        </p>
                        <p class="text-sm text-gray-500">gramos</p>
                    </div>

                    <!-- Water -->
                    <div class="text-center">
                        <?php 
                        $waterPercentage = ($nutritionData['water']['current'] / $nutritionData['water']['target']) * 100;
                        ?>
                        <div class="inline-block w-32 h-32 rounded-full border-8 border-primary relative">
                            <span class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-primary">
                                <?php echo round($waterPercentage); ?>%
                            </span>
                        </div>
                        <p class="mt-4 text-lg text-gray-600">Agua</p>
                        <p class="font-bold text-secondary text-xl">
                            <?php echo $nutritionData['water']['current']; ?>/<?php echo $nutritionData['water']['target']; ?>
                        </p>
                        <p class="text-sm text-gray-500">litros</p>
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
                    <a href="https://www.facebook.com" target="_blank" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.twitter.com" target="_blank" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.youtube.com" target="_blank" class="text-gray-400 hover:text-primary transition duration-300">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <div class="mt-4 md:mt-0">
                    <p class="text-gray-400">&copy; 2025 TrainSmart - Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Custom JS -->
    <script>
        async function searchFood() {
            const query = document.getElementById('food-search').value;
            if (!query) return;

            try {
                const response = await fetch('php/search_food.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ query })
                });

                const data = await response.json();
                displaySearchResults(data);
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displaySearchResults(results) {
            const container = document.getElementById('search-results');
            container.innerHTML = '';

            results.forEach(food => {
                const div = document.createElement('div');
                div.className = 'p-3 bg-gray-50 rounded-md hover:bg-gray-100';
                div.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">${food.food_name}</p>
                            <p class="text-sm text-gray-600">${food.serving_qty} ${food.serving_unit} - ${food.nf_calories} kcal</p>
                            <p class="text-sm text-gray-600">Proteínas: ${food.nf_protein}g</p>
                        </div>
                        <button onclick="logFood(${JSON.stringify(food).replace(/"/g, '"')})"
                                class="px-3 py-1 bg-primary text-white rounded hover:bg-opacity-90">
                            Registrar
                        </button>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function logFood(food) {
            document.getElementById('calories').value = food.nf_calories;
            document.getElementById('protein').value = food.nf_protein;
        }

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
