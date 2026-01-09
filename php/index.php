<?php
require_once('cabecera.php');
require_once('conexion.php');

// Definir categorías
if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'Admin') {
    $categorias = ["General" => "general.php", "Deportes" => "deportes.php", "Tecnología" => "tecnologia.php", "Videojuegos" => "videojuegos.php", "Usuarios" => "usuarios.php"];
} else {
    $categorias = ["General" => "general.php", "Deportes" => "deportes.php", "Tecnología" => "tecnologia.php", "Videojuegos" => "videojuegos.php", "Perfil" => "editarperfil.php"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<div class="contenido_publicaciones">
    <div class="foronoticias">
        <h2 style="color:white;">Tendencias</h2>
        <?php if (isset($_SESSION['usuario'])): ?>
            <button id="btnPublicar" class="boton_publicar" type="button">Publicar</button>
        <?php else: ?>
            <p style="color:white;">Debes iniciar sesión para publicar.</p>
        <?php endif; ?>
    </div>

    <form id="formPublicacion" class="form_publicacion" action="publicar.php" method="post" enctype="multipart/form-data" style="display:none;">
        <label for="categoria" style="color:white;">Categoría:</label>
        <select name="categoria" id="categoria" required style="margin-bottom: 15px; display:block; padding: 5px; border-radius:4px;">
            <?php foreach ($categorias as $nombre => $archivo): ?>
                <option value="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="layout-vertical">
            <textarea name="contenido" rows="4" placeholder="Escribe tu publicación aquí..." required></textarea>

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
        $busqueda = (isset($_GET['buscar'])) ? $con->real_escape_string(trim($_GET['buscar'])) : "";
        
        $sql = "SELECT p.*, d.foto_perfil, COALESCE(SUM(CASE WHEN v.tipo = 'up' THEN 1 WHEN v.tipo = 'down' THEN -1 ELSE 0 END), 0) AS puntuacion 
                FROM publicaciones p 
                LEFT JOIN votos v ON p.id = v.publicacion_id 
                LEFT JOIN dato d ON p.usuario = d.usuario";
        
        if (!empty($busqueda)) {
            $sql .= " WHERE p.usuario LIKE '%$busqueda%' OR p.contenido LIKE '%$busqueda%' OR p.categoria LIKE '%$busqueda%'";
        }
        
        $sql .= " GROUP BY p.id ORDER BY puntuacion DESC, p.fecha DESC";
        $resultado = $con->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $idPub = (int)$fila['id'];
                $usuarioEsc = htmlspecialchars($fila['usuario']);
                $categoriaEsc = htmlspecialchars($fila['categoria'] ?? '');
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
                if ($categoriaEsc) echo "<p class='categoria'>( $categoriaEsc )</p>";
                echo "</div><p class='fecha'>$fechaEsc</p></div>";
                echo "<p class='contenido'>$contenidoEsc</p>";
                if ($imagenPubPath) echo "<img src='$imagenPubPath' class='imagen_publicacion ampliable-publicacion'>";
                if ($videoPubPath) echo "<video controls class='video_publicacion'><source src='$videoPubPath' type='video/mp4'></video>";
                
                $btnLikeClass = isset($_SESSION['usuario']) ? 'btn-like' : 'btn-like bloqueado';
                $btnDisClass = isset($_SESSION['usuario']) ? 'btn-dislike' : 'btn-dislike bloqueado';

                echo "<div class='votos'>";
                echo "<img src='../img/upvote.png' class='$btnLikeClass'>";
                echo "<img src='../img/downvote.png' class='$btnDisClass'>";
                echo "</div>";

                echo "<div class='contenedor-boton-responder'><a href='comentarios.php?id=$idPub' class='btn-responder'>Responder</a></div>";
                echo "</div><hr>";
            }
        }
        ?>
    </div>
</div>

<div id="modalFoto" class="modal-foto"><span class="cerrar-modal">&times;</span><img id="fotoAmpliada" class="foto-ampliada"></div>

<script src="../js/likes.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // HE ELIMINADO EL CÓDIGO DE 'btnPublicar' AQUÍ PORQUE YA ESTÁ EN likes.js
    // Si lo dejábamos, se ejecutaba dos veces y cerraba el formulario al instante.

    // 2. MODAL DE FOTOS
    const modal = document.getElementById('modalFoto');
    const imgAmpliada = document.getElementById('fotoAmpliada');
    const cerrar = document.querySelector('.cerrar-modal');

    document.querySelectorAll('.foto-perfil.ampliable, .imagen_publicacion.ampliable-publicacion').forEach(img => {
        img.onclick = function() {
            imgAmpliada.src = this.src;
            modal.style.display = 'flex';
        };
    });

    if(cerrar) cerrar.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
});

// 3. FUNCIONES DE PREVISUALIZACIÓN (Globales)
function previsualizarMedia(input, previewId, btnId) {
    const preview = document.getElementById(previewId);
    const btnQuitar = document.getElementById(btnId);
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) { 
            preview.src = e.target.result; 
            preview.style.display = 'block'; 
            btnQuitar.style.display = 'block'; 
        }
        reader.readAsDataURL(file);
    }
}

function quitarMedia(inputId, previewId, btnId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const btnQuitar = document.getElementById(btnId);

    input.value = ""; 
    preview.src = "";
    preview.style.display = 'none';
    btnQuitar.style.display = 'none';
}
</script>

</body>
</html>