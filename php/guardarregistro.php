<?php
session_start();
require_once("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $errores = [];

    // Recogemos datos
    $nombre    = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : "";
    $correo    = isset($_POST["correo"]) ? trim($_POST["correo"]) : "";
    $password  = isset($_POST["password"]) ? $_POST["password"] : "";
    $confirmar = isset($_POST["confirmar"]) ? $_POST["confirmar"] : "";
    $terminos  = isset($_POST["terminos"]);

 
    $_SESSION['datos_temporales'] = [
        'nombre' => $nombre,
        'correo' => $correo
    ];


    if (empty($nombre) || empty($correo) || empty($password) || empty($confirmar)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

 
    if (!empty($nombre) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $nombre)) {
        $errores[] = "El usuario debe tener entre 3 y 20 caracteres (letras, números, _).";
    }

  
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido.";
    }

  
    if ($password !== $confirmar) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    
    if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $errores[] = "La contraseña debe tener: 8 caracteres, 1 mayúscula, 1 minúscula y 1 número.";
    }


    if (!$terminos) {
        $errores[] = "Debes aceptar los términos y condiciones.";
    }


    if (empty($errores)) {
        $stmt = $con->prepare("SELECT usuarioID FROM dato WHERE usuario = ? OR email = ?");
        $stmt->bind_param("ss", $nombre, $correo);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errores[] = "El usuario o el correo ya están registrados.";
        }
        $stmt->close();
    }

  
    if (count($errores) > 0) {
      
        $_SESSION['lista_errores'] = $errores;
        header("Location: registro.php");
        exit();
    } else {
       
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO dato (usuario, email, contrasenya) VALUES (?, ?, ?)";
        $insert = $con->prepare($sql);
        $insert->bind_param("sss", $nombre, $correo, $hash);

        if ($insert->execute()) {
          
            unset($_SESSION['datos_temporales']);
            unset($_SESSION['lista_errores']);
            $_SESSION["usuario"] = $nombre;
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['lista_errores'] = ["Error de base de datos: " . $insert->error];
            header("Location: registro.php");
            exit();
        }
        $insert->close();
    }
    $con->close();

} else {
    header("Location: registro.php");
    exit();
}
?>