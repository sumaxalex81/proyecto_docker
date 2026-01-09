<?php
require_once('conexion.php');
session_start();

if (!isset($_SESSION['usuario']) || !isset($_GET['id']) || !isset($_GET['pub'])) {
    header("Location: index.php");
    exit();
}

$notif_id = intval($_GET['id']);
$pub_id = intval($_GET['pub']);
$usuario = $_SESSION['usuario'];

// Marcar como leido
$sql = "UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_destino = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("is", $notif_id, $usuario);
$stmt->execute();

// Redirigir a la publicacion
header("Location: comentarios.php?id=" . $pub_id);
exit();
?>