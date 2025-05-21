<?php
require_once 'personal-trainer/php/password_helper.php';

$passwords = [
    '1234',
    'admin123',
    'trainer456',
    'client789'
];

echo "<h2>Hashes consistentes para contraseñas comunes:</h2>";
echo "<pre>";
foreach ($passwords as $password) {
    echo "Password: " . $password . "\n";
    echo "Hash: " . hashPassword($password) . "\n\n";
}
echo "</pre>";
