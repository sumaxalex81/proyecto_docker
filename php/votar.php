<?php
require_once('conexion.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo "Debes iniciar sesión.";
    exit();
}

$usuario = $_SESSION['usuario'];
$publicacion_id = intval($_POST['publicacion_id']);
$tipo = $_POST['tipo'] === 'up' ? 'up' : 'down';

$sqlCheck = "SELECT * FROM votos WHERE usuario='$usuario' AND publicacion_id=$publicacion_id";
$res = $con->query($sqlCheck);

if ($res->num_rows > 0) {
    $voto = $res->fetch_assoc();
    if ($voto['tipo'] === $tipo) {
        // Si vuelve a hacer clic en el mismo voto=eliminar
        $con->query("DELETE FROM votos WHERE id=" . $voto['id']);
    } else {
        // Cambiar voto
        $con->query("UPDATE votos SET tipo='$tipo' WHERE id=" . $voto['id']);
    }
} else {
    // Nuevo voto
    $con->query("INSERT INTO votos (usuario, publicacion_id, tipo) VALUES ('$usuario', $publicacion_id, '$tipo')");
}


$sqlVotos = "SELECT 
                SUM(CASE WHEN tipo='up' THEN 1 ELSE 0 END) as upvotes,
                SUM(CASE WHEN tipo='down' THEN 1 ELSE 0 END) as downvotes
             FROM votos WHERE publicacion_id = $publicacion_id";
$resVotos = $con->query($sqlVotos);
$votos = $resVotos->fetch_assoc();


$up = $votos['upvotes'] ?? 0;
$down = $votos['downvotes'] ?? 0;
$puntuacion = max(0, $up - $down);

echo $puntuacion;
?>
