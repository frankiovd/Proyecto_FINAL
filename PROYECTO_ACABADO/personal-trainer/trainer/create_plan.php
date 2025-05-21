<?php
require_once '../php/config.php';
require_once '../php/auth_helper.php';
require_once '../php/plan_functions.php';
require_once '../php/dashboard_helper.php';

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario_id']) || !isTrainer()) {
    header('Location: ../login.php');
    exit();
}

$trainerId = $_SESSION['usuario_id'];
$userName = $_SESSION['nombre'];
$errors = [];
$success = false;

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar y recoger datos del plan
        $planData = [
            'name' => sanitizarInput($_POST['name'] ?? ''),
            'description' => sanitizarInput($_POST['description'] ?? ''),
            'difficulty_level' => sanitizarInput($_POST['difficulty_level'] ?? ''),
            'duration_weeks' => intval($_POST['duration_weeks'] ?? 0),
            'exercises' => []
        ];

        // Validar campos obligatorios
        if (empty($planData['name'])) {
            $errors['name'] = "El nombre del plan es obligatorio.";
        }
        if (empty($planData['difficulty_level'])) {
            $errors['difficulty_level'] = "El nivel de dificultad es obligatorio.";
        }
        if ($planData['duration_weeks'] <= 0) {
            $errors['duration_weeks'] = "La duración debe ser mayor a 0 semanas.";
        }

        // Procesar ejercicios
        if (isset($_POST['exercise_name']) && is_array($_POST['exercise_name'])) {
            for ($i = 0; $i < count($_POST['exercise_name']); $i++) {
                if (!empty($_POST['exercise_name'][$i])) {
                    $planData['exercises'][] = [
                        'name' => sanitizarInput($_POST['exercise_name'][$i]),
                        'sets' => sanitizarInput($_POST['exercise_sets'][$i]),
                        'reps' => sanitizarInput($_POST['exercise_reps'][$i]),
                        'day' => sanitizarInput($_POST['exercise_day'][$i]),
                        'notes' => sanitizarInput($_POST['exercise_notes'][$i] ?? '')
                    ];
                }
            }
        }

        if (empty($planData['exercises'])) {
            $errors['exercises'] = "Debe agregar al menos un ejercicio al plan.";
        }

        // Si no hay errores, crear el plan
        if (empty($errors)) {
            $planId = createTrainingPlan($trainerId, $planData);
            if ($planId) {
                header('Location: plans.php?success=created');
                exit();
            }
        }
    } catch (Exception $e) {
        error_log("Error al crear plan: " . $e->getMessage());
        $errors['general'] = "Ha ocurrido un error al crear el plan.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Plan de Entrenamiento - TrainSmart</title>
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
        <div class="max-w-4xl mx-auto">
            <!-- Page Title -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h1 class="text-2xl font-bold text-secondary font-heading mb-2">Crear Nuevo Plan</h1>
                <p class="text-gray-600">Completa el formulario para crear un nuevo plan de entrenamiento.</p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors['general'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $errors['general']; ?></span>
            </div>
            <?php endif; ?>

            <!-- Create Plan Form -->
            <form action="create_plan.php" method="POST" class="bg-white rounded-lg shadow-md p-6">
                <!-- Basic Plan Information -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-secondary mb-4">Información Básica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Nombre del Plan*</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <?php if (isset($errors['name'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="difficulty_level" class="block text-gray-700 font-medium mb-2">Nivel de Dificultad*</label>
                            <select id="difficulty_level" name="difficulty_level" required
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Seleccionar nivel</option>
                                <option value="beginner">Principiante</option>
                                <option value="intermediate">Intermedio</option>
                                <option value="advanced">Avanzado</option>
                            </select>
                            <?php if (isset($errors['difficulty_level'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['difficulty_level']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="duration_weeks" class="block text-gray-700 font-medium mb-2">Duración (semanas)*</label>
                            <input type="number" id="duration_weeks" name="duration_weeks" min="1" required
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <?php if (isset($errors['duration_weeks'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['duration_weeks']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="description" class="block text-gray-700 font-medium mb-2">Descripción</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Exercises Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-secondary mb-4">Ejercicios</h2>
                    <div id="exercises-container">
                        <!-- Exercise template will be cloned here -->
                    </div>
                    <?php if (isset($errors['exercises'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['exercises']; ?></p>
                    <?php endif; ?>
                    <button type="button" id="add-exercise" 
                        class="mt-4 bg-secondary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-plus mr-2"></i> Agregar Ejercicio
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <a href="plans.php" class="mr-4 px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-300">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">
                        Crear Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Exercise Template (hidden) -->
    <template id="exercise-template">
        <div class="exercise-item bg-gray-50 p-4 rounded-md mb-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-secondary">Ejercicio</h3>
                <button type="button" class="remove-exercise text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nombre del Ejercicio*</label>
                    <input type="text" name="exercise_name[]" required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Día*</label>
                    <select name="exercise_day[]" required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sábado</option>
                        <option value="domingo">Domingo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Series*</label>
                    <input type="number" name="exercise_sets[]" min="1" required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Repeticiones*</label>
                    <input type="text" name="exercise_reps[]" required placeholder="Ej: 12 o 8-12"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Notas</label>
                    <textarea name="exercise_notes[]" rows="2"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>
        </div>
    </template>

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

            // Exercise functionality
            const container = document.getElementById('exercises-container');
            const template = document.getElementById('exercise-template');
            const addButton = document.getElementById('add-exercise');

            // Add first exercise by default
            addExercise();

            // Add exercise button click handler
            addButton.addEventListener('click', addExercise);

            function addExercise() {
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);

                // Add remove button handler to the new exercise
                const removeButtons = container.getElementsByClassName('remove-exercise');
                const lastRemoveButton = removeButtons[removeButtons.length - 1];
                lastRemoveButton.addEventListener('click', function(e) {
                    const exerciseItem = e.target.closest('.exercise-item');
                    if (container.children.length > 1) {
                        exerciseItem.remove();
                    }
                });
            }
        });
    </script>
</body>
</html>
