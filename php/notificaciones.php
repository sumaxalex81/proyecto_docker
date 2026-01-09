<?php
require_once('cabecera.php');
require_once('conexion.php');

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];


$sql = "
    SELECT n.*, d.foto_perfil 
    FROM notificaciones n
    LEFT JOIN dato d ON n.usuario_origen = d.usuario
    WHERE n.usuario_destino = ?
    ORDER BY n.fecha DESC
    LIMIT 50
";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .container-notif {
            width: 600px; margin: 40px auto; color: white;
            background-color: #222; padding: 20px; border-radius: 10px;
        }
        .notif-item {
            display: flex; align-items: center; gap: 15px;
            padding: 15px; border-bottom: 1px solid #444;
            transition: background 0.3s; text-decoration: none; color: white;
            border-radius: 5px;
        }
        .notif-item:hover { background-color: #333; }
        .notif-item.no-leido { background-color: #2a3a2a; border-left: 4px solid #4CAF50; }
        
        .foto-notif {
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover;
            border: 2px solid #4CAF50;
        }
        .info-notif { flex: 1; }
        .fecha-notif { font-size: 0.8em; color: #aaa; }
        .texto-destacado { color: #4CAF50; font-weight: bold; }
        .sin-notif { text-align: center; padding: 20px; color: #aaa; }
    </style>
</head>
<body>

<div class="container-notif">
    <h2>🔔 Tus Notificaciones</h2>
    <hr style="border-color: #444; margin-bottom: 20px;">

    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($row = $resultado->fetch_assoc()): ?>
            <?php
        
            $avatar = "https://ui-avatars.com/api/?name=".urlencode($row['usuario_origen'])."&background=random&color=fff";
            if (!empty($row['foto_perfil']) && file_exists("../img/perfiles/" . $row['foto_perfil'])) {
                $avatar = "../img/perfiles/" . htmlspecialchars($row['foto_perfil']);
            }
            
         
            $accion = ($row['tipo'] === 'respuesta') ? "respondió a tu comentario" : "comentó en tu publicación";
            $claseLeido = ($row['leido'] == 0) ? 'no-leido' : '';
            
         
            $link = "check_notificacion.php?id=" . $row['id'] . "&pub=" . $row['publicacion_id'];
            ?>

            <a href="<?= $link ?>" class="notif-item <?= $claseLeido ?>">
                <img src="<?= $avatar ?>" class="foto-notif">
                <div class="info-notif">
                    <span class="texto-destacado"><?= htmlspecialchars($row['usuario_origen']) ?></span> 
                    <?= $accion ?>
                    <br>
                    <span class="fecha-notif"><?= date("d/m/Y H:i", strtotime($row['fecha'])) ?></span>
                </div>
                <?php if ($row['leido'] == 0): ?>
                    <span style="color:#4CAF50;">●</span>
                <?php endif; ?>
            </a>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="sin-notif">
            No tienes notificaciones nuevas.
        </div>
    <?php endif; ?>
</div>

</body>
</html>