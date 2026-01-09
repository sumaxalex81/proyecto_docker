<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibimos el token del campo oculto del formulario
    $token = $_POST['token_login']; 
    $pass = $_POST['contrasenya'];
    $confirmar = $_POST['confirmar'];

    if ($pass !== $confirmar) {
        die("Las contraseñas no coinciden.");
    }

    // Buscamos el usuario por el token
    $stmt = $con->prepare("SELECT usuarioID FROM dato WHERE token_login = ? AND tokenTemporal > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $fila = $res->fetch_assoc();
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $update = $con->prepare("UPDATE dato SET contrasenya = ?, token_login = NULL, tokenTemporal = NULL WHERE usuarioID = ?");
        $update->bind_param("si", $hash, $fila['usuarioID']);
        $update->execute();

        echo "Contraseña actualizada correctamente. <a href='login.php'>Inicia sesión</a>";
    } else {
        echo "Token no válido o expirado. Solicita una nueva recuperación.";
    }
}
?>