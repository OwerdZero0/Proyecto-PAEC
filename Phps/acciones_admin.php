<?php
/* =========================================
    CONEXION
========================================= */
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion")
    or die("Problemas con la conexión: " . mysqli_connect_error());

mysqli_set_charset($conexion, "utf8mb4");

/* =========================================
    FUNCIONES AUXILIARES
========================================= */
function limpiar($conexion, $valor) {
    return mysqli_real_escape_string($conexion, trim((string)$valor));
}

function longitud_texto($valor) {
    return function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
}

function redirigir_a($ruta, $tipo, $mensaje) {
    $ruta = trim($ruta) !== '' ? $ruta : 'admin_asignaciones.php';
    $tipo = urlencode($tipo);
    $mensaje = urlencode($mensaje);
    header("Location: {$ruta}?tipo={$tipo}&mensaje={$mensaje}");
    exit;
}

function redirigir($tipo, $mensaje) {
    redirigir_a('admin_asignaciones.php', $tipo, $mensaje);
}

function redirigir_formulario($tipo, $mensaje) {
    redirigir_a('formulario.php', $tipo, $mensaje);
}

function obtener_decimal_post($campo) {
    $valor = trim((string)($_POST[$campo] ?? ''));

    if ($valor === '') {
        return 0.00;
    }

    $valor = str_replace(',', '.', $valor);

    if (!is_numeric($valor)) {
        return null;
    }

    $numero = (float)$valor;

    if ($numero < 0) {
        return null;
    }

    return round($numero, 2);
}

/* =========================================
    VALIDAR METODO
========================================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("error", "Acceso no permitido.");
}

$accion = trim((string)($_POST["accion"] ?? ""));

if ($accion === '') {
    if (
        isset($_POST['fecha_entrega']) ||
        isset($_POST['responsable_entrega']) ||
        isset($_POST['grupo_entrega'])
    ) {
        $accion = 'guardar_recoleccion';
    } else {
        redirigir("error", "No se recibió ninguna acción.");
    }
}

/* =========================================
    ACCIONES
========================================= */

/* =========================================
    1) AGREGAR MAESTRO
========================================= */
if ($accion === "agregar_maestro") {
    $nombre_maestro = limpiar($conexion, $_POST["nombre_maestro"] ?? "");

    if ($nombre_maestro === "") {
        redirigir("error", "El nombre del maestro es obligatorio.");
    }

    $sql_verificar = "SELECT id_maestro FROM maestros WHERE nombre_maestro = '$nombre_maestro' LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir("error", "Ese maestro ya existe.");
    }

    $sql_insertar = "INSERT INTO maestros (nombre_maestro, activo) VALUES ('$nombre_maestro', 1)";

    if (mysqli_query($conexion, $sql_insertar)) {
        redirigir("ok", "Maestro agregado correctamente.");
    } else {
        redirigir("error", "Error al agregar maestro: " . mysqli_error($conexion));
    }
}

/* =========================================
    2) EDITAR MAESTRO
========================================= */
if ($accion === "editar_maestro") {
    $id_maestro = (int)($_POST["id_maestro"] ?? 0);
    $nombre_maestro = limpiar($conexion, $_POST["nombre_maestro"] ?? "");
    $activo = isset($_POST["activo"]) ? 1 : 0;

    if ($id_maestro <= 0 || $nombre_maestro === "") {
        redirigir("error", "Datos inválidos para editar maestro.");
    }

    $sql_verificar = "SELECT id_maestro
                        FROM maestros
                        WHERE nombre_maestro = '$nombre_maestro'
                        AND id_maestro <> $id_maestro
                        LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir("error", "Ya existe otro maestro con ese nombre.");
    }

    $sql_actualizar = "UPDATE maestros
                        SET nombre_maestro = '$nombre_maestro',
                            activo = $activo
                        WHERE id_maestro = $id_maestro";

    if (mysqli_query($conexion, $sql_actualizar)) {
        redirigir("ok", "Maestro actualizado correctamente.");
    } else {
        redirigir("error", "Error al actualizar maestro: " . mysqli_error($conexion));
    }
}

/* =========================================
    3) ELIMINAR MAESTRO
========================================= */
if ($accion === "eliminar_maestro") {
    $id_maestro = (int)($_POST["id_maestro"] ?? 0);

    if ($id_maestro <= 0) {
        redirigir("error", "ID de maestro inválido.");
    }

    $sql_eliminar = "DELETE FROM maestros WHERE id_maestro = $id_maestro";

    if (mysqli_query($conexion, $sql_eliminar)) {
        redirigir("ok", "Maestro eliminado correctamente.");
    } else {
        redirigir("error", "Error al eliminar maestro: " . mysqli_error($conexion));
    }
}

/* =========================================
    4) AGREGAR GRUPO
========================================= */
if ($accion === "agregar_grupo") {
    $nombre_grupo = limpiar($conexion, $_POST["nombre_grupo"] ?? "");
    $ciclo_escolar = limpiar($conexion, $_POST["ciclo_escolar"] ?? "");
    $turno = limpiar($conexion, $_POST["turno"] ?? "");

    if ($nombre_grupo === "" || $ciclo_escolar === "" || $turno === "") {
        redirigir("error", "Todos los campos del grupo son obligatorios.");
    }

    if ($turno !== "Matutino" && $turno !== "Vespertino") {
        redirigir("error", "Turno inválido.");
    }

    $sql_verificar = "SELECT id_grupo
                        FROM grupos
                        WHERE nombre_grupo = '$nombre_grupo'
                        AND ciclo_escolar = '$ciclo_escolar'
                        AND turno = '$turno'
                        LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir("error", "Ese grupo ya existe en ese ciclo y turno.");
    }

    $sql_insertar = "INSERT INTO grupos (nombre_grupo, ciclo_escolar, turno, activo)
                        VALUES ('$nombre_grupo', '$ciclo_escolar', '$turno', 1)";

    if (mysqli_query($conexion, $sql_insertar)) {
        redirigir("ok", "Grupo agregado correctamente.");
    } else {
        redirigir("error", "Error al agregar grupo: " . mysqli_error($conexion));
    }
}

/* =========================================
    5) EDITAR GRUPO
========================================= */
if ($accion === "editar_grupo") {
    $id_grupo = (int)($_POST["id_grupo"] ?? 0);
    $nombre_grupo = limpiar($conexion, $_POST["nombre_grupo"] ?? "");
    $ciclo_escolar = limpiar($conexion, $_POST["ciclo_escolar"] ?? "");
    $turno = limpiar($conexion, $_POST["turno"] ?? "");
    $activo = isset($_POST["activo"]) ? 1 : 0;

    if ($id_grupo <= 0 || $nombre_grupo === "" || $ciclo_escolar === "" || $turno === "") {
        redirigir("error", "Datos inválidos para editar grupo.");
    }

    if ($turno !== "Matutino" && $turno !== "Vespertino") {
        redirigir("error", "Turno inválido.");
    }

    $sql_verificar = "SELECT id_grupo
                        FROM grupos
                        WHERE nombre_grupo = '$nombre_grupo'
                        AND ciclo_escolar = '$ciclo_escolar'
                        AND turno = '$turno'
                        AND id_grupo <> $id_grupo
                        LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir("error", "Ya existe otro grupo con esos mismos datos.");
    }

    $sql_actualizar = "UPDATE grupos
                        SET nombre_grupo = '$nombre_grupo',
                            ciclo_escolar = '$ciclo_escolar',
                            turno = '$turno',
                            activo = $activo
                        WHERE id_grupo = $id_grupo";

    if (mysqli_query($conexion, $sql_actualizar)) {
        redirigir("ok", "Grupo actualizado correctamente.");
    } else {
        redirigir("error", "Error al actualizar grupo: " . mysqli_error($conexion));
    }
}

/* =========================================
    6) ELIMINAR GRUPO
========================================= */
if ($accion === "eliminar_grupo") {
    $id_grupo = (int)($_POST["id_grupo"] ?? 0);

    if ($id_grupo <= 0) {
        redirigir("error", "ID de grupo inválido.");
    }

    $sql_eliminar = "DELETE FROM grupos WHERE id_grupo = $id_grupo";

    if (mysqli_query($conexion, $sql_eliminar)) {
        redirigir("ok", "Grupo eliminado correctamente.");
    } else {
        redirigir("error", "Error al eliminar grupo: " . mysqli_error($conexion));
    }
}

/* =========================================
    7) AGREGAR ASIGNACION
========================================= */
if ($accion === "asignar" || $accion === "agregar_asignacion") {
    $id_maestro = (int)($_POST["id_maestro"] ?? 0);
    $id_grupo = (int)($_POST["id_grupo"] ?? 0);

    if ($id_maestro <= 0 || $id_grupo <= 0) {
        redirigir("error", "Debes seleccionar un maestro y un grupo.");
    }

    $sql_existe_maestro = "SELECT id_asignacion FROM asignaciones WHERE id_maestro = $id_maestro LIMIT 1";
    $sql_existe_grupo = "SELECT id_asignacion FROM asignaciones WHERE id_grupo = $id_grupo LIMIT 1";

    $resultado_maestro = mysqli_query($conexion, $sql_existe_maestro);
    $resultado_grupo = mysqli_query($conexion, $sql_existe_grupo);

    if ($resultado_maestro && mysqli_num_rows($resultado_maestro) > 0) {
        redirigir("error", "Ese maestro ya tiene un grupo asignado.");
    }

    if ($resultado_grupo && mysqli_num_rows($resultado_grupo) > 0) {
        redirigir("error", "Ese grupo ya está asignado a otro maestro.");
    }

    $sql_insertar = "INSERT INTO asignaciones (id_maestro, id_grupo)
                        VALUES ($id_maestro, $id_grupo)";

    if (mysqli_query($conexion, $sql_insertar)) {
        redirigir("ok", "Asignación creada correctamente.");
    } else {
        redirigir("error", "Error al crear asignación: " . mysqli_error($conexion));
    }
}

/* =========================================
    8) EDITAR ASIGNACION
========================================= */
if ($accion === "editar_asignacion") {
    $id_asignacion = (int)($_POST["id_asignacion"] ?? 0);
    $id_maestro = (int)($_POST["id_maestro"] ?? 0);
    $id_grupo = (int)($_POST["id_grupo"] ?? 0);

    if ($id_asignacion <= 0 || $id_maestro <= 0 || $id_grupo <= 0) {
        redirigir("error", "Datos inválidos para editar la asignación.");
    }

    $sql_existe_maestro = "SELECT id_asignacion
                            FROM asignaciones
                            WHERE id_maestro = $id_maestro
                            AND id_asignacion <> $id_asignacion
                            LIMIT 1";

    $sql_existe_grupo = "SELECT id_asignacion
                            FROM asignaciones
                            WHERE id_grupo = $id_grupo
                            AND id_asignacion <> $id_asignacion
                            LIMIT 1";

    $resultado_maestro = mysqli_query($conexion, $sql_existe_maestro);
    $resultado_grupo = mysqli_query($conexion, $sql_existe_grupo);

    if ($resultado_maestro && mysqli_num_rows($resultado_maestro) > 0) {
        redirigir("error", "Ese maestro ya está asignado en otro registro.");
    }

    if ($resultado_grupo && mysqli_num_rows($resultado_grupo) > 0) {
        redirigir("error", "Ese grupo ya está asignado en otro registro.");
    }

    $sql_actualizar = "UPDATE asignaciones
                        SET id_maestro = $id_maestro,
                            id_grupo = $id_grupo
                        WHERE id_asignacion = $id_asignacion";

    if (mysqli_query($conexion, $sql_actualizar)) {
        redirigir("ok", "Asignación actualizada correctamente.");
    } else {
        redirigir("error", "Error al actualizar asignación: " . mysqli_error($conexion));
    }
}

/* =========================================
    9) ELIMINAR ASIGNACION
========================================= */
if ($accion === "eliminar_asignacion") {
    $id_asignacion = (int)($_POST["id_asignacion"] ?? 0);

    if ($id_asignacion <= 0) {
        redirigir("error", "ID de asignación inválido.");
    }

    $sql_eliminar = "DELETE FROM asignaciones WHERE id_asignacion = $id_asignacion";

    if (mysqli_query($conexion, $sql_eliminar)) {
        redirigir("ok", "Asignación eliminada correctamente.");
    } else {
        redirigir("error", "Error al eliminar asignación: " . mysqli_error($conexion));
    }
}

/* =========================================
    10) GUARDAR RECOLECCION
========================================= */
if ($accion === 'guardar_recoleccion') {
    $fecha_entrega = trim((string)($_POST['fecha_entrega'] ?? ''));
    $responsable_entrega = trim((string)($_POST['responsable_entrega'] ?? ''));
    $grupo_entrega = trim((string)($_POST['grupo_entrega'] ?? ''));
    $observaciones = trim((string)($_POST['observaciones'] ?? ''));

    $material_pet = isset($_POST['material_pet']) ? 1 : 0;
    $material_carton = isset($_POST['material_carton']) ? 1 : 0;
    $material_tapas = isset($_POST['material_tapas']) ? 1 : 0;
    $material_vidrio = isset($_POST['material_vidrio']) ? 1 : 0;
    $material_electrodomesticos = isset($_POST['material_electrodomesticos']) ? 1 : 0;
    $material_papel = isset($_POST['material_papel']) ? 1 : 0;

    if ($fecha_entrega === '') {
        redirigir_formulario('error', 'La fecha de entrega es obligatoria.');
    }

    $fecha_valida = DateTime::createFromFormat('Y-m-d', $fecha_entrega);
    if (!$fecha_valida || $fecha_valida->format('Y-m-d') !== $fecha_entrega) {
        redirigir_formulario('error', 'La fecha de entrega no es válida.');
    }

    if ($responsable_entrega === '') {
        redirigir_formulario('error', 'Debes seleccionar un responsable de entrega.');
    }

    if ($grupo_entrega === '') {
        redirigir_formulario('error', 'Debes seleccionar un grupo.');
    }

    if (
        $material_pet === 0 &&
        $material_carton === 0 &&
        $material_tapas === 0 &&
        $material_vidrio === 0 &&
        $material_electrodomesticos === 0 &&
        $material_papel === 0
    ) {
        redirigir_formulario('error', 'Debes seleccionar al menos un material entregado.');
    }

    if (longitud_texto($responsable_entrega) > 80) {
        redirigir_formulario('error', 'El nombre del responsable excede el tamaño permitido.');
    }

    if (longitud_texto($grupo_entrega) > 50) {
        redirigir_formulario('error', 'El grupo excede el tamaño permitido.');
    }

    if ($observaciones !== '' && longitud_texto($observaciones) > 250) {
        redirigir_formulario('error', 'Las observaciones no pueden exceder 250 caracteres.');
    }

    $cantidad_pet = obtener_decimal_post('cantidad_pet');
    $cantidad_carton = obtener_decimal_post('cantidad_carton');
    $cantidad_tapas = obtener_decimal_post('cantidad_tapas');
    $cantidad_vidrio = obtener_decimal_post('cantidad_vidrio');
    $cantidad_electrodomesticos = obtener_decimal_post('cantidad_electrodomesticos');
    $cantidad_papel = obtener_decimal_post('cantidad_papel');

    if (
        $cantidad_pet === null ||
        $cantidad_carton === null ||
        $cantidad_tapas === null ||
        $cantidad_vidrio === null ||
        $cantidad_electrodomesticos === null ||
        $cantidad_papel === null
    ) {
        redirigir_formulario('error', 'Todas las cantidades deben ser numéricas y mayores o iguales a 0.');
    }

    if (
        ($material_pet === 1 && $cantidad_pet <= 0) ||
        ($material_carton === 1 && $cantidad_carton <= 0) ||
        ($material_tapas === 1 && $cantidad_tapas <= 0) ||
        ($material_vidrio === 1 && $cantidad_vidrio <= 0) ||
        ($material_electrodomesticos === 1 && $cantidad_electrodomesticos <= 0) ||
        ($material_papel === 1 && $cantidad_papel <= 0)
    ) {
        redirigir_formulario('error', 'La cantidad de los materiales seleccionados no puede ser de 0 kg.');
    }

    if ($material_pet === 0) {
        $cantidad_pet = 0.00;
    }
    if ($material_carton === 0) {
        $cantidad_carton = 0.00;
    }
    if ($material_tapas === 0) {
        $cantidad_tapas = 0.00;
    }
    if ($material_vidrio === 0) {
        $cantidad_vidrio = 0.00;
    }
    if ($material_electrodomesticos === 0) {
        $cantidad_electrodomesticos = 0.00;
    }
    if ($material_papel === 0) {
        $cantidad_papel = 0.00;
    }

    $stmt = mysqli_prepare(
        $conexion,
        'INSERT INTO recoleccion (
            fecha_entrega,
            responsable_entrega,
            grupo_entrega,
            material_pet,
            cantidad_pet,
            material_carton,
            cantidad_carton,
            material_tapas,
            cantidad_tapas,
            material_vidrio,
            cantidad_vidrio,
            material_electrodomesticos,
            cantidad_electrodomesticos,
            material_papel,
            cantidad_papel,
            observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        redirigir_formulario('error', 'Error al preparar el guardado: ' . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssidididididids',
        $fecha_entrega,
        $responsable_entrega,
        $grupo_entrega,
        $material_pet,
        $cantidad_pet,
        $material_carton,
        $cantidad_carton,
        $material_tapas,
        $cantidad_tapas,
        $material_vidrio,
        $cantidad_vidrio,
        $material_electrodomesticos,
        $cantidad_electrodomesticos,
        $material_papel,
        $cantidad_papel,
        $observaciones
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        redirigir_formulario('ok', 'Registro de recolección guardado correctamente.');
    }

    $error_stmt = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    redirigir_formulario('error', 'Error al guardar la recolección: ' . $error_stmt);
}

/* =========================================
    SI LA ACCION NO EXISTE
========================================= */
redirigir("error", "Acción no reconocida.");
?>
