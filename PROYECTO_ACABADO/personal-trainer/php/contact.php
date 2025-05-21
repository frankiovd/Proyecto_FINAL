<?php
// Initialize variables
$name = $email = $phone = $subject = $message = '';
$errors = [];
$success = false;

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
    
    // Get phone (optional)
    if (!empty($_POST['phone'])) {
        $phone = trim($_POST['phone']);
    }
    
    // Validate subject
    if (empty($_POST['subject'])) {
        $errors['subject'] = 'El asunto es obligatorio';
    } else {
        $subject = trim($_POST['subject']);
    }
    
    // Validate message
    if (empty($_POST['message'])) {
        $errors['message'] = 'El mensaje es obligatorio';
    } else {
        $message = trim($_POST['message']);
    }
    
    // Check privacy acceptance
    if (!isset($_POST['privacy'])) {
        $errors['privacy'] = 'Debes aceptar la política de privacidad';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Store the message in a database
        // 2. Send an email notification
        // 3. Set a success flag
        
        // For this demo, we'll just set a success flag
        $success = true;
        
        // Optional: Send an email (commented out for demo)
        /*
        $to = 'info@fitlife.com';
        $email_subject = "Nuevo mensaje de contacto: $subject";
        $email_body = "Has recibido un nuevo mensaje de contacto.\n\n".
            "Detalles:\n\nNombre: $name\n".
            "Email: $email\n".
            "Teléfono: $phone\n".
            "Asunto: $subject\n".
            "Mensaje: $message\n";
        $headers = "From: $email\n";
        $headers .= "Reply-To: $email\n";
        
        mail($to, $email_subject, $email_body, $headers);
        */
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - FitLife Entrenamiento Personal</title>
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
                    <a href="../contact.html" class="text-primary font-medium transition duration-300">Contacto</a>
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
                <a href="../contact.html" class="block py-2 text-primary font-medium">Contacto</a>
                <div class="flex space-x-4 mt-4">
                    <a href="../login.php" class="px-4 py-2 text-secondary font-medium hover:text-primary transition duration-300">Iniciar Sesión</a>
                    <a href="../register.php" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition duration-300">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contact Response Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-8 rounded-lg mb-8">
                        <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-secondary font-heading mb-4">¡Mensaje Enviado Correctamente!</h2>
                        <p class="text-gray-600 mb-6">Gracias por contactarnos. Hemos recibido tu mensaje y nos pondremos en contacto contigo lo antes posible.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-8 rounded-lg mb-8">
                        <i class="fas fa-exclamation-circle text-5xl text-red-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-secondary font-heading mb-4">Ha ocurrido un error</h2>
                        <p class="text-gray-600 mb-6">Lo sentimos, ha habido un problema al procesar tu mensaje. Por favor, verifica la información e inténtalo de nuevo.</p>
                        <ul class="text-left mb-6">
                            <?php foreach ($errors as $error): ?>
                                <li class="mb-1"><i class="fas fa-times-circle mr-2"></i> <?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="../contact.html" class="px-6 py-3 bg-secondary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al Formulario
                    </a>
                    <a href="../index.html" class="px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-home mr-2"></i> Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </section>

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
