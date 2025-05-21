<?php
require_once 'php/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers de seguridad
establecerHeaders();

// Registrar la actividad de cierre de sesión si hay un usuario
if (isset($_SESSION['usuario_id'])) {
    registrarActividad($_SESSION['usuario_id'], 'logout', 'Cierre de sesión exitoso');
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login con mensaje de éxito
header("Location: login.php?logout=success");
exit();
?>
