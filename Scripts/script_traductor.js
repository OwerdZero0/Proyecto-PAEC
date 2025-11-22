//Importamos las traducciones
import { traducciones } from "./script_traducciones.js";

// Función para aplicarle el idioma a las páginas
function setLenguaje(lang) {
    // Verificamos que el idioma exista en el diccionario
    if (!traducciones[lang]) return;

    // Guardamos el idioma seleccionado para que se mantenga entre páginas
    localStorage.setItem("idioma", lang);

    // Guardamos los elementos que traducen el texto (En los textos es "data-i18n")
    const elementos_texto = document.querySelectorAll("[data-i18n]");

    // ForEach para cambiar cada elemento por su texto de idioma
    elementos_texto.forEach((el) => {
        const key = el.getAttribute("data-i18n"); // Obtenemos todos los data-i18n en key
        const traduccion = traducciones[lang][key]; // Buscamos la traducción en el diccionario y lo guardamos
        if (traduccion) { // Verificamos que la traducción exista
            el.textContent = traduccion;
        }
    });

    // Elementos que traducen el ALT de las imágenes (En imágenes es "data-i18n-alt")
    const elementos_alt = document.querySelectorAll("[data-i18n-alt]");

    elementos_alt.forEach((el) => {
        const key = el.getAttribute("data-i18n-alt");
        const traduccion = traducciones[lang][key];
        if (traduccion) {
            el.setAttribute("alt", traduccion);
        }
    });

    //Cambio de video según el idioma
    const videos_src = document.querySelectorAll("[data-i18n-src]")

    videos_src.forEach((el) => {
        const key = el.getAttribute("data-i18n-src");
        const traduccion = traducciones[lang][key];
        if (traduccion) {
            el.setAttribute("src", traduccion);

            // Buscamos el video para recargar y cambiar el video
            const video = document.getElementById("video_galeria");
            if (video) {
                video.load();
            }
        }
    });
}

// Detectar cambios en los radio buttons y llamar a setLenguaje
document.addEventListener("DOMContentLoaded", () => { //Se asegura de que la pagina esta cargada antes de ejecutar el script
    const radios = document.querySelectorAll('input[name="plan"]');

    //Se guarda en idiomaGuardado el idioma seleccionado por el usuario, si no a selecciona por defecto se coloca español
    const idiomaGuardado = localStorage.getItem("idioma") || "es";

    //Mantener el idioma aun que se recargue la pagina
    radios.forEach((radio) => {
        if (radio.value === idiomaGuardado) {
            radio.checked = true;
        }

        radio.addEventListener("change", (event) => {
            const lang = event.target.value; // es, en, nah
            setLenguaje(lang);
        });
    });

    // Establecemos el idioma inicial (El que se encuentre guardado)
    setLenguaje(idiomaGuardado);
});
