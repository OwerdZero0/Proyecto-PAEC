const archivos = {
    es: {
        "historia": "Multimedia/Archivos/historia_es.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_es.pdf",
        "recursos": "Multimedia/Archivos/recursos_es.xlsx"
    },
    en: {
        "historia": "Multimedia/Archivos/historia_en.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_en.pdf",
        "recursos": "Multimedia/Archivos/recursos_en.xlsx"
    },
    nah: {
        "historia": "Multimedia/Archivos/historia_nah.pdf",
        "matematicas": "Multimedia/Archivos/matematicas_nah.pdf",
        "recursos": "Multimedia/Archivos/recursos_nah.xlsx"
    }
}

const idiomaGuardado = localStorage.getItem('idioma');

function descargar_historia() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["historia"];
    link.download = "Proyecto de Historia.pdf";
    link.click();
}

function descargar_mate() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["matematicas"];
    link.download = "Proyecto de Matemáticas.pdf";
    link.click();
}

function descargar_recursos() {
    const link = document.createElement('a');
    link.href = archivos[idiomaGuardado]["recursos"];
    link.download = "Proyecto de Recursos Socioemocionales.xlsx";
    link.click();
}
