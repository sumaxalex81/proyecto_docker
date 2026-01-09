<?php

require_once('conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$publicacion_id = isset($_POST['publicacion_id']) ? intval($_POST['publicacion_id']) : 0;
$contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';
$comentario_padre = (isset($_POST['comentario_padre']) && is_numeric($_POST['comentario_padre'])) ? intval($_POST['comentario_padre']) : null;

$nombreImagen = null;
$nombreVideo = null;


$maxImageSize = 5 * 1024 * 1024; 
$maxVideoSize = 50 * 1024 * 1024;


if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
    if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK && $_FILES['imagen']['size'] <= $maxImageSize) {
        $carpetaDestinoImg = __DIR__ . "/../img/";
        if (!is_dir($carpetaDestinoImg)) mkdir($carpetaDestinoImg, 0755, true);
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'])) {
            $nombreImagen = uniqid("com_img_") . "." . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestinoImg . $nombreImagen);
        }
    }
}

if (isset($_FILES['video']) && !empty($_FILES['video']['name'])) {
    if ($_FILES['video']['error'] === UPLOAD_ERR_OK && $_FILES['video']['size'] <= $maxVideoSize) {
        $carpetaDestinoVid = __DIR__ . "/../videos/";
        if (!is_dir($carpetaDestinoVid)) mkdir($carpetaDestinoVid, 0755, true);
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
            $nombreVideo = uniqid("com_vid_") . "." . $ext;
            move_uploaded_file($_FILES['video']['tmp_name'], $carpetaDestinoVid . $nombreVideo);
        }
    }
}

if ($contenido === '' && !$nombreImagen && !$nombreVideo) {
    header("Location: comentarios.php?id=" . $publicacion_id . "&error=vacio");
    exit();
}


try {
    $sql = "INSERT INTO comentarios (publicacion_id, usuario, contenido, comentario_padre, imagen, video, fecha) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ississ", $publicacion_id, $usuario, $contenido, $comentario_padre, $nombreImagen, $nombreVideo);
    $stmt->execute();
    $stmt->close();

   
    
    //Averiguar a quién notificar
    $usuario_destino = "";
    $tipo = "";

    if ($comentario_padre) {
      
        $sqlTarget = "SELECT usuario FROM comentarios WHERE id = ?";
        $tipo = 'respuesta';
        $stmtT = $con->prepare($sqlTarget);
        $stmtT->bind_param("i", $comentario_padre);
    } else {
      
        $sqlTarget = "SELECT usuario FROM publicaciones WHERE id = ?";
        $tipo = 'comentario';
        $stmtT = $con->prepare($sqlTarget);
        $stmtT->bind_param("i", $publicacion_id);
    }
    
    $stmtT->execute();
    $resT = $stmtT->get_result();
    if ($rowT = $resT->fetch_assoc()) {
        $usuario_destino = $rowT['usuario'];
    }
    $stmtT->close();


    if (!empty($usuario_destino) && $usuario_destino !== $usuario) {
        $sqlNotif = "INSERT INTO notificaciones (usuario_destino, usuario_origen, publicacion_id, tipo, fecha) VALUES (?, ?, ?, ?, NOW())";
        $stmtN = $con->prepare($sqlNotif);
        $stmtN->bind_param("ssis", $usuario_destino, $usuario, $publicacion_id, $tipo);
        $stmtN->execute();
        $stmtN->close();
    }

} catch (Exception $e) {

    if ($nombreImagen && file_exists(__DIR__ . "/../img/" . $nombreImagen)) unlink(__DIR__ . "/../img/" . $nombreImagen);
    if ($nombreVideo && file_exists(__DIR__ . "/../videos/" . $nombreVideo)) unlink(__DIR__ . "/../videos/" . $nombreVideo);
    exit("Error: " . $e->getMessage());
}

header("Location: comentarios.php?id=" . $publicacion_id);
exit();
?>