<?php
// Initialize variables
$email = '';
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'El email es obligatorio';
    } else {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
    }
    
    // If no errors, process the subscription
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Check if the email already exists in your subscribers list
        // 2. Add the email to your subscribers database or send it to your email marketing service
        // 3. Set a success flag
        
        // For this demo, we'll just set a success flag
        $success = true;
        
        // Optional: Send a confirmation email (commented out for demo)
        /*
        $to = $email;
        $subject = "Confirmación de suscripción - FitLife";
        $message = "Gracias por suscribirte a nuestro newsletter. Recibirás noticias, consejos y ofertas exclusivas de FitLife.";
        $headers = "From: info@fitlife.com\r\n";
        $headers .= "Reply-To: info@fitlife.com\r\n";
        
        mail($to, $subject, $message, $headers);
        */
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - FitLife Entrenamiento Personal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">
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
                    <a href="../index.html" class="text-secondary hover:text-primary font-medium transition duration-300">Inicio</a>
                    <a href="../about.html" class="text-secondary hover:text-primary font-medium transition duration-300">Nosotros</a>
                    <a href="../plans.html" class="text-secondary hover:text-primary font-medium transition duration-300">Planes</a>
                    <a href="../testimonials.html" class="text-secondary hover:text-primary font-medium transition duration-300">Testimonios</a>
                    <a href="../contact.html" class="text-secondary hover:text-primary font-medium transition duration-300">Contacto</a>
                </div>
                <div class="hidden md:flex space-x-4">
                    <a href="../login.php" class="px-4 py-2 text-secondary font-medium hover:text-primary transition duration-300">Iniciar Sesión</a>
                    <a href="../register.php" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">Registrarse</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-secondary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <a href="../index.html" class="block py-2 text-secondary hover:text-primary font-medium">Inicio</a>
                <a href="../about.html" class="block py-2 text-secondary hover:text-primary font-medium">Nosotros</a>
                <a href="../plans.html" class="block py-2 text-secondary hover:text-primary font-medium">Planes</a>
                <a href="../testimonials.html" class="block py-2 text-secondary hover:text-primary font-medium">Testimonios</a>
                <a href="../contact.html" class="block py-2 text-secondary hover:text-primary font-medium">Contacto</a>
                <div class="flex space-x-4 mt-4">
                    <a href="../login.php" class="px-4 py-2 text-secondary font-medium hover:text-primary transition duration-300">Iniciar Sesión</a>
                    <a href="../register.php" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Newsletter Response Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-8 rounded-lg mb-8">
                        <i class="fas fa-envelope-open-text text-5xl text-green-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-secondary font-heading mb-4">¡Suscripción Exitosa!</h2>
                        <p class="text-gray-600 mb-6">Gracias por suscribirte a nuestro newsletter. A partir de ahora recibirás consejos de entrenamiento, nutrición y bienestar, además de ofertas exclusivas y novedades.</p>
                        <div class="bg-white p-4 rounded-lg border border-green-200 inline-block">
                            <p class="font-medium text-gray-700"><?php echo htmlspecialchars($email); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-8 rounded-lg mb-8">
                        <i class="fas fa-exclamation-circle text-5xl text-red-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-secondary font-heading mb-4">Ha ocurrido un error</h2>
                        <p class="text-gray-600 mb-6">Lo sentimos, ha habido un problema al procesar tu suscripción. Por favor, verifica la información e inténtalo de nuevo.</p>
                        <ul class="text-left mb-6">
                            <?php foreach ($errors as $error): ?>
                                <li class="mb-1"><i class="fas fa-times-circle mr-2"></i> <?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="../index.html" class="px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-home mr-2"></i> Volver al Inicio
                    </a>
                    <?php if (!$success): ?>
                        <a href="../contact.html#newsletter" class="px-6 py-3 bg-secondary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                            <i class="fas fa-redo mr-2"></i> Intentar de Nuevo
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section (only shown on success) -->
    <?php if ($success): ?>
    <section class="py-16 bg-light">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-secondary font-heading mb-4">¿Qué Recibirás en Nuestro Newsletter?</h2>
                <p class="text-gray-600 max-w-3xl mx-auto">Nuestro newsletter está diseñado para ayudarte a mantenerte informado y motivado en tu viaje hacia un estilo de vida más saludable.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-dumbbell text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary mb-4">Consejos de Entrenamiento</h3>
                    <p class="text-gray-600">Rutinas efectivas, técnicas correctas y estrategias para maximizar tus resultados.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-apple-alt text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary mb-4">Nutrición y Recetas</h3>
                    <p class="text-gray-600">Consejos nutricionales, recetas saludables y planes de alimentación para complementar tu entrenamiento.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-percentage text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary mb-4">Ofertas Exclusivas</h3>
                    <p class="text-gray-600">Descuentos especiales, promociones y acceso anticipado a nuevos servicios y programas.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transition-transform duration-300 hover:transform hover:scale-105">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bullhorn text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary mb-4">Novedades y Eventos</h3>
                    <p class="text-gray-600">Mantente informado sobre las últimas novedades, eventos y actividades de FitLife.</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-heading">FitLife</h3>
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
                        <li><a href="../index.html" class="text-gray-400 hover:text-primary transition duration-300">Inicio</a></li>
                        <li><a href="../about.html" class="text-gray-400 hover:text-primary transition duration-300">Nosotros</a></li>
                        <li><a href="../plans.html" class="text-gray-400 hover:text-primary transition duration-300">Planes</a></li>
                        <li><a href="../testimonials.html" class="text-gray-400 hover:text-primary transition duration-300">Testimonios</a></li>
                        <li><a href="../contact.html" class="text-gray-400 hover:text-primary transition duration-300">Contacto</a></li>
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
                <p class="text-gray-400">&copy; 2023 FitLife - Entrenamiento Personal. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
