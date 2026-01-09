const OJO = document.querySelector("#img-contrasenya");
const OJOS = document.querySelector("#img-contrasenya2");

function cambio() {
    let imagen = OJO.getAttribute("src");
    const campoContrasena = document.querySelector('[name="password"]');

    if (imagen.includes("ojocerrado.png")) {
        OJO.src = "../img/ojoabierto.png";
        campoContrasena.setAttribute("type", "text");

    } else {
        OJO.src = "../img/ojocerrado.png";
        campoContrasena.setAttribute("type", "password");
    }
}

function cambios() {
    let imagen = OJOS.getAttribute("src");
    const campoContrasena = document.querySelector('[name="confirmar"]');

    if (imagen.includes("ojocerrado.png")) {
        OJOS.src = "../img/ojoabierto.png";
        campoContrasena.setAttribute("type", "text");

    } else {
        OJOS.src = "../img/ojocerrado.png";
        campoContrasena.setAttribute("type", "password");
    }
}
