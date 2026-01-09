<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('conexion.php');

$ruta = $_SERVER['PHP_SELF'];
$fichero = basename($ruta);

//LÓGICA DE COOKIES
if (!isset($_SESSION['usuario']) && isset($_COOKIE['usuario']) && isset($_COOKIE['token'])) {
    $usuarioCookie = $_COOKIE['usuario'];
    $tokenCookie = $_COOKIE['token'];

    $sql = "SELECT token_login FROM dato WHERE usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuarioCookie);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if (hash_equals($fila['token_login'], $tokenCookie)) {
            $_SESSION['usuario'] = $usuarioCookie; 
        }
    }
}

//PREPARAR FOTO DE PERFIL
$fotoParaMostrar = null;
$numNotificaciones = 0;

if (isset($_SESSION['usuario'])) {
    $userSesion = $_SESSION['usuario'];

    $sqlFoto = "SELECT foto_perfil FROM dato WHERE usuario = ?";
    $stmtFoto = $con->prepare($sqlFoto);
    $stmtFoto->bind_param("s", $userSesion);
    $stmtFoto->execute();
    $resFoto = $stmtFoto->get_result();
    
    if ($filaFoto = $resFoto->fetch_assoc()) {
        $nombreFoto = $filaFoto['foto_perfil'];
        if (!empty($nombreFoto) && file_exists(__DIR__ . "/../img/perfiles/" . $nombreFoto)) {
            $fotoParaMostrar = "../img/perfiles/" . htmlspecialchars($nombreFoto);
        } else {
            $fotoParaMostrar = "https://ui-avatars.com/api/?name=".urlencode($userSesion)."&background=random&color=fff&size=64";
        }
    }
    $sqlCount = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_destino = ? AND leido = 0";
    if ($stmtC = $con->prepare($sqlCount)) {
        $stmtC->bind_param("s", $userSesion);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        $rowC = $resC->fetch_assoc();
        $numNotificaciones = $rowC['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro</title>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css"/>
    <style>
        .login { display: flex; align-items: center; gap: 10px; }
        .mini-foto-perfil { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #4CAF50; transition: 0.2s; }
        .mini-foto-perfil:hover { transform: scale(1.1); }
        .pipe-separador { color: #666; margin: 0 5px; }
        
        .notificacion-wrapper { position: relative; cursor: pointer; margin-right: 10px; display: flex; align-items: center; text-decoration: none; }
        .campana-icon { font-size: 24px; color: white; transition: 0.3s; }
        .campana-icon:hover { color: #4CAF50; transform: scale(1.1); }
        .badge { position: absolute; top: -5px; right: -5px; background-color: #f44336; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center; border: 1px solid #222; }
    </style>
</head>
<body>
    <div class="cabecera">
        <div class="foto">
            <a href="index.php"><img class="logo_grande" src="../img/logoFORO.png" alt="Logo Foro"/></a>
        </div>

        <div class="barra_busqueda">
            <form action="index.php" method="get">
                <input type="text" name="buscar" placeholder="Buscar..." 
                       value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                <button type="submit">🔍</button>
            </form>
        </div>

        <div class="cuenta">
            <?php 
            if (isset($_SESSION['usuario'])) {
                $user = $_SESSION['usuario'];
                echo '<div class="login">';


                echo '<a href="notificaciones.php" class="notificacion-wrapper">';
                echo '<span class="campana-icon">🔔</span>';
                if ($numNotificaciones > 0) {
                    echo '<span class="badge">' . ($numNotificaciones > 9 ? '9+' : $numNotificaciones) . '</span>';
                }
                echo '</a>';


                if ($fotoParaMostrar) {
                    echo "<a href='perfil.php?usuario=" . urlencode($user) . "'>";
                    echo "<img src='$fotoParaMostrar' alt='Perfil' class='mini-foto-perfil'>";
                    echo "</a>";
                }

                echo "<a href='perfil.php?usuario=" . urlencode($user) . "'><button class='registros'>$user</button></a>";
                echo "<a href='logout.php' class='registrosout'>Logout</a>";
                echo '</div>';
            } else {
                $claseReg = ($fichero == 'registro.php') ? 'registrosos' : 'registros';
                $claseLog = ($fichero == 'login.php') ? 'registrosos' : 'registros';
                
                echo '<a href="registro.php"><button class="'.$claseReg.'">Regístrate</button></a>';
                echo '<a href="login.php"><button class="'.$claseLog.'">Inicio Sesión</button></a>';
            }
            ?>
        </div>
    </div>

    <?php

    if (isset($_SESSION['usuario'])) {
        if ($_SESSION['usuario'] === 'Admin') {
            $categorias = ["General"=>"index.php", "Deportes"=>"deportes.php", "Tecnología"=>"tecnologia.php", "Videojuegos"=>"videojuegos.php", "Usuarios"=>"usuarios.php"];
        } else {
            $categorias = ["General"=>"index.php", "Deportes"=>"deportes.php", "Tecnología"=>"tecnologia.php", "Videojuegos"=>"videojuegos.php", "Perfil"=>"editarperfil.php"];
        }
    } else {

        $categorias = ["General"=>"index.php", "Deportes"=>"deportes.php", "Tecnología"=>"tecnologia.php", "Videojuegos"=>"videojuegos.php"];
    }
    ?>

    <nav class="barra_categorias">
        <?php
        foreach ($categorias as $nombre => $archivo) {
            $activo = ($fichero === basename($archivo)) ? 'categoria-activa' : '';
            echo "<a href=\"$archivo\" class=\"$activo\">$nombre</a>";
            if ($nombre === 'Videojuegos' && isset($_SESSION['usuario']) && $_SESSION['usuario'] !== 'Admin') {
                echo " <span class='pipe-separador'>|</span> ";
            }
        }
        ?>
    </nav>
</body>
</html>