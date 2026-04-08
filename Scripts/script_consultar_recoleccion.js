document.addEventListener('DOMContentLoaded', () => {
    const selectMaestro = document.getElementById('maestro');
    const selectGrupo = document.getElementById('grupo');
    
    const selectedMaestro = selectMaestro.getAttribute('data-selected') || '';
    const selectedGrupo = selectGrupo.getAttribute('data-selected') || '';

    let asignaciones = [];

    fetch('obtener_asignaciones_formulario.php')
        .then(res => res.json())
        .then(data => {
            if (data.ok && data.asignaciones) {
                asignaciones = data.asignaciones;
                
                // Llenar ambos con todas las opciones (jamás se encogen)
                llenarTodosLosMaestros();
                llenarTodosLosGrupos();
            } else {
                console.error("No se recibieron datos de asignaciones", data);
            }
        })
        .catch(err => {
            console.error('Error durante la carga:', err);
            selectMaestro.innerHTML = '<option value="">Error cargando</option>';
        });

    function llenarTodosLosMaestros() {
        const maestrosUnicos = new Set();
        asignaciones.forEach(a => maestrosUnicos.add(a.nombre_maestro));

        const maestrosArr = Array.from(maestrosUnicos).sort();
        let html = '<option value="">Todos</option>';
        
        maestrosArr.forEach(m => {
            const isSelected = (m === selectedMaestro) ? 'selected' : '';
            html += `<option value="${m}" ${isSelected}>${m}</option>`;
        });

        selectMaestro.innerHTML = html;
        if (selectedMaestro) selectMaestro.value = selectedMaestro;
    }

    function llenarTodosLosGrupos() {
        const gruposUnicos = new Set();
        asignaciones.forEach(a => gruposUnicos.add(a.nombre_grupo));

        const gruposArr = Array.from(gruposUnicos).sort();
        let html = '<option value="">Todos</option>';
        
        gruposArr.forEach(g => {
            const isSelected = (g === selectedGrupo) ? 'selected' : '';
            html += `<option value="${g}" ${isSelected}>${g}</option>`;
        });

        selectGrupo.innerHTML = html;
        if (selectedGrupo) selectGrupo.value = selectedGrupo;
    }

    // Cuando cambia maestro, autoseleccionar grupo (sin encoger la lista)
    selectMaestro.addEventListener('change', (e) => {
        const maestroElegido = e.target.value;
        if (maestroElegido !== '') {
            const gruposDeEsteMaestro = asignaciones.filter(a => a.nombre_maestro === maestroElegido);
            if (gruposDeEsteMaestro.length === 1) {
                // Autocompletar el campo de grupo al grupo de este maestro
                selectGrupo.value = gruposDeEsteMaestro[0].nombre_grupo;
            }
        }
    });

    // Cuando cambia grupo, autoseleccionar maestro (sin encoger la lista)
    selectGrupo.addEventListener('change', (e) => {
        const grupoElegido = e.target.value;
        if (grupoElegido !== '') {
            const asignacionEncontrada = asignaciones.find(a => a.nombre_grupo === grupoElegido);
            if (asignacionEncontrada) {
                // Autocompletar el campo de maestro al maestro de este grupo
                selectMaestro.value = asignacionEncontrada.nombre_maestro;
            }
        }
    });
});
