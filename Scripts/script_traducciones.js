// Importacion de las traducciones de cada pagina

import {portada} from "./Traducciones i18n/portada.js"
import {centro_acopio} from "./Traducciones i18n/centro_acopio.js"
import {reciclaje} from "./Traducciones i18n/reciclaje.js"
import {galeria} from "./Traducciones i18n/galeria.js"

// Diccionario con todos los idiomas de la pagina (es = Español, en = English, nah = Nahuatl).

export const traducciones = { 
    es: { 
        //TEXTOS EN ESPAÑOL

        ...portada.es,
        ...centro_acopio.es,
        ...reciclaje.es,
        ...galeria.es
    },

    en: {
        //TEXTOS EN INGLES

        ...portada.en,
        ...centro_acopio.en,
        ...reciclaje.en,
        ...galeria.en
    },

    nah: {
        // TEXTOS EN NAHUATL (TRADUCCION APROXIMADA)

        ...portada.nah,
        ...centro_acopio.nah,
        ...reciclaje.nah,
        ...galeria.nah
    }
};