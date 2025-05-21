<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';
require_once '../php/dashboard_helper.php';
require_once '../php/nutritionix_helper.php';

// Initialize Nutritionix API
$nutritionix = new NutritionixAPI('3438a51b', 'f1934d6d8683aa2cfbd47e870ef61fa1');

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    header('Location: ../login.php');
    exit();
}

$trainerId = $_SESSION['usuario_id'];
$clients = getTrainerClients($trainerId);
$notificationCount = getUserNotifications($trainerId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrición - TrainSmart</title>
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
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="nutrition.php" class="text-primary font-medium transition duration-300">Nutrición</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <?php if (isset($_SESSION['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['photo_url']); ?>" alt="Usuario" class="w-8 h-8 rounded-full">
                            <?php else: ?>
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario" class="w-8 h-8 rounded-full">
                            <?php endif; ?>
                            <span class="text-secondary font-medium"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden">
                            <a href="../profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="../settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ajustes</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="text-secondary hover:text-primary transition duration-300 relative cursor-pointer" id="notifications-button">
                            <i class="fas fa-bell text-xl"></i>
                            <?php if ($notificationCount > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                <?php echo $notificationCount; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div id="notifications-menu" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg py-1 hidden">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-medium text-gray-900">Notificaciones</h3>
                            </div>
                            <div class="px-4 py-2 text-sm">
                                <?php if ($notificationCount > 0): ?>
                                    <div class="text-gray-700">
                                        <div class="flex items-center space-x-2 mb-2 p-2 hover:bg-gray-50 rounded">
                                            <i class="fas fa-user-plus text-primary"></i>
                                            <p>Nuevo cliente asignado</p>
                                        </div>
                                        <div class="flex items-center space-x-2 mb-2 p-2 hover:bg-gray-50 rounded">
                                            <i class="fas fa-clipboard-check text-primary"></i>
                                            <p>Plan completado por cliente</p>
                                        </div>
                                        <div class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded">
                                            <i class="fas fa-chart-line text-primary"></i>
                                            <p>Actualización de progreso</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-gray-500 text-center py-2">
                                        No hay notificaciones nuevas
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
                <a href="plans.php" class="block py-2 text-secondary hover:text-primary font-medium">Planes</a>
                <a href="nutrition.php" class="block py-2 text-primary font-medium">Nutrición</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="../profile.php" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="../settings.php" class="block py-2 text-secondary hover:text-primary font-medium">Ajustes</a>
                <a href="../logout.php" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Meal Plan Creation -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-secondary font-heading mb-4">Crear Plan Nutricional</h1>
            <div class="mb-4">
                <label for="client-select" class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Cliente</label>
                <select id="client-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Food Search Section -->
                <div>
                    <div class="mb-4">
                        <label for="food-search" class="block text-sm font-medium text-gray-700 mb-2">Buscar Alimentos</label>
                        <div class="flex gap-2">
                            <input type="text" id="food-search" 
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" 
                                   placeholder="Ej: 100g pollo">
                            <button onclick="searchFood()" 
                                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                                Buscar
                            </button>
                        </div>
                    </div>
                    <div id="search-results" class="mt-4 space-y-2"></div>
                </div>

                <!-- Meal Plan Builder -->
                <div>
                    <h3 class="text-lg font-semibold text-secondary mb-3">Plan de Comidas</h3>
                    <div id="meal-plan" class="space-y-4">
                        <div class="meal-time">
                            <h4 class="font-medium text-gray-700 mb-2">Desayuno</h4>
                            <div id="breakfast-items" class="space-y-2"></div>
                        </div>
                        <div class="meal-time">
                            <h4 class="font-medium text-gray-700 mb-2">Almuerzo</h4>
                            <div id="lunch-items" class="space-y-2"></div>
                        </div>
                        <div class="meal-time">
                            <h4 class="font-medium text-gray-700 mb-2">Cena</h4>
                            <div id="dinner-items" class="space-y-2"></div>
                        </div>
                        <div class="meal-time">
                            <h4 class="font-medium text-gray-700 mb-2">Snacks</h4>
                            <div id="snacks-items" class="space-y-2"></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button onclick="saveMealPlan()" 
                                class="w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                            Guardar Plan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client's Meal Plans -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-secondary font-heading mb-4">Planes Nutricionales del Cliente</h2>
            <div id="client-meal-plans" class="space-y-4">
                <!-- Los planes se cargarán aquí dinámicamente -->
            </div>
        </div>

        <!-- Nutrition Overview -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-secondary font-heading mb-4">Nutrición de Clientes</h1>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calorías</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proteínas</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agua</th>
                            <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($clients as $client): 
                            $nutritionData = getUserNutritionData($client['id']);
                        ?>
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
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $nutritionData['calories']['current']; ?>/<?php echo $nutritionData['calories']['target']; ?> kcal
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-primary h-2 rounded-full" 
                                         style="width: <?php echo ($nutritionData['calories']['current'] / $nutritionData['calories']['target'] * 100); ?>%">
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $nutritionData['protein']['current']; ?>/<?php echo $nutritionData['protein']['target']; ?>g
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-primary h-2 rounded-full" 
                                         style="width: <?php echo ($nutritionData['protein']['current'] / $nutritionData['protein']['target'] * 100); ?>%">
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $nutritionData['water']['current']; ?>/<?php echo $nutritionData['water']['target']; ?>L
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-primary h-2 rounded-full" 
                                         style="width: <?php echo ($nutritionData['water']['current'] / $nutritionData['water']['target'] * 100); ?>%">
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="client_details.php?id=<?php echo $client['id']; ?>" 
                                   class="text-primary hover:text-primary-dark">Ver detalles</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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

            // Notifications menu toggle
            const notificationsButton = document.getElementById('notifications-button');
            const notificationsMenu = document.getElementById('notifications-menu');
            
            notificationsButton.addEventListener('click', () => {
                notificationsMenu.classList.toggle('hidden');
            });
            
            // Close notifications menu when clicking outside
            document.addEventListener('click', (event) => {
                if (!notificationsButton.contains(event.target) && !notificationsMenu.contains(event.target)) {
                    notificationsMenu.classList.add('hidden');
                }
            });
        });
    </script>
    <script>
    let selectedFoods = {
        breakfast: [],
        lunch: [],
        dinner: [],
        snacks: []
    };

    async function searchFood() {
        const query = document.getElementById('food-search').value;
        if (!query) return;

        try {
            const response = await fetch('../php/search_food.php', {
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
            div.className = 'p-3 bg-gray-50 rounded-md hover:bg-gray-100 cursor-pointer';
            div.innerHTML = `
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium">${food.food_name}</p>
                        <p class="text-sm text-gray-600">${food.serving_qty} ${food.serving_unit} - ${food.nf_calories} kcal</p>
                    </div>
                    <div class="flex gap-2">
                        <select class="text-sm border rounded px-2 py-1">
                            <option value="breakfast">Desayuno</option>
                            <option value="lunch">Almuerzo</option>
                            <option value="dinner">Cena</option>
                            <option value="snacks">Snacks</option>
                        </select>
                        <button onclick="addFoodToMeal(${JSON.stringify(food).replace(/"/g, '"')}, this.previousElementSibling.value)"
                                class="px-3 py-1 bg-primary text-white rounded hover:bg-opacity-90">
                            Añadir
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
    }

    function addFoodToMeal(food, mealType) {
        selectedFoods[mealType].push(food);
        updateMealPlanDisplay();
    }

    function updateMealPlanDisplay() {
        Object.keys(selectedFoods).forEach(mealType => {
            const container = document.getElementById(`${mealType}-items`);
            container.innerHTML = '';

            selectedFoods[mealType].forEach((food, index) => {
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center p-2 bg-gray-50 rounded';
                div.innerHTML = `
                    <div>
                        <p class="font-medium">${food.food_name}</p>
                        <p class="text-sm text-gray-600">${food.serving_qty} ${food.serving_unit} - ${food.nf_calories} kcal</p>
                    </div>
                    <button onclick="removeFood('${mealType}', ${index})" 
                            class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        });
    }

    function removeFood(mealType, index) {
        selectedFoods[mealType].splice(index, 1);
        updateMealPlanDisplay();
    }

    async function saveMealPlan() {
        const clientId = document.getElementById('client-select').value;
        if (!clientId) {
            alert('Por favor seleccione un cliente');
            return;
        }

        try {
            const response = await fetch('../php/save_meal_plan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    client_id: clientId,
                    ...selectedFoods
                })
            });

            const result = await response.json();
            if (result.success) {
                alert('Plan nutricional guardado correctamente');
                selectedFoods = { breakfast: [], lunch: [], dinner: [], snacks: [] };
                updateMealPlanDisplay();
                loadClientMealPlans(clientId);
            } else {
                alert('Error al guardar el plan nutricional');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar el plan nutricional');
        }
    }

    function loadClientMealPlans(clientId) {
        if (!clientId) return;

        fetch(`../php/get_meal_plans.php?client_id=${clientId}`)
            .then(response => response.json())
            .then(plans => {
                const container = document.getElementById('client-meal-plans');
                container.innerHTML = '';

                plans.forEach(plan => {
                    const planDiv = document.createElement('div');
                    planDiv.className = 'bg-white p-4 rounded-lg shadow mb-4';
                    planDiv.innerHTML = `
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-semibold">Plan del ${new Date(plan.created_at).toLocaleDateString()}</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium mb-2">Desayuno</h4>
                                <ul class="list-disc list-inside">
                                    ${plan.plan_data.breakfast.map(food => 
                                        `<li>${food.food_name} - ${food.serving_qty} ${food.serving_unit}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Almuerzo</h4>
                                <ul class="list-disc list-inside">
                                    ${plan.plan_data.lunch.map(food => 
                                        `<li>${food.food_name} - ${food.serving_qty} ${food.serving_unit}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Cena</h4>
                                <ul class="list-disc list-inside">
                                    ${plan.plan_data.dinner.map(food => 
                                        `<li>${food.food_name} - ${food.serving_qty} ${food.serving_unit}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium mb-2">Snacks</h4>
                                <ul class="list-disc list-inside">
                                    ${plan.plan_data.snacks.map(food => 
                                        `<li>${food.food_name} - ${food.serving_qty} ${food.serving_unit}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                    container.appendChild(planDiv);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los planes nutricionales');
            });
    }

    // Agregar event listener para el cambio de cliente
    document.getElementById('client-select').addEventListener('change', function() {
        loadClientMealPlans(this.value);
    });
    </script>
</body>
</html>
