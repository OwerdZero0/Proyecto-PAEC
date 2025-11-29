// Importación de las traducciones de cada página

import { inicio } from "./Traducciones i18n/inicio.js"
import { centro_acopio } from "./Traducciones i18n/centro_acopio.js"
import { reciclaje } from "./Traducciones i18n/reciclaje.js"
import { galeria } from "./Traducciones i18n/galeria.js"
import { formulario } from "./Traducciones i18n/formulario.js"
import { descargables } from "./Traducciones i18n/descargables.js"
import { calculo_reciclaje } from "./Traducciones i18n/calculo_reciclaje.js"

// Diccionario que contiene todos los idiomas de la página (es = Español, en = English, nah = Nahuatl).

export const traducciones = {
    es: {
        // TEXTOS EN ESPAÑOL

        ...inicio.es,
        ...centro_acopio.es,
        ...reciclaje.es,
        ...galeria.es,
        ...formulario.es,
        ...descargables.es,
        ...calculo_reciclaje.es
    },

    en: {
        // TEXTOS EN INGLÉS

        ...inicio.en,
        ...centro_acopio.en,
        ...reciclaje.en,
        ...galeria.en,
        ...formulario.en,
        ...descargables.en,
        ...calculo_reciclaje.en
    },

    nah: {
        // TEXTOS EN NAHUATL (TRADUCCIÓN APROXIMADA)

        ...inicio.nah,
        ...centro_acopio.nah,
        ...reciclaje.nah,
        ...galeria.nah,
        ...formulario.nah,
        ...descargables.nah,
        ...calculo_reciclaje.nah
    }
};