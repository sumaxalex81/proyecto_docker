<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <style>
     
      .caja-errores {
          background-color: #ffcccc;
          border: 1px solid red;
          color: #b30000;
          padding: 10px;
          border-radius: 5px;
          margin: 10px 0;
          font-size: 0.9em;
          text-align: left;
      }
      .caja-errores p {
          margin: 3px 0;
      }
  </style>
</head>
<body>
<?php require_once("cabecera.php"); ?>

<div class="login_form">
  <h2>Registro</h2>

  <form action="guardarregistro.php" method="post">

    <div class="todoform">
      <input type="text" name="nombre" id="nombre" placeholder="Nombre de usuario" 
             value="<?php echo isset($_SESSION['datos_temporales']['nombre']) ? htmlspecialchars($_SESSION['datos_temporales']['nombre']) : ''; ?>">
      
      <input type="text" name="correo" id="correo" placeholder="Correo electrónico"
             value="<?php echo isset($_SESSION['datos_temporales']['correo']) ? htmlspecialchars($_SESSION['datos_temporales']['correo']) : ''; ?>">

      <div class="campo-contrasena">
        <input type="password" name="password" id="password" placeholder="Contraseña">
        <img id="img-contrasenya" class="mostrar-ocultar" src="../img/ojocerrado.png" onclick="cambio()">
      </div>

      <div class="campo-contrasena">
        <input type="password" name="confirmar" id="confirmar" placeholder="Confirmar contraseña" >
        <img id="img-contrasenya2" class="mostrar-ocultar" src="../img/ojocerrado.png" onclick="cambios()">
      </div>

      <?php if (isset($_SESSION['lista_errores']) && count($_SESSION['lista_errores']) > 0): ?>
          <div class="caja-errores">
              <?php 
                  foreach ($_SESSION['lista_errores'] as $mensajeError) {
                      echo "<p>• $mensajeError</p>";
                  }
                  unset($_SESSION['lista_errores']); 
              ?>
          </div>
      <?php endif; ?>

      <label>
        <input type="checkbox" name="terminos" value="1"> Acepto los términos y condiciones.
      </label>

      <button type="submit">Registrarse</button>
    </div>
  </form>
</div>

<script src="../js/ojo.js"></script>

</body>
</html>