document.addEventListener('DOMContentLoaded', () => {
    const botonesMenu = document.querySelectorAll('.menu_superior_boton');
    const secciones = document.querySelectorAll('.seccion_botones');

    botonesMenu.forEach((boton) => {
        boton.addEventListener('click', () => {
            const seccionObjetivo = boton.dataset.seccion;

            botonesMenu.forEach((item) => item.classList.remove('activo'));
            boton.classList.add('activo');

            secciones.forEach((seccion) => {
                seccion.classList.remove('activa');
            });

            const destino = document.getElementById(`seccion-${seccionObjetivo}`);
            if (destino) {
                destino.classList.add('activa');
            }
        });
    });
});
