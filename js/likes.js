// likes.js - Delegación y votaciones
document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById('formPublicacion');

    // Delegación de eventos para todo el body
    document.body.addEventListener('click', (e) => {
        const t = e.target;

        // 🔹 Toggle formulario publicar
        if (t && t.id === 'btnPublicar' && form) {
            const cs = window.getComputedStyle(form);
            form.style.display = cs.display === 'none' ? 'block' : 'none';
        }

        // 🔹 Likes
        if (t && t.classList.contains('btn-like')) {
            const pub = t.closest('.publicacion');
            if (!pub) return;
            const id = pub.dataset.id;
            const dislikeBtn = pub.querySelector('.btn-dislike');

            // Animación local
            if (!t.classList.contains('activo')) {
                t.classList.add('animacion','activo');
                dislikeBtn?.classList.remove('activo');
            } else {
                t.classList.remove('activo');
            }

            // Llamada al servidor
            fetch('votar.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `publicacion_id=${id}&tipo=up`
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById(`puntuacion_${id}`).innerText = data;
            })
            .catch(err => console.error(err));
        }

        // 🔹 Dislikes
        if (t && t.classList.contains('btn-dislike')) {
            const pub = t.closest('.publicacion');
            if (!pub) return;
            const id = pub.dataset.id;
            const likeBtn = pub.querySelector('.btn-like');

            if (!t.classList.contains('activo')) {
                t.classList.add('animacion','activo');
                likeBtn?.classList.remove('activo');
            } else {
                t.classList.remove('activo');
            }

            fetch('votar.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `publicacion_id=${id}&tipo=down`
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById(`puntuacion_${id}`).innerText = data;
            })
            .catch(err => console.error(err));
        }

    });

});
