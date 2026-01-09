<?php
require_once('cabecera.php');
require_once('conexion.php');


if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

if (isset($_POST['usuarioID'])) {
    $id = (int) $_POST['usuarioID'];


    $sqlCheck = "SELECT usuario FROM dato WHERE usuarioID = ?";
    $stmtCheck = $con->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $usuarioData = $result->fetch_assoc();

    if ($usuarioData && $usuarioData['usuario'] === 'Admin') {
        header("Location: usuarios.php?msg=error_admin");
        exit;
    }

   
    $stmtPub = $con->prepare("DELETE FROM publicaciones WHERE usuario = (SELECT usuario FROM dato WHERE usuarioID = ?)");
    $stmtPub->bind_param("i", $id);
    $stmtPub->execute();

    $stmtCom = $con->prepare("DELETE FROM comentarios WHERE usuario = (SELECT usuario FROM dato WHERE usuarioID = ?)");
    $stmtCom->bind_param("i", $id);
    $stmtCom->execute();


    $stmt = $con->prepare("DELETE FROM dato WHERE usuarioID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: usuarios.php?msg=eliminado");
    exit;
} else {
    header("Location: usuarios.php");
    exit;
}
