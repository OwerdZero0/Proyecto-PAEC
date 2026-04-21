/* =========================================================
    SECCIÓN 1: ESPERAR A QUE CARGUE EL DOM
    ---------------------------------------------------------
    Todo el código se ejecuta hasta que el HTML esté listo.
========================================================= */
document.addEventListener('DOMContentLoaded', async function () {

    /* =====================================================
        SECCIÓN 2: OBTENER ELEMENTOS DEL FORMULARIO
        -----------------------------------------------------
        Aquí capturamos los elementos que vamos a usar:
        - formulario
        - select de responsable
        - select de grupo
        - checkboxes de materiales
    ===================================================== */
    const formulario = document.querySelector('.formulario');
    const responsable = document.querySelector('select[name="responsable_entrega"]');
    const grupo = document.querySelector('select[name="grupo_entrega"]');
    const checks = document.querySelectorAll('.cantidad_entrega input[type="checkbox"]');

    /* =====================================================
        SECCIÓN 3: ESTRUCTURAS DE DATOS
        -----------------------------------------------------
        Estas variables guardarán:
        - las asignaciones recibidas desde PHP
        - un mapa responsable -> grupo
        - un mapa grupo -> responsable
        
        Esto permite que si eliges un maestro, se coloque
        automáticamente su grupo, y viceversa.
    ===================================================== */
    let asignaciones = [];
    let responsableGrupo = {};
    let grupoResponsable = {};

    /* =====================================================
        SECCIÓN 4: FUNCIÓN PARA CARGAR ASIGNACIONES DESDE PHP
        -----------------------------------------------------
        Esta función hace fetch al archivo PHP que consulta
        la base de datos.

        Después:
        - limpia los selects
        - crea grupos por turno
        - llena las opciones
        - arma los diccionarios automáticos
    ===================================================== */
    async function cargarAsignaciones() {
        try {
            const respuesta = await fetch('obtener_asignaciones_formulario.php');

            if (!respuesta.ok) {
                throw new Error('No se pudo obtener la información del servidor.');
            }

            const data = await respuesta.json();

            if (!data.ok) {
                throw new Error(data.mensaje || 'Error al cargar asignaciones.');
            }

            asignaciones = data.asignaciones || [];

            /* -------------------------------------------------
                Reiniciar contenido de los selects
            ------------------------------------------------- */
            responsable.innerHTML = '<option value="">Seleccione un responsable</option>';
            grupo.innerHTML = '<option value="">Seleccione un grupo</option>';

            /* -------------------------------------------------
                Reiniciar diccionarios
            ------------------------------------------------- */
            responsableGrupo = {};
            grupoResponsable = {};

            /* -------------------------------------------------
                Crear optgroups por turno para responsables
            ------------------------------------------------- */
            const optMatutinoResp = document.createElement('optgroup');
            optMatutinoResp.label = 'Matutino';

            const optVespertinoResp = document.createElement('optgroup');
            optVespertinoResp.label = 'Vespertino';

            /* -------------------------------------------------
                Crear optgroups por turno para grupos
            ------------------------------------------------- */
            const optMatutinoGrupo = document.createElement('optgroup');
            optMatutinoGrupo.label = 'Matutino';

            const optVespertinoGrupo = document.createElement('optgroup');
            optVespertinoGrupo.label = 'Vespertino';

            /* -------------------------------------------------
                Recorrer asignaciones y llenar estructuras
            ------------------------------------------------- */
            asignaciones.forEach(item => {
                const nombreMaestro = String(item.nombre_maestro || '').trim();
                const nombreGrupo = String(item.nombre_grupo || '').trim();
                const turno = String(item.turno || '').trim();

                if (!nombreMaestro || !nombreGrupo) {
                    return;
                }

                /* ---------------------------------------------
                    Guardar relación en ambos sentidos
                --------------------------------------------- */
                responsableGrupo[nombreMaestro] = nombreGrupo;
                grupoResponsable[nombreGrupo] = nombreMaestro;

                /* ---------------------------------------------
                    Crear opción para responsable
                --------------------------------------------- */
                const optionResp = document.createElement('option');
                optionResp.value = nombreMaestro;
                optionResp.textContent = nombreMaestro;

                /* ---------------------------------------------
                    Crear opción para grupo
                --------------------------------------------- */
                const optionGrupo = document.createElement('option');
                optionGrupo.value = nombreGrupo;
                optionGrupo.textContent = nombreGrupo;

                /* ---------------------------------------------
                    Clasificar por turno
                --------------------------------------------- */
                if (turno === 'Matutino') {
                    optMatutinoResp.appendChild(optionResp);
                    optMatutinoGrupo.appendChild(optionGrupo);
                } else {
                    optVespertinoResp.appendChild(optionResp);
                    optVespertinoGrupo.appendChild(optionGrupo);
                }
            });

            /* -------------------------------------------------
                Agregar grupos solo si tienen opciones
            ------------------------------------------------- */
            if (optMatutinoResp.children.length > 0) {
                responsable.appendChild(optMatutinoResp);
            }

            if (optVespertinoResp.children.length > 0) {
                responsable.appendChild(optVespertinoResp);
            }

            if (optMatutinoGrupo.children.length > 0) {
                grupo.appendChild(optMatutinoGrupo);
            }

            if (optVespertinoGrupo.children.length > 0) {
                grupo.appendChild(optVespertinoGrupo);
            }

        } catch (error) {
            console.error('Error al cargar asignaciones:', error);
            alert('No se pudieron cargar los responsables y grupos desde la base de datos.');
        }
    }

    /* =====================================================
        SECCIÓN 5: ACTIVAR Y DESACTIVAR CAMPOS DE CANTIDAD
        -----------------------------------------------------
        Si un material está marcado:
        - se habilita su input numérico
        - se vuelve obligatorio

        Si no está marcado:
        - se deshabilita
        - se limpia
        - deja de ser obligatorio
    ===================================================== */
    function actualizarCamposMaterial() {
        checks.forEach(check => {
            const label = check.closest('label');
            if (!label) return;

            const inputCantidad = label.querySelector('input[type="number"]');
            if (!inputCantidad) return;

            if (check.checked) {
                inputCantidad.required = true;
                inputCantidad.disabled = false;
                inputCantidad.min = "0.001";
                inputCantidad.max = "9999999999.999";
                inputCantidad.step = "0.001";
            } else {
                inputCantidad.required = false;
                inputCantidad.value = '';
                inputCantidad.disabled = true;
            }
        });
    }

    /* =====================================================
        SECCIÓN 6: CARGAR DATOS DESDE LA BASE DE DATOS
        -----------------------------------------------------
        Antes de trabajar con los selects, primero se cargan
        las asignaciones desde PHP.
    ===================================================== */
    await cargarAsignaciones();

    /* =====================================================
        SECCIÓN 7: RELACIÓN AUTOMÁTICA RESPONSABLE <-> GRUPO
        -----------------------------------------------------
        Si eliges un responsable, se pone su grupo.
        Si eliges un grupo, se pone su responsable.
    ===================================================== */
    if (responsable && grupo) {

        responsable.addEventListener('change', () => {
            if (responsable.value !== "") {
                const grupoCalculado = responsableGrupo[responsable.value] || "";
                grupo.value = grupoCalculado;
            } else {
                grupo.value = "";
            }
        });

        grupo.addEventListener('change', () => {
            if (grupo.value !== "") {
                const responsableCalculado = grupoResponsable[grupo.value] || "";
                responsable.value = responsableCalculado;
            } else {
                responsable.value = "";
            }
        });
    }

    /* =====================================================
        SECCIÓN 8: ESTADO INICIAL DE LOS MATERIALES
        -----------------------------------------------------
        Apenas carga la página, se actualiza el estado de los
        campos numéricos para que no queden activos sin razón.
    ===================================================== */
    actualizarCamposMaterial();

    /* =====================================================
        SECCIÓN 9: ESCUCHAR CAMBIOS EN LOS CHECKBOXES
        -----------------------------------------------------
        Cada vez que se marca o desmarca un material, se
        actualiza el input de cantidad correspondiente.
    ===================================================== */
    checks.forEach(check => {
        check.addEventListener('change', actualizarCamposMaterial);
    });

    /* -----------------------------------------------------
        RESTRINGIR A 10 ENTEROS Y 3 DECIMALES EN TIEMPO REAL
    ----------------------------------------------------- */
    const inputsCantidades = document.querySelectorAll('.cantidad_entrega input[type="number"]');
    inputsCantidades.forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value;
            if (val.includes('.')) {
                let parts = val.split('.');
                let entero = parts[0];
                let decimal = parts[1] || '';
                
                if (entero.length > 10) {
                    entero = entero.slice(0, 10);
                }
                if (decimal.length > 3) {
                    decimal = decimal.slice(0, 3);
                }
                
                // Si el input comenzó con . (ej: .5)
                if (entero === "" && val.startsWith('.')) {
                    entero = "0";
                }
                
                this.value = `${entero}.${decimal}`;
            } else {
                if (val.length > 10) {
                    this.value = val.slice(0, 10);
                }
            }
        });
    });

    /* =====================================================
        SECCIÓN 10: VALIDAR ENVÍO DEL FORMULARIO
        -----------------------------------------------------
        Aquí se valida que:
        - haya al menos un material seleccionado
        - el usuario confirme el envío
    ===================================================== */
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();

        const seleccionados = document.querySelectorAll('.cantidad_entrega input[type="checkbox"]:checked');

        if (seleccionados.length === 0) {
            Swal.fire({
                title: 'Atención',
                text: 'Debe seleccionar al menos un material para registrar la entrega.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#00695c',
                background: 'rgba(255, 255, 255, 0.95)',
                color: '#37474f',
                backdrop: 'rgba(0, 105, 92, 0.4)'
            });
            return;
        }

        let cantidadInvalida = false;
        seleccionados.forEach(check => {
            const label = check.closest('label');
            if (label) {
                const inputCantidad = label.querySelector('input[type="number"]');
                if (inputCantidad && parseFloat(inputCantidad.value) <= 0) {
                    cantidadInvalida = true;
                }
            }
        });

        if (cantidadInvalida) {
            Swal.fire({
                title: 'Cantidad inválida',
                text: 'La cantidad de los materiales seleccionados no puede ser 0 kg. Por favor ingrese una cantidad válida mayor a 0.',
                icon: 'error',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#00695c',
                background: 'rgba(255, 255, 255, 0.95)',
                color: '#37474f',
                backdrop: 'rgba(0, 105, 92, 0.4)'
            });
            return;
        }

        Swal.fire({
            title: '¿Confirmar envío?',
            text: '¿Está seguro(a) de enviar esta información? Verifique que los datos sean correctos antes de continuar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#00695c',
            cancelButtonColor: '#e53935',
            background: 'rgba(255, 255, 255, 0.95)',
            color: '#37474f',
            backdrop: 'rgba(0, 105, 92, 0.4)'
        }).then((result) => {
            if (result.isConfirmed) {
                formulario.submit();
            }
        });
    });

    /* =====================================================
        SECCIÓN 11: COMPORTAMIENTO AL REINICIAR FORMULARIO
        -----------------------------------------------------
        Cuando el usuario presiona "Borrar":
        - se restauran los campos de materiales
        - se limpian responsable y grupo
    ===================================================== */
    formulario.addEventListener('reset', function () {
        setTimeout(() => {
            actualizarCamposMaterial();
            responsable.value = "";
            grupo.value = "";
        }, 0);
    });

});