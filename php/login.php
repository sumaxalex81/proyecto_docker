<?php require_once('cabecera.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="body_login">

<div class="login_form">
    <h2>Iniciar Sesión</h2>

    <?php
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'user') {
            echo "<p style='color:red;'>Usuario no encontrado.</p>";
        } elseif ($_GET['error'] === 'pass') {
            echo "<p style='color:red;'>Contraseña incorrecta.</p>";
        }
    }
    ?>

    <form action="validacionlogin.php" method="post">
        <input type="text" name="usuario" placeholder="Usuario" 
               value="<?php echo isset($_COOKIE['usuario']) ? htmlspecialchars($_COOKIE['usuario']) : ''; ?>" required>

        <div class="campo-contrasena">
            <input type="password" name="password" placeholder="Contraseña"
                   value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>" required>
            <img id="img-contrasenya" class="mostrar-ocultar" src="../img/ojocerrado.png" onclick="cambio()">
        </div>

        <label style="color:white;">
            <input type="checkbox" name="recordar" <?php echo isset($_COOKIE['usuario']) ? 'checked' : ''; ?>> Recordarme
        </label>

        <button type="submit" name="login">Entrar</button>
    </form>

    <div style="margin-top:15px;">
        <a href="recuperar.php" style="color:#ddd; text-decoration:underline;">¿Has olvidado tu contraseña?</a>
    </div>
</div>

<script src="../js/ojo.js"></script>
</body>
</html>
