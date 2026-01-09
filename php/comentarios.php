<?php
require_once('cabecera.php');
require_once('conexion.php');

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$idPublicacion = intval($_GET['id']);

// Obtener publicación
$sqlPub = "
    SELECT p.*, u.foto_perfil 
    FROM publicaciones p
    LEFT JOIN dato u ON p.usuario = u.usuario
    WHERE p.id = ?
";
$stmtPub = $con->prepare($sqlPub);
$stmtPub->bind_param("i", $idPublicacion);
$stmtPub->execute();
$resPub = $stmtPub->get_result();
$publicacion = $resPub->fetch_assoc();

if (!$publicacion) {
    echo "<p style='color:white;'>No existe la publicación.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comentarios</title>
<link rel="stylesheet" href="../css/estilos.css">

<style>
    

    .contenedor-form-vertical {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }


    .textarea-custom {
        width: 100% !important;
        display: block !important;
        box-sizing: border-box !important;
        min-height: 80px;
        margin-bottom: 10px !important;
        padding: 10px !important;
        border-radius: 5px !important;
        border: 1px solid #ccc !important;
        background-color: #f9f9f9 !important;
        color: #000 !important;
        font-family: sans-serif !important;
        resize: vertical !important;
        float: none !important; 
    }


    .barra-botones-derecha {
        display: flex !important;
        flex-direction: row !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 15px !important;
        width: 100% !important;
    }


    .input-invisible {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        opacity: 0 !important;
        position: absolute !important;
    }


    .icono-upload {
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 30px !important;
        height: 30px !important;
        margin: 0 !important;
        padding: 0 !important;
        opacity: 0.6;
        transition: 0.2s;
        border: none !important;
        background: none !important;
    }
    .icono-upload:hover { opacity: 1; transform: scale(1.1); }
    .icono-upload img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }


    .btn-enviar-custom {
        background-color: #4CAF50 !important;
        color: white !important;
        border: none !important;
        padding: 8px 25px !important;
        border-radius: 5px !important;
        cursor: pointer !important;
        font-weight: bold !important;
        font-size: 14px !important;
        height: auto !important;
        line-height: normal !important;
    }
    .btn-enviar-custom:hover { background-color: #45a049 !important; }


    .mini-thumb {
        width: 45px !important;
        height: 45px !important;
        object-fit: cover !important;
        border-radius: 5px !important;
        border: 2px solid #4CAF50 !important;
        display: none; 
        margin-right: 10px !important;
        background-color: #000;
    }


    .comentarios-lista { margin-top: 30px; }
    .item-comentario { 
        margin-bottom: 20px; 
        padding-left: 15px; 
        border-left: 3px solid #444; 
    }
    

    .form-respuesta-wrapper {
        display: none; 
        margin-top: 15px;
        padding: 15px;
        background-color: #2b2b2b; 
        border-radius: 8px;
        border: 1px solid #444;
    }
</style>
</head>
<body>

<div class="contenido_publicaciones">
    <div class="publicacion">
        <div class="usuario-info">
            <?php 
            $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($publicacion['usuario']) . "&background=random&color=fff";
            if (!empty($publicacion['foto_perfil']) && file_exists("../img/perfiles/" . $publicacion['foto_perfil'])) {
                $avatarUrl = "../img/perfiles/" . htmlspecialchars($publicacion['foto_perfil']);
            }
            ?>
            <img src="<?= $avatarUrl ?>" class="foto-perfil" alt="Foto de perfil">
            
            <div>
                <strong style="color:#4CAF50;"><?= htmlspecialchars($publicacion['usuario']) ?></strong><br>
                <span class="fecha"><?= htmlspecialchars($publicacion['fecha']) ?></span>
            </div>
        </div>

        <p><?= nl2br(htmlspecialchars($publicacion['contenido'])) ?></p>

        <?php if (!empty($publicacion['imagen'])): ?>
            <img src="../img/<?= htmlspecialchars($publicacion['imagen']) ?>" class="imagen_publicacion">
        <?php endif; ?>
        
        <?php if (!empty($publicacion['video']) && file_exists("../videos/" . $publicacion['video'])): ?>
            <video controls src="../videos/<?= htmlspecialchars($publicacion['video']) ?>" class="video_publicacion"></video>
        <?php endif; ?>
    </div>

    <hr>

    <h3 style="color:white;">Comentarios</h3>

    <?php if (isset($_SESSION['usuario'])): ?>
        <form action="responder.php" method="post" enctype="multipart/form-data" style="margin-bottom: 40px;">
            <input type="hidden" name="publicacion_id" value="<?= $idPublicacion ?>">
            
            <div class="contenedor-form-vertical">
                <textarea name="contenido" class="textarea-custom" rows="3" placeholder="Escribe un comentario..." required></textarea>
                
                <div class="barra-botones-derecha">
                    <img id="prevImgMain" class="mini-thumb">
                    
                    <input type="file" name="imagen" id="inpImgMain" accept="image/*" class="input-invisible" onchange="previsualizarMedia(this, 'prevImgMain')">
                    <label for="inpImgMain" class="icono-upload" title="Imagen">
                        <img src="../img/icono_imagen.png" alt="IMG">
                    </label>

                    <video id="prevVidMain" class="mini-thumb" autoplay muted loop></video>
                    
                    <input type="file" name="video" id="inpVidMain" accept="video/*" class="input-invisible" onchange="previsualizarMedia(this, 'prevVidMain')">
                    <label for="inpVidMain" class="icono-upload" title="Vídeo">
                        <img src="../img/icono_video.png" alt="VID">
                    </label>

                    <button type="submit" class="btn-enviar-custom">Comentar</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <p style="color:white;">Debes iniciar sesión para comentar.</p>
    <?php endif; ?>

    <div class="comentarios-lista">
        <?php
        function mostrarComentarios($con, $publicacion_id, $padre_id = NULL, $nivel = 0) {
            $sql = "
                SELECT c.*, u.foto_perfil 
                FROM comentarios c
                LEFT JOIN dato u ON c.usuario = u.usuario
                WHERE c.publicacion_id = ? AND " . ($padre_id ? "c.comentario_padre = ?" : "c.comentario_padre IS NULL") . "
                ORDER BY c.fecha ASC
            ";

            $stmt = $con->prepare($sql);
            if ($padre_id) {
                $stmt->bind_param("ii", $publicacion_id, $padre_id);
            } else {
                $stmt->bind_param("i", $publicacion_id);
            }
            $stmt->execute();
            $res = $stmt->get_result();

            while ($comentario = $res->fetch_assoc()) {
                $margen = 20 * $nivel;

                
                $avatarCom = "https://ui-avatars.com/api/?name=" . urlencode($comentario['usuario']) . "&background=random&color=fff";
                if (!empty($comentario['foto_perfil']) && file_exists("../img/perfiles/" . $comentario['foto_perfil'])) {
                    $avatarCom = "../img/perfiles/" . htmlspecialchars($comentario['foto_perfil']);
                }

                echo "<div class='item-comentario' style='margin-left: {$margen}px'>";
                
                
                echo "<div class='usuario-info'>";
                echo "<img src='$avatarCom' class='foto-perfil' alt='Foto'>";
                echo "<div>
                        <strong style='color:#4CAF50;'>" . htmlspecialchars($comentario['usuario']) . "</strong><br>
                        <span class='fecha'>" . htmlspecialchars($comentario['fecha']) . "</span>
                      </div>";
                echo "</div>";

                echo "<p style='color: #ddd; margin-top: 5px;'>" . nl2br(htmlspecialchars($comentario['contenido'])) . "</p>";

                
                if (!empty($comentario['imagen'])) {
                    echo "<img src='../img/" . htmlspecialchars($comentario['imagen']) . "' class='imagen_publicacion' style='max-width:200px; margin-top:5px; border-radius:5px;'>";
                }
                if (!empty($comentario['video']) && file_exists("../videos/".$comentario['video'])) {
                    echo "<video controls src='../videos/" . htmlspecialchars($comentario['video']) . "' class='video_publicacion' style='max-width:200px; margin-top:5px; border-radius:5px;'></video>";
                }

               
                if (isset($_SESSION['usuario'])) {
                    echo "<p><span class='responder' style='color:#4CAF50; cursor:pointer; font-size:0.9em; text-decoration:underline;' onclick=\"toggleForm('form_{$comentario['id']}')\">Responder</span></p>";

                    $cid = $comentario['id'];
                    
                    
                    echo "
                    <div id='form_{$cid}' class='form-respuesta-wrapper'>
                        <form action='responder.php' method='post' enctype='multipart/form-data'>
                            <input type='hidden' name='publicacion_id' value='{$publicacion_id}'>
                            <input type='hidden' name='comentario_padre' value='{$cid}'>
                            
                            <div class='contenedor-form-vertical'>
                                <textarea name='contenido' class='textarea-custom' rows='2' placeholder='Responde a {$comentario['usuario']}...' required></textarea>
                                
                                <div class='barra-botones-derecha'>
                                    <img id='preImg_{$cid}' class='mini-thumb'>
                                    
                                    <input type='file' name='imagen' id='inpImg_{$cid}' accept='image/*' class='input-invisible' onchange=\"previsualizarMedia(this, 'preImg_{$cid}')\">
                                    <label for='inpImg_{$cid}' class='icono-upload' title='Imagen'>
                                        <img src='../img/icono_imagen.png'>
                                    </label>

                                    <video id='preVid_{$cid}' class='mini-thumb' autoplay muted loop></video>
                                    
                                    <input type='file' name='video' id='inpVid_{$cid}' accept='video/*' class='input-invisible' onchange=\"previsualizarMedia(this, 'preVid_{$cid}')\">
                                    <label for='inpVid_{$cid}' class='icono-upload' title='Vídeo'>
                                        <img src='../img/icono_video.png'>
                                    </label>

                                    <button type='submit' class='btn-enviar-custom'>Responder</button>
                                </div>
                            </div>
                        </form>
                    </div>";
                }

                mostrarComentarios($con, $publicacion_id, $comentario['id'], $nivel + 1);
                echo "</div>";
            }
        }

        mostrarComentarios($con, $idPublicacion);
        ?>
    </div>
</div>

<script>

function toggleForm(id) {
    const el = document.getElementById(id);
    if (el.style.display === "block") {
        el.style.display = "none";
    } else {
        el.style.display = "block";
    }
}


function previsualizarMedia(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
        preview.style.display = 'none';
    }
}
</script>

</body>
</html>