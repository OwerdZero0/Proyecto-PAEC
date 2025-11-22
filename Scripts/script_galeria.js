// Selecciona el contenedor que mueve todas las diapositivas (imágenes y video)
const pistaImagenes = document.querySelector(".contenido_imgenes");

// Convierte cada slide en un arreglo para manipularlos fácilmente
const diapositivas = Array.from(pistaImagenes.children);

// Selecciona los botones de siguiente y anterior
const botonAnterior = document.querySelector(".boton_imagen_anterior");
const botonSiguiente = document.querySelector(".boton_imagen_siguiente");

// Contenedor donde se generarán los puntos de navegación
const contenedorPuntos = document.querySelector(".puntos_desplazables");

// Obtiene el índice de la imagen que tiene la clase imagen_activa
let indiceActual = diapositivas.findIndex((slide) =>
    slide.classList.contains("imagen_activa")
);

// Si ninguna imagen tiene imagen_activa, se asigna por defecto la primera
if (indiceActual === -1) {
    indiceActual = 0;
    diapositivas[0].classList.add("imagen_activa");
}

// CREA LOS PUNTOS DE NAVEGACIÓN
function crearPuntos() {
    diapositivas.forEach((_, index) => {
        const punto = document.createElement("button");
        punto.classList.add("punto_desplazable");

        // Marca el punto correspondiente a la diapositiva activa
        if (index === indiceActual) punto.classList.add("punto_desplazable_activo");

        // Al hacer clic en un punto, se mueve a esa diapositiva
        punto.addEventListener("click", () => irADiapositiva(index));

        contenedorPuntos.appendChild(punto);
    });
}

// ACTUALIZA LA DIAPOSITIVA Y EL PUNTO ACTIVOS
function actualizarClasesActivas() {
    // Cambia las diapositivas activas
    diapositivas.forEach((slide, index) => {
        if (index === indiceActual) {
            slide.classList.add("imagen_activa");
        } else {
            slide.classList.remove("imagen_activa");
        }
    });

    // Cambia los puntos activos
    const puntos = Array.from(
        document.querySelectorAll(".punto_desplazable")
    );
    puntos.forEach((punto, index) => {
        punto.classList.toggle("punto_desplazable_activo", index === indiceActual);
    });
}

// FUNCIÓN PARA IR A UNA DIAPOSITIVA
function irADiapositiva(indice) {
    // Si el índice es menor que 0, va a la última imagen
    if (indice < 0) indice = diapositivas.length - 1;

    // Si el índice es mayor al último elemento, vuelve a la primera
    if (indice >= diapositivas.length) indice = 0;

    // Actualiza el índice actual
    indiceActual = indice;

    // Calcula cuánto debe moverse el track (-100%, -200%, etc.)
    const desplazamiento = -indiceActual * 100;
    pistaImagenes.style.transform = `translateX(${desplazamiento}%)`;

    // Actualiza clases de diapositivas y puntos
    actualizarClasesActivas();
}

// BOTONES DE SIGUIENTE Y ANTERIOR
function siguienteDiapositiva() {
    irADiapositiva(indiceActual + 1);
}

function anteriorDiapositiva() {
    irADiapositiva(indiceActual - 1);
}

// Eventos al presionar los botones
botonAnterior.addEventListener("click", anteriorDiapositiva);
botonSiguiente.addEventListener("click", siguienteDiapositiva);

// CONTROLES CON EL TECLADO (← y →)
document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") siguienteDiapositiva();
    if (e.key === "ArrowLeft") anteriorDiapositiva();
});

// AUTO-DESLIZAMIENTO CADA 5 SEGUNDOS
let intervaloAutoDeslizamiento = setInterval(siguienteDiapositiva, 5000);

// Si el usuario hace clic, se reinicia el auto-slide
function reiniciarAutoDeslizamiento() {
    clearInterval(intervaloAutoDeslizamiento);
    intervaloAutoDeslizamiento = setInterval(siguienteDiapositiva, 5000);
}

botonAnterior.addEventListener("click", reiniciarAutoDeslizamiento);
botonSiguiente.addEventListener("click", reiniciarAutoDeslizamiento);

// INICIALIZACIÓN DEL SLIDER
crearPuntos();           // Crea los botoncitos inferiores
actualizarClasesActivas(); // Marca la diapositiva inicial