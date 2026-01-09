<?php 
require_once("cabecera.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
  <div class="login_form">
    <h2>Recuperar contraseña</h2>

    <?php
    if (isset($_GET['msg'])) echo "<p style='color:green;'>".$_GET['msg']."</p>";
    if (isset($_GET['error'])) echo "<p style='color:red;'>".$_GET['error']."</p>";
    ?>

    <form action="enviar_recuperacion.php" method="post">
      <input type="email" name="correo" placeholder="Introduce tu correo" required>
      <button type="submit">Enviar enlace</button>
    </form>
  </div>
</body>
</html>
