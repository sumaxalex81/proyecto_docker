<?php
require_once('conexion.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_SESSION['usuario'];
    $contenido = $_POST['contenido'];
    $categoria = $_POST['categoria'];
    $nombreArchivo = null;
    $nombreVideo = null;


    if (!empty($_FILES['imagen']['name'])) {
        $carpetaDestino = "../img/";
        $nombreOriginal = $_FILES['imagen']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif','jfif'];

        if (in_array($extension, $tiposPermitidos)) {
            $nombreArchivo = uniqid("img_") . "." . $extension;
            $rutaDestino = $carpetaDestino . $nombreArchivo;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                exit("Error al subir la imagen.");
            }
        } else {
            exit("Solo se permiten imágenes JPG, JPEG, PNG o GIF.");
        }
    }


    if (!empty($_FILES['video']['name'])) {
        $carpetaDestino = "../videos/";
        $nombreOriginal = $_FILES['video']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $tiposPermitidos = ['mp4', 'webm', 'ogg'];

        if (!in_array($extension, $tiposPermitidos)) {
            exit("Solo se permiten videos MP4, WEBM u OGG.");
        }

 
        $tmpFile = $_FILES['video']['tmp_name'];
        $ffprobe = "/usr/bin/ffprobe"; 
        $cmd = "$ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($tmpFile);
        $duracion = floatval(shell_exec($cmd));

        if ($duracion > 60) {
            exit("El video no puede durar más de 60 segundos.");
        }

        $nombreVideo = uniqid("vid_") . "." . $extension;
        $rutaDestino = $carpetaDestino . $nombreVideo;
        if (!move_uploaded_file($tmpFile, $rutaDestino)) {
            exit("Error al subir el video.");
        }
    }

    $stmt = $con->prepare("INSERT INTO publicaciones (usuario, contenido, categoria, imagen, video) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $usuario, $contenido, $categoria, $nombreArchivo, $nombreVideo);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>
