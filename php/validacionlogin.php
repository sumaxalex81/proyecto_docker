<?php
session_start();
require_once('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $sql = "SELECT usuario, contrasenya FROM dato WHERE usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        if (password_verify($password, $fila['contrasenya'])) {
            $_SESSION['usuario'] = $fila['usuario'];

            if (!empty($_POST['recordar'])) {
             
                setcookie('usuario', $usuario, time() + (86400 * 30), "/");
                setcookie('password', $password, time() + (86400 * 30), "/");
            } else {
             
                setcookie('usuario', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
            }

            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=pass");
            exit;
        }
    } else {
        header("Location: login.php?error=user");
        exit;
    }

    $stmt->close();
    $con->close();
}
?>
