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
    return mysqli_real_escape_string($conexion, trim($valor));
}

function redirigir($tipo, $mensaje) {
    $tipo = urlencode($tipo);
    $mensaje = urlencode($mensaje);
    header("Location: admin_asignaciones.php?tipo={$tipo}&mensaje={$mensaje}");
    exit;
}

/* =========================================
    VALIDAR METODO
========================================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("error", "Acceso no permitido.");
}

$accion = $_POST["accion"] ?? "";

if ($accion === "") {
    redirigir("error", "No se recibió ninguna acción.");
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
    SI LA ACCION NO EXISTE
========================================= */
redirigir("error", "Acción no reconocida.");
?>