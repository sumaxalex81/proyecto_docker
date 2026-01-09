const botonCategorias = document.querySelector('.boton_categorias');
const menuDesplegable = document.querySelector('.menu_desplegable');

if (botonCategorias && menuDesplegable) {
    botonCategorias.addEventListener('click', () => {
        menuDesplegable.style.display = menuDesplegable.style.display === 'flex' ? 'none' : 'flex';
    });
}
