<?php
$passwordIngresada = '1234';
$hashGuardado = '$2y$10$kS/Yu5UdvQzFepNODx6LD.buA7lIfxG0UsH2cgT5KHgIcA1z7FJ7G';

echo "Contraseña ingresada: $passwordIngresada<br>";
echo "Hash guardado: $hashGuardado<br><br>";

if (password_verify($passwordIngresada, $hashGuardado)) {
    echo "<b>✅ Coinciden: acceso permitido</b>";
} else {
    echo "<b>❌ No coinciden: acceso denegado</b>";
}
