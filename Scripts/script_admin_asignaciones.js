document.addEventListener("DOMContentLoaded", () => {
    /* =========================
    1) CONFIRMAR ELIMINACIONES
    ========================= */
    const formulariosEliminar = document.querySelectorAll(".form-eliminar");

    formulariosEliminar.forEach((formulario) => {
        formulario.addEventListener("submit", (e) => {
            const tipo = formulario.dataset.tipo || "registro";
            const confirmar = confirm(`¿Seguro que deseas eliminar este ${tipo}?`);

            if (!confirmar) {
                e.preventDefault();
            }
        });
    });

    /* =========================
    2) VALIDAR FORMULARIOS
    ========================= */
    const formularios = document.querySelectorAll("form");

    formularios.forEach((formulario) => {
        formulario.addEventListener("submit", (e) => {
            const camposTexto = formulario.querySelectorAll('input[type="text"], input[type="date"], textarea');

            for (let campo of camposTexto) {
                if (campo.hasAttribute("required")) {
                    if (campo.value.trim() === "") {
                        alert("Hay campos obligatorios vacíos.");
                        campo.focus();
                        e.preventDefault();
                        return;
                    }
                }
            }

            const selects = formulario.querySelectorAll("select");

            for (let select of selects) {
                if (select.hasAttribute("required")) {
                    if (select.value.trim() === "") {
                        alert("Debes seleccionar una opción válida.");
                        select.focus();
                        e.preventDefault();
                        return;
                    }
                }
            }
        });
    });

    /* =========================
    3) FILTRO DE ASIGNACIONES
    ========================= */
    const buscador = document.getElementById("buscador_asignaciones");
    const filasAsignaciones = document.querySelectorAll(".tabla-asignaciones tbody tr");

    if (buscador) {
        buscador.addEventListener("keyup", () => {
            const texto = buscador.value.toLowerCase().trim();

            filasAsignaciones.forEach((fila) => {
                const contenidoFila = fila.textContent.toLowerCase();

                if (contenidoFila.includes(texto)) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });
        });
    }
});