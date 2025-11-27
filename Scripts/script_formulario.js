// A) PARTE IDENTIFICACIÓN DE LA ENTREGA 

// Obtener los selects por name
const responsable = document.querySelector('select[name="responsable_entrega"]');
const grupo = document.querySelector('select[name="grupo_entrega"]');

// Mapa responsable -> grupo
const responsable_grupo = {
    "CORONA ZAHUANTITLA MARICELA": "1AMC",
    "MENDEZ SALAS EVARISTO GUADALUPE": "1AMVT",
    "GUTIERREZ URCID FERNANDO": "1AMP",
    "ROJAS RODRIGUEZ CARLOS OCTAVIO": "1BMP",
    "GOMEZ OREA NOEMI SUSANA": "1AMINT",
    "CASTILLO CARDONA SAMANTHA RUBI": "1AMLQ",
    "PALMEROS ORTIZ EDGAR": "1BMLQ",
    "ESPINOZA SANCHEZ BRISEIDA": "1AMMTION",
    "CRUZ MARQUEZ MARIA DEL CARMEN": "3AMC",
    "TAPIA SMITH NORMA LUZ": "3AMVT",
    "VAZQUEZ GONZALEZ GUADALUPE": "3AMP",
    "CERON PEREZ SELENE": "3BMP",
    "LEON COTE BARRERA": "3AMLQ",
    "BAEZ BARRADAS ZARINA": "3AMMTION",
    "HERNANDEZ LARA DULCE MARIA": "5AMC",
    "SOTELO REYES TONANTZIN": "5AMLG",
    "PINEDA VAZQUEZ NARETH": "5AMP",
    "DELGADO ATONAL SYAMASUNDAR-DAS": "5BMP",
    "HERNANDEZ CRUZ MAXIMINO": "5AMLQ",
    "BARRIOS RODRIGUEZ GLORIA": "5AMMTION",
    "JUAREZ JUAREZ ENRIQUE": "1AVC",
    "MATIAS GUZMAN FABIOLA": "1AVE-C",
    "MORALES QUIROZ KAREN": "1AVI",
    "ATONAL FERNANDEZ STHEPANIE": "1AVP",
    "PEREZ CAMACHO GABINA": "1BCP",
    "PEREZ COYOTL ANA LAURA": "1AVLQ",
    "ITURBIDE ORTIZ LUDWINDG": "1AVMTION",
    "ARROYO SERRANO HECTOR": "1AVURB",
    "DIYARZA MEZA MARIELA": "3AVC",
    "GONZALEZ GONZALES YASMIN": "3AVVT",
    "RIVERA CASTILLO MATILDE": "3AVP",
    "RAMOS BRAVO OSCAR": "3BVP",
    "MENA MENA IRMA": "3AVLQ",
    "BLANCAS AGUILAR CESAR": "3AVMTION",
    "SANTIAGO GUZMAN MIGUEL ANGEL": "5AVC",
    "JUAREZ SOTO LORENA": "5AVLOG",
    "ESPEJEL SARTILLO LINDA": "5AVP",
    "MUÑOZ CORTES BEATRIZ": "5BVP",
    "DE LOS SANTOS MUNIVE VICTORIA": "5AVLQ",
    "VARGAS ALTAMIRANO ALEJANDRO": "5AVMTION",
};

// Crear el mapa inverso: grupo -> responsable
const grupo_responsable = {};
for (const [resp, grp] of Object.entries(responsable_grupo)) {
    grupo_responsable[grp] = resp;
}

// Manejar cambio en RESPONSABLE
if (responsable && grupo) {
    responsable.addEventListener("change", () => {
        if (responsable.value !== "") {
            // Desactiva grupo y lo autocomplete
            grupo.disabled = true;
            grupo.value = responsable_grupo[responsable.value] || "";
        } else {
            // Si limpian el responsable, se reactiva grupo
            grupo.disabled = false;
            grupo.value = "";
        }
    });

    // Manejar cambio en GRUPO
    grupo.addEventListener("change", () => {
        if (grupo.value !== "") {
            // Desactiva responsable y lo autocomplete
            responsable.disabled = true;
            responsable.value = grupo_responsable[grupo.value] || "";
        } else {
            // Si limpian el grupo, se reactiva responsable
            responsable.disabled = false;
            responsable.value = "";
        }
    });
}

// B) PARTE CANTIDADES DE CHECKBOX

// Seleccionamos todos los checkboxes dentro de la sección de cantidades
const checkboxes = document.querySelectorAll('.cantidad_entrega input[type="checkbox"]');

checkboxes.forEach((chk) => {
    // Buscamos el input de texto que está en el mismo <label> que el checkbox
    const label = chk.closest("label");
    const inputCantidad = label.querySelector('input[type="text"], input[type="number"]');

    if (!inputCantidad) return; // seguridad

    // Al inicio: desactivamos el input
    inputCantidad.disabled = true;

    // Cuando cambia el checkbox
    chk.addEventListener("change", () => {
        if (chk.checked) {
            // Si se marca: activamos el input y hacemos focus
            inputCantidad.disabled = false;
            inputCantidad.focus();
        } else {
            // Si se desmarca: limpiamos y desactivamos
            inputCantidad.value = "";
            inputCantidad.disabled = true;
        }
    });
});

