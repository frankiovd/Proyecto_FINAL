<?php
require_once 'php/config.php';

// Iniciar sesión y establecer headers de seguridad
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
establecerHeaders();

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Initialize variables
$email = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("=== Inicio de intento de login ===");
        
        // Validar CSRF token
        validarCSRF();
        error_log("CSRF validado correctamente");
        
        // Recoger y sanitizar datos del formulario
        $email = sanitizarInput($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        error_log("Email recibido: " . $email);

        // Validación básica
        if (empty($email)) {
            $errors['email'] = "El email es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "El formato del email no es válido.";
        }

        if (empty($password)) {
            $errors['password'] = "La contraseña es obligatoria.";
        }

            // Si no hay errores de validación
            if (empty($errors)) {
                error_log("Validación básica pasada");
                $conn = conectarDB();
                error_log("Conexión a DB establecida");

                // Consulta segura usando prepared statements con JOIN a user_roles
                $sql = "SELECT u.id, u.email, u.password, u.name, u.status, ur.role 
                        FROM users u 
                        LEFT JOIN user_roles ur ON u.id = ur.user_id 
                        WHERE u.email = ? AND u.status = 'active'";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    error_log("Error en prepare: " . $conn->error);
                    throw new Exception("Error preparando la consulta");
                }

                $stmt->bind_param("s", $email);
                if (!$stmt->execute()) {
                    error_log("Error en execute: " . $stmt->error);
                    throw new Exception("Error ejecutando la consulta");
                }

                $result = $stmt->get_result();
                error_log("Consulta ejecutada. Num rows: " . $result->num_rows);

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    error_log("Usuario encontrado. Role: " . $user['role']);
                    error_log("Hash almacenado: " . $user['password']);
                    
                    if ($password === $user['password']) {
                        error_log("Verificación de contraseña exitosa");
                    // Regenerar ID de sesión por seguridad
                    session_regenerate_id(true);
                    
                    // Establecer variables de sesión
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario'] = $user['email'];
                    $_SESSION['nombre'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['ultimo_acceso'] = time();
                    
                    // Registrar el login exitoso
                    registrarActividad($user['id'], 'login', 'Login exitoso');
                    
                    // Redirigir según el rol
                    switch ($user['role']) {
                        case 'trainer':
                            header("Location: trainer/dashboard.php");
                            break;
                        case 'client':
                            header("Location: dashboard.php");
                            break;
                        case 'admin':
                            // Temporarily redirect admins to regular dashboard
                            header("Location: dashboard.php");
                            break;
                        default:
                            // Si rol desconocido, cerrar sesión
                            session_destroy();
                            header("Location: login.php?error=rol_desconocido");
                            break;
                    }
                    exit();
                } else {
                    $errors['login'] = "Email o contraseña incorrectos.";
                    registrarActividad(0, 'login_fallido', "Email: $email");
                }
            } else {
                $errors['login'] = "Email o contraseña incorrectos.";
                registrarActividad(0, 'login_fallido', "Email: $email");
            }

            $stmt->close();
            $conn->close();
        }
    } catch (Exception $e) {
        error_log("=== Error detallado en login ===");
        error_log("Mensaje: " . $e->getMessage());
        error_log("Archivo: " . $e->getFile());
        error_log("Línea: " . $e->getLine());
        error_log("Trace: " . $e->getTraceAsString());
        
        if ($conn && $conn->error) {
            error_log("Error MySQL: " . $conn->error);
        }
        
        $errors['general'] = "Error: " . $e->getMessage();
    }
}

// Generar nuevo token CSRF
$csrf_token = validarCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TrainSmart</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Firebase App (the core Firebase SDK) -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <!-- Firebase Authentication -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>
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
                    <a href="login.php" class="px-4 py-2 text-primary font-medium transition duration-300">Iniciar Sesión</a>
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
                    <a href="login.php" class="block py-2 text-primary font-medium">Iniciar Sesión</a>
                    <a href="register.php" class="block py-2 text-secondary hover:text-primary font-medium">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Login Form Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto">
                <div class="bg-light p-8 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold text-primary font-heading mb-6">Accede a tu cuenta</h2>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                            <span class="block sm:inline"><?php echo $errors['general']; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors['login'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                            <span class="block sm:inline"><?php echo $errors['login']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="POST" class="space-y-6">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div>
                            <label for="email" class="block text-secondary font-medium mb-2">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                                   class="w-full px-4 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <?php if (isset($errors['email'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-secondary font-medium mb-2">Contraseña</label>
                            <input type="password" id="password" name="password" 
                                   class="w-full px-4 py-2 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <?php if (isset($errors['password'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['password']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember" name="remember" class="mr-2 focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="remember" class="text-secondary">Recordarme</label>
                            </div>
                            <a href="recover-password.php" class="text-primary hover:underline">¿Olvidaste tu contraseña?</a>
                        </div>
                        
                        <div>
                            <button type="submit" class="w-full px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-light text-secondary">O continúa con</span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" onclick="signInWithGoogle()"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-secondary hover:bg-gray-50">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 mr-2" alt="Google logo">
                                <span>Google</span>
                            </button>
                            <button type="button" onclick="signInWithFacebook()"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-secondary hover:bg-gray-50">
                                <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5 mr-2" alt="Facebook logo">
                                <span>Facebook</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-secondary">¿No tienes una cuenta? <a href="register.php" class="text-primary hover:underline">Regístrate</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-8">
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
                </div>
                <div class="mt-4 md:mt-0">
                    <p class="text-gray-400">&copy; 2023 TrainSmart - Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Firebase Configuration and Authentication -->
    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_AUTH_DOMAIN",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_STORAGE_BUCKET",
            messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
            appId: "YOUR_APP_ID"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        // Google Sign In
        function signInWithGoogle() {
            const provider = new firebase.auth.GoogleAuthProvider();
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    // El usuario ha iniciado sesión correctamente
                    const user = result.user;
                    // Enviar los datos del usuario al servidor
                    return fetch('auth/google-callback.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: user.email,
                            name: user.displayName,
                            uid: user.uid,
                            photoURL: user.photoURL,
                            provider: 'google'
                        })
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php';
                    } else {
                        throw new Error(data.message || 'Error en el inicio de sesión');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Error al iniciar sesión con Google: ' + error.message);
                });
        }

        // Facebook Sign In
        function signInWithFacebook() {
            const provider = new firebase.auth.FacebookAuthProvider();
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    // El usuario ha iniciado sesión correctamente
                    const user = result.user;
                    // Enviar los datos del usuario al servidor
                    return fetch('auth/facebook-callback.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: user.email,
                            name: user.displayName,
                            uid: user.uid,
                            photoURL: user.photoURL,
                            provider: 'facebook'
                        })
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php';
                    } else {
                        throw new Error(data.message || 'Error en el inicio de sesión');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Error al iniciar sesión con Facebook: ' + error.message);
                });
        }
    </script>

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
