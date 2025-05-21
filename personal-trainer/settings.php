<?php
require_once 'php/config.php';
require_once 'php/auth_helper.php';
require_once 'php/profile_helper.php';
require_once 'php/dashboard_helper.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['usuario_id'];
$userSettings = getUserSettings($userId);
$notificationCount = getUserNotifications($userId);

// Procesar actualización de configuración
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_settings'])) {
            $settings = [
                'notifications_enabled' => isset($_POST['notifications_enabled']) ? 1 : 0,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'language' => sanitizarInput($_POST['language']),
                'theme' => sanitizarInput($_POST['theme'])
            ];
            
            if (updateUserSettings($userId, $settings)) {
                $message = 'Configuración actualizada correctamente';
                $messageType = 'success';
                $userSettings = $settings;
            }
        } elseif (isset($_POST['update_password'])) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Las contraseñas nuevas no coinciden');
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception('La contraseña debe tener al menos 8 caracteres');
            }
            
            if (updateUserPassword($userId, $currentPassword, $newPassword)) {
                $message = 'Contraseña actualizada correctamente';
                $messageType = 'success';
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes - TrainSmart</title>
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
                    <a href="plans.php" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="progress.php" class="text-secondary hover:text-primary font-medium transition duration-300">Progreso</a>
                    <a href="nutrition.php" class="text-secondary hover:text-primary font-medium transition duration-300">Nutrición</a>
                    <a href="settings.php" class="text-primary font-medium transition duration-300">Ajustes</a>
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
                <a href="nutrition.php" class="block py-2 text-secondary hover:text-primary font-medium">Nutrición</a>
                <a href="settings.php" class="block py-2 text-primary font-medium">Ajustes</a>
                <div class="border-t border-gray-200 my-2"></div>
                <a href="profile.php" class="block py-2 text-secondary hover:text-primary font-medium">Mi Perfil</a>
                <a href="logout.php" class="block py-2 text-secondary hover:text-primary font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Settings Content -->
    <div class="container mx-auto px-4 py-8">
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Settings Navigation -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-secondary mb-4">Ajustes</h3>
                    <nav class="space-y-2">
                        <a href="#account" class="block px-4 py-2 rounded-md text-secondary hover:bg-light hover:text-primary transition duration-300">
                            <i class="fas fa-user-circle mr-2"></i> Cuenta
                        </a>
                        <a href="#notifications" class="block px-4 py-2 rounded-md text-secondary hover:bg-light hover:text-primary transition duration-300">
                            <i class="fas fa-bell mr-2"></i> Notificaciones
                        </a>
                        <a href="#privacy" class="block px-4 py-2 rounded-md text-secondary hover:bg-light hover:text-primary transition duration-300">
                            <i class="fas fa-shield-alt mr-2"></i> Privacidad
                        </a>
                        <a href="#appearance" class="block px-4 py-2 rounded-md text-secondary hover:bg-light hover:text-primary transition duration-300">
                            <i class="fas fa-paint-brush mr-2"></i> Apariencia
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Settings Forms -->
            <div class="md:col-span-2">
                <!-- Account Settings -->
                <div id="account" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Configuración de la Cuenta</h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-6">
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-4">Cambiar Contraseña</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                                        <input type="password" id="current_password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                                        <input type="password" id="new_password" name="new_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="update_password" class="bg-primary text-white py-2 px-4 rounded-md hover:bg-opacity-90 transition duration-300">
                                        Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div id="notifications" class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Configuración de Notificaciones</h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-secondary">Notificaciones Push</h4>
                                    <p class="text-sm text-gray-600">Recibe notificaciones sobre tus entrenamientos y logros</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notifications_enabled" class="sr-only peer" <?php echo $userSettings['notifications_enabled'] ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary peer-focus:ring-opacity-20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-secondary">Notificaciones por Email</h4>
                                    <p class="text-sm text-gray-600">Recibe actualizaciones importantes por correo electrónico</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" class="sr-only peer" <?php echo $userSettings['email_notifications'] ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary peer-focus:ring-opacity-20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div class="pt-4">
                                <button type="submit" name="update_settings" class="bg-primary text-white py-2 px-4 rounded-md hover:bg-opacity-90 transition duration-300">
                                    Guardar Preferencias
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div id="privacy" class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Configuración de Privacidad</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-secondary">Perfil Público</h4>
                                    <p class="text-sm text-gray-600">Permite que otros usuarios vean tu perfil y progreso</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary peer-focus:ring-opacity-20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-secondary">Compartir Estadísticas</h4>
                                    <p class="text-sm text-gray-600">Permite que tu entrenador vea tus estadísticas detalladas</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary peer-focus:ring-opacity-20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div id="appearance" class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-primary text-white p-4">
                        <h2 class="text-xl font-bold font-heading">Configuración de Apariencia</h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Idioma</label>
                                <select id="language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="es" <?php echo $userSettings['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                                    <option value="en" <?php echo $userSettings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                </select>
                            </div>
                            <div>
                                <label for="theme" class="block text-sm font-medium text-gray-700 mb-1">Tema</label>
                                <select id="theme" name="theme" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="light" <?php echo $userSettings['theme'] === 'light' ? 'selected' : ''; ?>>Claro</option>
                                    <option value="dark" <?php echo $userSettings['theme'] === 'dark' ? 'selected' : ''; ?>>Oscuro</option>
                                </select>
                            </div>
                            <button type="submit" name="update_settings" class="bg-primary text-white py-2 px-4 rounded-md hover:bg-opacity-90 transition duration-300">
                                Guardar Preferencias
                            </button>
                        </form>
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

        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
