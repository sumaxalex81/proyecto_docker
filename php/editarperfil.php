<?php
require_once('cabecera.php');
require_once('conexion.php');


if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit(); 
}

$usuario = $_SESSION['usuario'];


$sql = "SELECT * FROM dato WHERE usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();

$mensaje = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoCorreo = trim($_POST['correo']);
    $nuevoNombre = trim($_POST['nombre']);
    $accion = $_POST['accion'] ?? 'guardar';
    
    
    $fotoPerfil = $datos['foto_perfil']; 
    $rutaCarpeta = "../img/perfiles/";

    if ($accion === 'eliminar_foto') {
        
        if (!empty($fotoPerfil) && file_exists($rutaCarpeta . $fotoPerfil)) {
            unlink($rutaCarpeta . $fotoPerfil); 
        }
        $fotoPerfil = ""; 
        $mensaje = "🗑️ Foto de perfil eliminada.";
        
    } else {
      
        if (!empty($_FILES['foto']['name'])) {
            $nombreArchivo = basename($_FILES['foto']['name']);
            $rutaDestino = $rutaCarpeta . $nombreArchivo;
            $extensionesValidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (in_array($extension, $extensionesValidas)) {
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    $fotoPerfil = $nombreArchivo;
                } else {
                    $mensaje = "❌ Error al subir la imagen.";
                }
            } else {
                $mensaje = "⚠️ Formato de imagen no válido.";
            }
        }
    }

    $sqlUpdate = "UPDATE dato SET email = ?, usuario = ?, foto_perfil = ? WHERE usuario = ?";
    $stmtUpdate = $con->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssss", $nuevoCorreo, $nuevoNombre, $fotoPerfil, $usuario);

    if ($stmtUpdate->execute()) {
        if ($accion === 'guardar') $mensaje = "✅ Perfil actualizado correctamente.";
        $_SESSION['usuario'] = $nuevoNombre;
        $_SESSION['foto_perfil'] = $fotoPerfil;
    } else {
        $mensaje = "❌ Error al actualizar el perfil.";
    }


    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = $resultado->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .perfil-container {
            width: 400px;
            margin: 50px auto;
            background-color: #333;
            color: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .perfil-container h2 { margin-bottom: 20px; }
        
       
        .foto-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

  
        .perfil-img-grande {
            width: 130px; 
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4CAF50;
            background-color: white;
        }

       
        .btn-eliminar-foto {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #f44336;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #333;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            padding: 0;
        }
        .btn-eliminar-foto:hover {
            background-color: #d32f2f;
            transform: scale(1.1);
        }

      
        .btn-cambiar-foto {
            display: block;
            margin-top: 10px;
            background-color: #555;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            border: 1px solid #777;
        }
        .btn-cambiar-foto:hover { background-color: #777; }

        .perfil-container input[type="text"],
        .perfil-container input[type="email"] {
            width: 90%; padding: 12px; border-radius: 5px; border: none;
            margin-bottom: 15px; background-color: #444; color: white; border: 1px solid #555;
        }

        .btn-guardar {
            background-color: #4CAF50; color: white; border: none;
            padding: 12px 30px; border-radius: 25px; cursor: pointer;
            font-size: 16px; font-weight: bold; margin-top: 10px; width: 100%;
        }
        .btn-guardar:hover { background-color: #45a049; }
        .mensaje { margin-top: 15px; color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>

<div class="perfil-container">
    <h2>Editar Perfil</h2>

    <?php

    $nombreFotoBD = $datos['foto_perfil'] ?? '';
    $rutaArchivoLocal = "../img/perfiles/" . $nombreFotoBD;
    $tieneFotoSubida = !empty($nombreFotoBD) && file_exists($rutaArchivoLocal);

    if ($tieneFotoSubida) {
        $fotoMostrar = $rutaArchivoLocal;
    } else {
        $nombreUsuarioEnc = urlencode($datos['usuario'] ?? 'U');
        $fotoMostrar = "https://ui-avatars.com/api/?name=$nombreUsuarioEnc&background=random&color=fff&size=128";
    }
    ?>

    <form action="editarperfil.php" method="post" enctype="multipart/form-data">
        
        <div class="foto-wrapper">
            <img id="imgVisualizacion" src="<?= $fotoMostrar ?>" alt="Foto de perfil" class="perfil-img-grande">
            
            <?php if ($tieneFotoSubida): ?>
                <button type="submit" name="accion" value="eliminar_foto" class="btn-eliminar-foto" title="Eliminar foto actual">✕</button>
            <?php endif; ?>
            
            <input type="file" name="foto" id="inputFoto" accept="image/*" style="display: none;" onchange="previsualizarPerfil(this)">
            <label for="inputFoto" class="btn-cambiar-foto">📷 Cambiar foto</label>
        </div>

        <input type="text" name="nombre" value="<?= htmlspecialchars($datos['usuario'] ?? '') ?>" placeholder="Nombre de usuario" required>
        <input type="email" name="correo" value="<?= htmlspecialchars($datos['email'] ?? '') ?>" placeholder="Correo electrónico" required>
        
        <button type="submit" name="accion" value="guardar" class="btn-guardar">Guardar cambios</button>
    </form>

    <?php if (!empty($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
</div>

<script>
function previsualizarPerfil(input) {
    const img = document.getElementById('imgVisualizacion');
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>

</body>
</html>