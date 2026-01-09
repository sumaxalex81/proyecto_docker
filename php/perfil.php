<?php
require_once('cabecera.php');
require_once('conexion.php');

if (!isset($_GET['usuario'])) {
    echo "<p style='color:white;'>Usuario no especificado.</p>";
    exit;
}

$usuario = $_GET['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($usuario); ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<div class="contenido_publicaciones">
    <div class="foronoticias">
        <h2 style="color:white;">Publicaciones de <?php echo htmlspecialchars($usuario); ?></h2>
        <?php
        if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
            echo "<p style='color: #4CAF50;'>✅ Publicación eliminada correctamente.</p>";
        }
        ?>
    </div>

    <div class="lista_publicaciones">
        <?php
        $sql = "
            SELECT p.*, 
                   COALESCE(SUM(CASE WHEN v.tipo = 'up' THEN 1 WHEN v.tipo = 'down' THEN -1 ELSE 0 END), 0) AS puntuacion
            FROM publicaciones p
            LEFT JOIN votos v ON p.id = v.publicacion_id
            WHERE p.usuario = ?
            GROUP BY p.id
            ORDER BY p.fecha DESC
        ";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $idPub = $fila['id'];

                $sqlVotos = "SELECT 
                                SUM(CASE WHEN tipo='up' THEN 1 ELSE 0 END) as upvotes,
                                SUM(CASE WHEN tipo='down' THEN 1 ELSE 0 END) as downvotes
                             FROM votos WHERE publicacion_id = $idPub";
                $resVotos = $con->query($sqlVotos);
                $votos = $resVotos->fetch_assoc();
                $up = $votos['upvotes'] ?? 0;
                $down = $votos['downvotes'] ?? 0;
                $puntuacion = max(0, $up - $down);

                echo "<div class='publicacion' data-id='$idPub'>";
                echo "<p class='usuario'><strong>" . htmlspecialchars($fila['usuario']) . "</strong> - " . htmlspecialchars($fila['fecha']) . "</p>";
                echo "<p class='categoria'><em>Categoría: " . htmlspecialchars($fila['categoria']) . "</em></p>";
                echo "<p class='contenido'>" . nl2br(htmlspecialchars($fila['contenido'])) . "</p>";

                if (!empty($fila['imagen'])) {
                    echo "<img src='../img/" . htmlspecialchars($fila['imagen']) . "' alt='Imagen' class='imagen_publicacion'>";
                }

                echo "<div class='votos'>";
                echo "<img src='../img/upvote.png' class='btn-like bloqueado'>";
                echo "<span>$puntuacion</span>";
                echo "<img src='../img/downvote.png' class='btn-dislike bloqueado'>";
                echo "</div>";


                if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === $usuario) {
                    echo "<form action='eliminar_publicacion.php' method='POST' class='form-eliminar' onsubmit='return confirmarEliminacion();'>";
                    echo "<input type='hidden' name='id_publicacion' value='$idPub'>";
                    echo "<button type='submit' class='btn-eliminar'>Eliminar</button>";
                    echo "</form>";
                }

                echo "</div>"; 
            }
        } else {
            echo "<p style='color:white;'>Este usuario aún no tiene publicaciones.</p>";
        }
        ?>
    </div>
</div>

<script>
function confirmarEliminacion() {
    return confirm('¿Seguro que deseas eliminar esta publicación? Esta acción no se puede deshacer.');
}
</script>

</body>
</html>
