<?php
session_start();
require_once('conexion.php');

// Solo Admin puede eliminar
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'Admin') {
    die("No tienes permisos para eliminar publicaciones.");
}

// Verificar que se recibió el ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID de publicación no válido.");
}

$idPub = (int)$_POST['id'];


$pagina = isset($_POST['pagina']) ? $_POST['pagina'] : 'index.php';


$sqlUsuario = $con->prepare("SELECT usuario FROM publicaciones WHERE id = ?");
$sqlUsuario->bind_param("i", $idPub);
$sqlUsuario->execute();
$resUsuario = $sqlUsuario->get_result();
$usuario = $resUsuario && $resUsuario->num_rows > 0 ? $resUsuario->fetch_assoc()['usuario'] : "usuario no especificado";

// Eliminar la publicación
$sqlEliminar = $con->prepare("DELETE FROM publicaciones WHERE id = ?");
$sqlEliminar->bind_param("i", $idPub);
if ($sqlEliminar->execute()) {
    //eliminar votos asociados
    $sqlVotos = $con->prepare("DELETE FROM votos WHERE publicacion_id = ?");
    $sqlVotos->bind_param("i", $idPub);
    $sqlVotos->execute();


    header("Location: $pagina?mensaje=Publicación de $usuario eliminada correctamente");
    exit;
} else {
    die("Error al eliminar la publicación.");
}
