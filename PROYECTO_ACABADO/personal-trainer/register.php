<?php
// Initialize variables
$name = $email = $phone = $password = $confirm_password = $user_type = '';
$errors = [];
$success = false;

// Include database connection
require_once 'php/config.php';

function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate name
    if (empty($_POST['name'])) {
        $errors['name'] = 'El nombre es obligatorio';
    } else {
        $name = trim($_POST['name']);
    }
    
    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'El email es obligatorio';
    } else {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
    }
    
    // Validate phone
    if (!empty($_POST['phone'])) {
        $phone = trim($_POST['phone']);
        // Simple phone validation
        if (!preg_match('/^[0-9+\s()-]{6,20}$/', $phone)) {
            $errors['phone'] = 'El número de teléfono no es válido';
        }
    }
    
    // Validate password
    if (empty($_POST['password'])) {
        $errors['password'] = 'La contraseña es obligatoria';
    } else {
        $password = $_POST['password'];
        if (strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
        }
    }
    
    // Validate password confirmation
    if (empty($_POST['confirm_password'])) {
        $errors['confirm_password'] = 'Debes confirmar la contraseña';
    } else {
        $confirm_password = $_POST['confirm_password'];
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
    }
    
    // Validate user type
    if (empty($_POST['user_type'])) {
        $errors['user_type'] = 'Debes seleccionar un tipo de usuario';
    } else {
        $user_type = $_POST['user_type'];
        if (!in_array($user_type, ['trainer', 'client'])) {
            $errors['user_type'] = 'Tipo de usuario no válido';
        }
    }

    // Check terms acceptance
    if (!isset($_POST['terms'])) {
        $errors['terms'] = 'Debes aceptar los términos y condiciones';
    }
    
    // If no errors, process registration
    if (empty($errors)) {
        $conn = conectarDB();

        // Check if email already exists
        if (emailExists($conn, $email)) {
            $errors['email'] = 'Este email ya está registrado';
        } else {
            try {
                // Start transaction
                $conn->begin_transaction();

                // Insert user with plain password
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, status) VALUES (?, ?, ?, ?, 'active')");
                $stmt->bind_param("ssss", $name, $email, $password, $phone);
                
                if ($stmt->execute()) {
                    $userId = $stmt->insert_id;

                    // Insert user role
                    $stmtRole = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
                    $stmtRole->bind_param("is", $userId, $user_type);
                    
                    if ($stmtRole->execute()) {
                        // Create default user settings
                        $stmtSettings = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
                        $stmtSettings->bind_param("i", $userId);
                        $stmtSettings->execute();
                        $stmtSettings->close();

                        // Commit transaction
                        $conn->commit();
                        $success = true;
                    } else {
                        throw new Exception("Error al asignar el rol de usuario");
                    }
                    $stmtRole->close();
                } else {
                    throw new Exception("Error al crear el usuario");
                }
                $stmt->close();
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $errors['general'] = 'Error al registrar el usuario: ' . $e->getMessage();
            }
        }
        $conn->close();
    }
}

// Get selected plan from URL parameter
$selected_plan = isset($_GET['plan']) ? $_GET['plan'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TrainSmart</title>
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
                    <a href="index.html" class="text-secondary hover:text-primary font-medium transition duration-300">Inicio</a>
                    <a href="about.html" class="text-secondary hover:text-primary font-medium transition duration-300">Sobre Nosotros</a>
                    <a href="testimonios.html" class="text-secondary hover:text-primary font-medium transition duration-300">Testimonios</a>
                    <a href="area-entrenador.html" class="text-secondary hover:text-primary font-medium transition duration-300">Área Entrenador</a>
                    <a href="area-cliente.html" class="text-secondary hover:text-primary font-medium transition duration-300">Área Cliente</a>
                </div>
                <div class="hidden md:flex space-x-4">
                    <a href="login.php" class="px-4 py-2 text-secondary font-medium hover:text-primary transition duration-300">Iniciar Sesión</a>
                    <a href="register.php" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">Registrarse</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-secondary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <a href="index.html" class="block py-2 text-secondary hover:text-primary font-medium">Inicio</a>
                <a href="about.html" class="block py-2 text-secondary hover:text-primary font-medium">Sobre Nosotros</a>
                <a href="testimonios.html" class="block py-2 text-secondary hover:text-primary font-medium">Testimonios</a>
                <a href="area-entrenador.html" class="block py-2 text-secondary hover:text-primary font-medium">Área Entrenador</a>
                <a href="area-cliente.html" class="block py-2 text-secondary hover:text-primary font-medium">Área Cliente</a>
                <div class="mt-4">
                    <a href="login.php" class="block py-2 text-secondary hover:text-primary font-medium">Iniciar Sesión</a>
                    <a href="register.php" class="block py-2 text-primary font-medium">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="py-20 bg-primary">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white font-heading mb-4">Registro</h1>
            <p class="text-white text-lg max-w-3xl mx-auto">Únete a la comunidad TrainSmart y comienza tu viaje hacia un estilo de vida más saludable.</p>
        </div>
    </section>

    <!-- Registration Form Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">¡Registro exitoso!</strong>
                        <span class="block sm:inline"> Tu cuenta ha sido creada correctamente. Ahora puedes <a href="login.php" class="text-primary hover:underline">iniciar sesión</a>.</span>
                    </div>
                <?php else: ?>
                    <div class="bg-light p-8 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold text-primary font-heading mb-6">Crea tu cuenta</h2>
                        
                        <form action="register.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-secondary font-medium mb-2">Nombre completo</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="w-full px-4 py-2 border <?php echo isset($errors['name']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="email" class="block text-secondary font-medium mb-2">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full px-4 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-secondary font-medium mb-2">Teléfono</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full px-4 py-2 border <?php echo isset($errors['phone']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <?php if (isset($errors['phone'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['phone']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label for="user_type" class="block text-secondary font-medium mb-2">Tipo de usuario</label>
                                <select id="user_type" name="user_type" class="w-full px-4 py-2 border <?php echo isset($errors['user_type']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <option value="">Selecciona tipo de usuario</option>
                                    <option value="trainer" <?php echo $user_type === 'trainer' ? 'selected' : ''; ?>>Entrenador</option>
                                    <option value="client" <?php echo $user_type === 'client' ? 'selected' : ''; ?>>Cliente</option>
                                </select>
                                <?php if (isset($errors['user_type'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['user_type']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-secondary font-medium mb-2">Contraseña</label>
                                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['password']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-secondary font-medium mb-2">Confirmar contraseña</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border <?php echo isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['confirm_password']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="terms" name="terms" class="mr-2 focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                                <label for="terms" class="text-secondary">Acepto los <a href="#" class="text-primary hover:underline">términos y condiciones</a> y la <a href="#" class="text-primary hover:underline">política de privacidad</a></label>
                            </div>
                            <?php if (isset($errors['terms'])): ?>
                                <p class="text-red-500 text-sm"><?php echo $errors['terms']; ?></p>
                            <?php endif; ?>
                            
                            <div>
                                <button type="submit" class="px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300 w-full">Crear Cuenta</button>
                            </div>
                        </form>
                        
                        <div class="mt-6 text-center">
                            <p class="text-secondary">¿Ya tienes una cuenta? <a href="login.php" class="text-primary hover:underline">Inicia sesión</a></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-16 bg-light">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-primary font-heading mb-4">Beneficios de Unirte a TrainSmart</h2>
                <p class="text-secondary max-w-3xl mx-auto">Al registrarte, obtendrás acceso a una serie de beneficios exclusivos para nuestros miembros.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-user-check text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Entrenamiento Personalizado</h3>
                    <p class="text-secondary">Acceso a planes de entrenamiento diseñados específicamente para ti, adaptados a tus objetivos y nivel de condición física.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-mobile-alt text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">App de Seguimiento</h3>
                    <p class="text-secondary">Seguimiento de tu progreso, comunicación con tu entrenador y acceso a tu plan de entrenamiento y nutrición desde cualquier dispositivo.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Comunidad FitLife</h3>
                    <p class="text-secondary">Forma parte de una comunidad de personas con objetivos similares, comparte experiencias y mantente motivado.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-heading">TrainSmart</h3>
                    <p class="text-gray-400 mb-4">Transformando vidas a través del entrenamiento personalizado y un estilo de vida saludable.</p>
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
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 font-heading">Enlaces Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="index.html" class="text-gray-400 hover:text-primary transition duration-300">Inicio</a></li>
                        <li><a href="about.html" class="text-gray-400 hover:text-primary transition duration-300">Nosotros</a></li>
                        <li><a href="plans.html" class="text-gray-400 hover:text-primary transition duration-300">Planes</a></li>
                        <li><a href="testimonials.html" class="text-gray-400 hover:text-primary transition duration-300">Testimonios</a></li>
                        <li><a href="contact.html" class="text-gray-400 hover:text-primary transition duration-300">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 font-heading">Servicios</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-primary transition duration-300">Entrenamiento Personal</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition duration-300">Asesoramiento Nutricional</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition duration-300">Entrenamiento en Grupo</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition duration-300">Programas Online</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary transition duration-300">Evaluación Física</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 font-heading">Contacto</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mt-1 mr-3"></i>
                            <span class="text-gray-400">Calle Principal 123, Madrid, España</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-primary mr-3"></i>
                            <span class="text-gray-400">+34 912 345 678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-primary mr-3"></i>
                            <span class="text-gray-400">info@fitlife.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock text-primary mr-3"></i>
                            <span class="text-gray-400">Lun-Vie: 7:00 - 21:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-12 pt-8 text-center">
                <p class="text-gray-400">&copy; 2023 TrainSmart - Plataforma de Gestión de Entrenamientos. Todos los derechos reservados.</p>
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
    </script>
</body>
</html>
