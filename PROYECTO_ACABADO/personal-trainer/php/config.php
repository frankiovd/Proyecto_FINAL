<?php
// Parámetros de conexión para XAMPP en puerto 3307
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'proyecto');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 0 for local development

// Función para establecer headers de seguridad y CORS
function establecerHeaders() {
    // CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: true');
    
    // Security headers - adjusted for OAuth popups
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    
    // Remove COOP and COEP headers that were causing issues with OAuth popups
    // header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
    // header('Cross-Origin-Embedder-Policy: require-corp');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

// Función para conectar a la base de datos
function conectarDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            throw new Exception('Error de conexión: ' . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Error de conexión: " . $e->getMessage());
        throw new Exception('Error al conectar con la base de datos');
    }
}

// Función para validar la sesión
function validarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit();
    }
    
    if (!isset($_SESSION['ultimo_acceso'])) {
        session_regenerate_id(true);
        $_SESSION['ultimo_acceso'] = time();
    } else if (time() - $_SESSION['ultimo_acceso'] > 3600) {
        session_destroy();
        header('Location: ../login.php?expired=1');
        exit();
    }
    
    $_SESSION['ultimo_acceso'] = time();
}

// Función para sanitizar input
function sanitizarInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para manejar errores de base de datos
function manejarErrorDB($conn, $error) {
    error_log("Error de base de datos: " . $error);
    if ($conn) {
        error_log("Error MySQL: " . $conn->error);
    }
    throw new Exception('Ha ocurrido un error en la base de datos');
}

// Función para validar el token CSRF
function validarCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: ../error.php?error=csrf');
            exit();
        }
    }
    
    return $_SESSION['csrf_token'];
}

// Función para verificar si el usuario tiene permisos
function verificarPermisos($permisoRequerido) {
    if (!isset($_SESSION['permisos']) || !in_array($permisoRequerido, $_SESSION['permisos'])) {
        header('Location: ../error.php?error=unauthorized');
        exit();
    }
}

// Función para registrar actividad
function registrarActividad($usuario_id, $accion, $detalles = '') {
    try {
        $conn = conectarDB();
        $sql = "INSERT INTO log_actividad (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
    }
}

// Establecer headers por defecto
establecerHeaders();
?>
