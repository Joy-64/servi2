/* CARRUSEL: cambia solo cada 4 segundos, o con las flechas / puntos / teclado / deslizando */

const TIEMPO = 4000; // 4 segundos

const carrusel = document.querySelector("#carrusel");
const slides = document.querySelectorAll(".carrusel-slide");
const anterior = document.querySelector("#slideAnterior");
const siguiente = document.querySelector("#slideSiguiente");
const contPuntos = document.querySelector("#carruselPuntos");
const barra = document.querySelector("#carruselBarra");

let slideActual = 0;
let temporizador = null;

// Un punto por cada foto
const puntos = [...slides].map(function (slide, i) {
    const punto = document.createElement("button");
    punto.type = "button";
    punto.className = "carrusel-punto" + (i === 0 ? " activo" : "");
    punto.setAttribute("aria-label", "Ir a la imagen " + (i + 1));
    punto.addEventListener("click", function () {
        mostrarSlide(i);
        reiniciar();
    });
    contPuntos.appendChild(punto);
    return punto;
});

function mostrarSlide(indice) {
    slides[slideActual].classList.remove("activo");
    puntos[slideActual].classList.remove("activo");

    slideActual = (indice + slides.length) % slides.length;

    slides[slideActual].classList.add("activo");
    puntos[slideActual].classList.add("activo");
}

// Barra de progreso: se reinicia la animación CSS en cada vuelta
function animarBarra() {
    barra.style.animation = "none";
    void barra.offsetWidth;
    barra.style.animation = "progreso " + TIEMPO + "ms linear forwards";
}

function iniciar() {
    detener();
    animarBarra();
    temporizador = setInterval(function () {
        mostrarSlide(slideActual + 1);
        animarBarra();
    }, TIEMPO);
}

function detener() {
    clearInterval(temporizador);
    temporizador = null;
}

function reiniciar() {
    iniciar();
}

anterior.addEventListener("click", function () {
    mostrarSlide(slideActual - 1);
    reiniciar();
});

siguiente.addEventListener("click", function () {
    mostrarSlide(slideActual + 1);
    reiniciar();
});

// Pausa mientras el mouse está encima
carrusel.addEventListener("mouseenter", function () {
    detener();
    barra.style.animationPlayState = "paused";
});
carrusel.addEventListener("mouseleave", iniciar);

// Flechas del teclado
document.addEventListener("keydown", function (e) {
    if (e.target.closest("input, textarea")) return;
    if (e.key === "ArrowLeft") { mostrarSlide(slideActual - 1); reiniciar(); }
    if (e.key === "ArrowRight") { mostrarSlide(slideActual + 1); reiniciar(); }
});

// Deslizar con el dedo en el celular
let inicioX = null;
carrusel.addEventListener("touchstart", function (e) {
    inicioX = e.touches[0].clientX;
}, { passive: true });
carrusel.addEventListener("touchend", function (e) {
    if (inicioX === null) return;
    const dx = e.changedTouches[0].clientX - inicioX;
    if (Math.abs(dx) > 40) {
        mostrarSlide(slideActual + (dx < 0 ? 1 : -1));
        reiniciar();
    }
    inicioX = null;
});

// Si la pestaña no está visible, no avanza
document.addEventListener("visibilitychange", function () {
    if (document.hidden) detener(); else iniciar();
});

iniciar();
