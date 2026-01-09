<?php
require_once('cabecera.php');
require_once('conexion.php');
$categoriaActual = 'Videojuegos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videojuegos</title>
    <link rel="stylesheet" href="../css/estilos.css">
    
    
    <script>
    function toggleFormVid() {
        var form = document.getElementById('formPublicacion');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
    </script>
</head>
<body>

<div class="contenido_publicaciones">
    <div class="foronoticias">
        <h2 style="color:white;">Videojuegos</h2>
        <?php if (isset($_SESSION['usuario'])): ?>
            <button class="boton_publicar" type="button" onclick="toggleFormVid()">Publicar</button>
        <?php else: ?>
            <p style="color:white;">Debes iniciar sesión para publicar.</p>
        <?php endif; ?>
    </div>

    <form id="formPublicacion" class="form_publicacion" action="publicar.php" method="post" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="categoria" value="Videojuegos">
        <div class="layout-vertical">
            <textarea name="contenido" rows="4" placeholder="Escribe tu publicación sobre videojuegos..." required></textarea>
            
            <div class="barra-acciones">
                <div class="preview-container">
                    <button type="button" class="btn-quitar-media" id="btnRemoveImg" onclick="quitarMedia('inputImagen', 'previewImg', 'btnRemoveImg')">&times;</button>
                    <img id="previewImg" class="mini-preview" alt="Vista previa">
                </div>
                <input type="file" name="imagen" id="inputImagen" accept="image/*" class="input-oculto" onchange="previsualizarMedia(this, 'previewImg', 'btnRemoveImg')">
                <label for="inputImagen" class="btn-icono" title="Subir Imagen">
                    <img src="../img/icono_imagen.png" alt="IMG">
                </label>

                <div class="preview-container">
                    <button type="button" class="btn-quitar-media" id="btnRemoveVid" onclick="quitarMedia('inputVideo', 'previewVid', 'btnRemoveVid')">&times;</button>
                    <video id="previewVid" class="mini-preview" autoplay muted loop playsinline></video>
                </div>
                <input type="file" name="video" id="inputVideo" accept="video/*" class="input-oculto" onchange="previsualizarMedia(this, 'previewVid', 'btnRemoveVid')">
                <label for="inputVideo" class="btn-icono" title="Subir Video">
                    <img src="../img/icono_video.png" alt="VID">
                </label>
                
                <button type="submit" class="btn-enviar-final">Enviar</button>
            </div>
        </div>
    </form>
    <hr>
    
    <div class="lista_publicaciones">
        <?php
        $sql = "SELECT p.*, u.foto_perfil, COALESCE(SUM(CASE WHEN v.tipo = 'up' THEN 1 WHEN v.tipo = 'down' THEN -1 ELSE 0 END), 0) AS puntuacion FROM publicaciones p LEFT JOIN votos v ON p.id = v.publicacion_id LEFT JOIN dato u ON p.usuario = u.usuario WHERE p.categoria = ? GROUP BY p.id ORDER BY puntuacion DESC, p.fecha DESC";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $categoriaActual);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $idPub = (int)$fila['id'];
                $usuarioEsc = htmlspecialchars($fila['usuario']);
                $fechaEsc = date("Y-m-d H:i", strtotime($fila['fecha']));
                $contenidoEsc = nl2br(htmlspecialchars($fila['contenido']));
                
                $fotoPerfilFinal = "https://ui-avatars.com/api/?name=".urlencode($fila['usuario'])."&background=random&color=fff&size=128";
                if (!empty($fila['foto_perfil']) && file_exists(__DIR__ . "/../img/perfiles/" . $fila['foto_perfil'])) {
                    $fotoPerfilFinal = "../img/perfiles/" . htmlspecialchars($fila['foto_perfil']);
                }

                $imagenPubPath = (!empty($fila['imagen']) && file_exists(__DIR__ . "/../img/" . $fila['imagen'])) ? "../img/" . htmlspecialchars($fila['imagen']) : "";
                $videoPubPath = (!empty($fila['video']) && file_exists(__DIR__ . "/../videos/" . $fila['video'])) ? "../videos/" . htmlspecialchars($fila['video']) : "";

                echo "<div class='publicacion' data-id='$idPub'>";
                echo "<div class='usuario-fecha'><div class='usuario-categoria'>";
                echo "<img src='$fotoPerfilFinal' alt='Perfil' class='foto-perfil ampliable'>";
                echo "<p class='usuario'><strong><a href='perfil.php?usuario=".urlencode($fila['usuario'])."' style='color:#4CAF50; text-decoration:none;'>$usuarioEsc</a></strong></p>";
                echo "</div><p class='fecha'>$fechaEsc</p></div>";
                echo "<p class='contenido'>$contenidoEsc</p>";
                if ($imagenPubPath) echo "<img src='$imagenPubPath' class='imagen_publicacion ampliable-publicacion'>";
                if ($videoPubPath) echo "<video controls class='video_publicacion'><source src='$videoPubPath' type='video/mp4'></video>";

                $btnLikeClass = isset($_SESSION['usuario']) ? 'btn-like' : 'btn-like bloqueado';
                $btnDisClass = isset($_SESSION['usuario']) ? 'btn-dislike' : 'btn-dislike bloqueado';

                echo "<div class='votos'><img src='../img/upvote.png' class='$btnLikeClass'><img src='../img/downvote.png' class='$btnDisClass'></div>";
                if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'Admin') {
                    echo "<form action='eliminar_publicacion_admin.php' method='post' class='form-eliminar'><input type='hidden' name='id' value='$idPub'><input type='hidden' name='pagina' value='videojuegos.php'><button type='submit' class='btn-eliminar'>Eliminar</button></form>";
                }
                echo "<div class='contenedor-boton-responder'><a href='comentarios.php?id=$idPub' class='btn-responder'>Responder</a></div>";
                echo "</div><hr>";
            }
        } else {
            echo "<p style='color:white;'>No hay publicaciones en Videojuegos.</p>";
        }
        ?>
    </div>
</div>
<div id="modalFoto" class="modal-foto" aria-hidden="true"><span class="cerrar-modal">&times;</span><img id="fotoAmpliada" class="foto-ampliada" src=""></div>
<script src="../js/likes.js"></script>
<script>
function previsualizarMedia(input, previewId, btnId) {
    var preview = document.getElementById(previewId);
    var btnQuitar = document.getElementById(btnId);
    var file = input.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) { 
            preview.src = e.target.result; 
            preview.style.display = 'block'; 
            btnQuitar.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

function quitarMedia(inputId, previewId, btnId) {
    document.getElementById(inputId).value = "";
    document.getElementById(previewId).src = "";
    document.getElementById(previewId).style.display = 'none';
    document.getElementById(btnId).style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalFoto');
    const imgAmpliada = document.getElementById('fotoAmpliada');
    const cerrar = document.querySelector('.cerrar-modal');
    
    document.querySelectorAll('.foto-perfil.ampliable, .imagen_publicacion.ampliable-publicacion').forEach(img => {
        img.addEventListener('click', () => { imgAmpliada.src = img.src; modal.style.display = 'flex'; });
    });
    if(cerrar) cerrar.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
});
</script>
</body>
</html>