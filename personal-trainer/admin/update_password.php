<?php
require_once '../php/config.php';

// Iniciar sesión y verificar si es admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Acceso denegado');
}

$message = '';
$users = [];

// Obtener lista de usuarios
try {
    $conn = conectarDB();
    $result = $conn->query("SELECT id, name, email FROM users ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} catch (Exception $e) {
    $message = "Error al cargar usuarios: " . $e->getMessage();
}

// Procesar actualización de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userId = $_POST['user_id'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($userId) || empty($newPassword)) {
            throw new Exception("Usuario y contraseña son requeridos");
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            $message = "Contraseña actualizada exitosamente";
        } else {
            throw new Exception("Error al actualizar la contraseña");
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Generar hash para una contraseña
$generatedHash = '';
if (isset($_POST['password_to_hash'])) {
    $generatedHash = password_hash($_POST['password_to_hash'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Contraseñas - FitLife Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Gestión de Contraseñas</h1>
        
        <?php if ($message): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Actualizar contraseña -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Actualizar Contraseña</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Usuario</label>
                        <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccionar usuario</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                        <input type="text" name="new_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Actualizar Contraseña
                    </button>
                </form>
            </div>

            <!-- Generar hash -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Generar Hash</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="text" name="password_to_hash" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Generar Hash
                    </button>
                </form>
                <?php if ($generatedHash): ?>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Hash Generado:</label>
                    <textarea readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50 p-2" rows="3"><?php echo htmlspecialchars($generatedHash); ?></textarea>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Instrucciones</h2>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Para actualizar la contraseña de un usuario, selecciónalo de la lista y escribe la nueva contraseña.</li>
                <li>Para generar un hash de contraseña (útil para insertar directamente en la base de datos), usa la sección "Generar Hash".</li>
                <li>Los hashes generados son únicos incluso para la misma contraseña, pero todos funcionarán correctamente.</li>
                <li>Se recomienda usar contraseñas seguras que incluyan mayúsculas, minúsculas, números y símbolos.</li>
            </ul>
        </div>
    </div>
</body>
</html>
