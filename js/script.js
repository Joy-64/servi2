/*BOTON MENU HAMBURGUESA*/

const boton = document.getElementById("menu-btn");
const menu = document.getElementById("menu");

boton.addEventListener("click", () => {
    const abierto = menu.classList.toggle("active");
    boton.classList.toggle("abierto", abierto);
    boton.setAttribute("aria-expanded", abierto);
});

// Al elegir una opción, se cierra el menú (celular)
menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
        menu.classList.remove("active");
        boton.classList.remove("abierto");
        boton.setAttribute("aria-expanded", false);
    });
});

/*SOMBRA EN LA CABECERA AL HACER SCROLL*/
const cabecera = document.querySelector(".cabecera");
window.addEventListener("scroll", () => {
    cabecera.classList.toggle("con-sombra", window.scrollY > 10);
});
