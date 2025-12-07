const archivos = {
    es: {
        "historia": "Multimedia/Archivos/historia_es.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_es.pdf",
        "recursos": "Multimedia/Archivos/recursos_es.xlsx",
        "energia": "Multimedia/Videos/Descargables/fisica_es.mp4"
    },
    en: {
        "historia": "Multimedia/Archivos/historia_en.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_en.pdf",
        "recursos": "Multimedia/Archivos/recursos_en.xlsx",
        "energia": "Multimedia/Videos/Descargables/fisica_en.mp4"
    },
    nah: {
        "historia": "Multimedia/Archivos/historia_nah.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_nah.pdf",
        "recursos": "Multimedia/Archivos/recursos_nah.xlsx",
        "energia": "Multimedia/Videos/Descargables/fisica_nah.mp4"
    }
}

const nombreArchivos = {
    es: {
        "historia": "Proyecto de Conciencia histórica II.pdf",
        "matematicas": "Proyecto de Temas selectivos de matemáticas II.pdf",
        "recursos": "Proyecto de Formación Socioemocionales V.xlsx",
        "energia": "Proyecto de Energía en los procesos de la vida diaria.mp4"
    },
    en: {
        "historia": "Historical Awareness Project II.pdf",
        "matematicas": "Selective Mathematics Topics Project II.pdf",
        "recursos": "Socioemotional Development Project V.xlsx",
        "energia": "Energy in Everyday Life Processes Project.mp4"
    },
    nah: {
        "historia": "Nikan Tlachiyalistli Histórica Tlamachtilli II.pdf",
        "matematicas": "Tlamachtilli de Matemáticas Tlen Tlakamej Tématl II.pdf",
        "recursos": "Tlamachtilli de Yeknemilistli V.xlsx",
        "energia": "Tlamachtilli de Tlamavalolistli ipan Tonalmej Yaoyotl.mp4"
    }
}

const idiomaGuardado = localStorage.getItem('idioma');

function descargar_historia() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["historia"];
    link.download = nombreArchivos[idiomaGuardado]["historia"];
    link.click();
}

function descargar_mate() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["matematicas"];
    link.download = nombreArchivos[idiomaGuardado]["matematicas"];
    link.click();
}

function descargar_recursos() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["recursos"];
    link.download = nombreArchivos[idiomaGuardado]["recursos"];
    link.click();
}

function descargar_energia() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["energia"];
    link.download = nombreArchivos[idiomaGuardado]["energia"];
    link.click();
}
