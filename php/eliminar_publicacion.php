<?php
require_once('cabecera.php');
require_once('conexion.php');

if (!isset($_SESSION['usuario'])) {
    echo "<p style='color:white;'>Debes iniciar sesión para eliminar tus publicaciones.</p>";
    exit;
}

if (isset($_POST['id_publicacion'])) {
    $id = intval($_POST['id_publicacion']);
    $usuario = $_SESSION['usuario'];


    $sqlCheck = "SELECT * FROM publicaciones WHERE id = ? AND usuario = ?";
    $stmtCheck = $con->prepare($sqlCheck);
    $stmtCheck->bind_param("is", $id, $usuario);
    $stmtCheck->execute();
    $resultado = $stmtCheck->get_result();

    if ($resultado && $resultado->num_rows > 0) {
  
        $sqlDelete = "DELETE FROM publicaciones WHERE id = ? AND usuario = ?";
        $stmtDel = $con->prepare($sqlDelete);
        $stmtDel->bind_param("is", $id, $usuario);
        $stmtDel->execute();

        header("Location: perfil.php?usuario=$usuario&msg=eliminado");
        exit;
    } else {
        echo "<p style='color:white;'>No puedes eliminar publicaciones de otros usuarios.</p>";
    }
} else {
    echo "<p style='color:white;'>Publicación no especificada.</p>";
}

header ('Location: perfil.php'); 
?>
