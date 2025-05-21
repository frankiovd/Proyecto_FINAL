<?php
require_once '../php/password_helper.php';

if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = hashPassword($password);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Hash - FitLife</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white p-6 rounded-lg shadow">
            <h1 class="text-2xl font-bold mb-6">Generador de Hash</h1>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="text" name="password" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>">
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Generar Hash
                </button>
            </form>

            <?php if (isset($hash)): ?>
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hash Generado:</label>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <code class="text-sm break-all"><?php echo htmlspecialchars($hash); ?></code>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Este hash siempre será el mismo para esta contraseña.
                </p>
            </div>
            <?php endif; ?>

            <div class="mt-6 text-sm text-gray-600">
                <h2 class="font-medium mb-2">Instrucciones:</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Ingresa la contraseña deseada</li>
                    <li>El hash generado será consistente y puede usarse directamente en la base de datos</li>
                    <li>La misma contraseña siempre generará el mismo hash</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
