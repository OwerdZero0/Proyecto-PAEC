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

const nombreArchivos = {
    es: {
        "historia": "Proyecto de Historia.pdf",
        "matematicas": "Proyecto de Matemáticas.pdf",
        "recursos": "Proyecto de Recursos Socioemocionales.xlsx"
    },
    en: {
        "historia": "Project History.pdf",
        "matematicas": "Project Mathematics.pdf",
        "recursos": "Project Socioemotional Resources.xlsx"
    },
    nah: {
        "historia": "Tlatolmelāhuacayotl ihcuac tlatolamatl.pdf",
        "matematicas": "Tlatolmelāhuacayotl ihcuac xōchihcayotl tlamantli.pdf",
        "recursos": "Tlatolmelāhuacayotl ihcuac tlānēxtiliztli huan yōllōtl tatzkuilōtl.xlsx"
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
