<?php
function getClientInfoSection() {
    ob_start();
    ?>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-secondary font-heading mb-4">Para Clientes</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Una experiencia personalizada para alcanzar tus objetivos de fitness.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-light p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-mobile-alt text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Acceso Móvil</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>App móvil intuitiva</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Acceso a planes en cualquier lugar</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Notificaciones personalizadas</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-light p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tasks text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Seguimiento Personal</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Registro de entrenamientos</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Seguimiento de medidas</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Fotos de progreso</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-light p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-video text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Recursos Multimedia</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Videos de ejercicios</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Guías técnicas</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Consejos nutricionales</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 text-center">
                <a href="register.php" class="inline-block px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300 mr-4">
                    Registrarse Ahora
                </a>
                <a href="login.php" class="inline-block px-6 py-3 bg-secondary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                    Iniciar Sesión
                </a>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function getTrainerInfoSection() {
    ob_start();
    ?>
    <section class="py-16 bg-light">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-secondary font-heading mb-4">Para Entrenadores</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Herramientas profesionales para gestionar tu negocio de entrenamiento personal.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users-cog text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Gestión de Clientes</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Panel de control personalizado</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Perfiles detallados de clientes</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Historial de entrenamiento</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-clipboard-list text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Creación de Planes</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Editor de planes intuitivo</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Biblioteca de ejercicios</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Plantillas personalizables</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-bar text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-secondary text-center mb-4">Análisis y Reportes</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Estadísticas detalladas</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Informes de progreso</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-primary mr-2"></i>
                            <span>Métricas de rendimiento</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 text-center">
                <a href="register.php" class="inline-block px-6 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300 mr-4">
                    Registrarse como Entrenador
                </a>
                <a href="login.php" class="inline-block px-6 py-3 bg-secondary text-white font-medium rounded-md hover:bg-opacity-90 transition duration-300">
                    Iniciar Sesión
                </a>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
?>
