<?php 
require_once('conexion.php');
require_once('cabecera.php');


date_default_timezone_set('Europe/Madrid');

if (isset($_GET['token_login'])) {
    $miToken = $_GET['token_login'];
    

    $ahora = date("Y-m-d H:i:s");

    $consulta = $con->prepare("SELECT * FROM dato WHERE token_login=? AND tokenTemporal > ?");
    $consulta->bind_param("ss", $miToken, $ahora);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {

?>

<div class="login_form">
    <h2>Restablecer Contraseña</h2>
    <form action="actualizar_password.php" method="post">
        <input type="hidden" name="token_login" value="<?php echo htmlspecialchars($miToken); ?>" />

        <div class="campo-contrasena">
            <label for="contrasenya">Contraseña nueva:</label><br>
            <input type="password" name="contrasenya" id="contrasenya" placeholder="Escribe tu nueva contraseña" required>
        </div>

        <div class="campo-contrasena">
            <label for="confirmar">Confirmar contraseña:</label><br>
            <input type="password" name="confirmar" id="confirmar" placeholder="Confirma tu contraseña" required>
        </div>

        <button type="submit">Actualizar Contraseña</button>
    </form>
</div>

<?php 
    } else {
       
        echo "<div class='login_form' style='text-align:center;'>";
        echo "<h3 style='color:red;'>El enlace no es válido o ha caducado.</h3>";
        
   
        echo "<p style='color:white; font-size:12px;'>Debug info:<br>";
        echo "Token recibido: " . htmlspecialchars($miToken) . "<br>";
        echo "Hora actual (PHP): " . $ahora . "<br>";
        echo "Posible causa: Token no encontrado o fecha expirada.</p>";
        
        echo "<a href='recuperar.php' style='color:#4CAF50;'>Volver a solicitar</a>";
        echo "</div>";
    }
} else {
    echo "<div class='login_form'><p>Acceso denegado.</p></div>";
}
?>