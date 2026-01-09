<?php
require_once('cabecera.php');
require_once('conexion.php');


if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'Admin') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuarios</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<div class="contenido_publicaciones">
    <h2 style="color:white;">Panel de administración de usuarios</h2>

    <?php
  
    if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
        echo "<p style='color: #4CAF50;'>✅ Usuario eliminado correctamente.</p>";
    }

    $sql = "SELECT usuarioID, usuario, email, foto_perfil FROM dato ORDER BY usuarioID ASC";
    $resultado = $con->query($sql);

    if ($resultado && $resultado->num_rows > 0): ?>
        <table border="1" style="width:100%; color:white; text-align:center; border-collapse:collapse;">
            <tr style="background-color:#333;">
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Foto de perfil</th>
                <th>Acciones</th>
            </tr>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['usuarioID']); ?></td>
                    <td><?= htmlspecialchars($fila['usuario']); ?></td>
                    <td><?= htmlspecialchars($fila['email']); ?></td>
                    <td>
                        <?php if (!empty($fila['foto_perfil'])): ?>
                            <img src="../img/perfiles/<?= htmlspecialchars($fila['foto_perfil']); ?>" alt="foto" width="60" height="60" style="border-radius:50%;">
                        <?php else: ?>
                            <span>Sin foto</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($fila['usuario'] !== 'Admin'): ?>
                            <form action="eliminar_usuario.php" method="POST" onsubmit="return confirmarEliminarUsuario();">
                                <input type="hidden" name="usuarioID" value="<?= $fila['usuarioID']; ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span>No permitido</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="color:white;">No hay usuarios registrados.</p>
    <?php endif; ?>
</div>

<script>
function confirmarEliminarUsuario() {
    return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.');
}
</script>

</body>
</html>
