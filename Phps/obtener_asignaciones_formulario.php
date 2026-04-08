<?php

/* =========================================================
    SECCIÓN 1: VALIDAR SESIÓN
    ---------------------------------------------------------
    Este archivo solo debe responder si el usuario ya inició
    sesión. Si no, devuelve error 401 en formato JSON.
========================================================= */
/* Endpoint público para traer los maestros y grupos asignados */

/* =========================================================
    SECCIÓN 2: CONEXIÓN A LA BASE DE DATOS
    ---------------------------------------------------------
    Se establece la conexión a MySQL usando la misma base
    de datos de tu sistema.
========================================================= */
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion");

if (!$conexion) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión a la base de datos"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_set_charset($conexion, "utf8mb4");

/* =========================================================
    SECCIÓN 3: CONSULTA DE ASIGNACIONES
    ---------------------------------------------------------
    Aquí se unen las tablas:
    - asignaciones
    - maestros
    - grupos

    Esto permite obtener:
    - id del maestro
    - nombre del maestro
    - id del grupo
    - nombre del grupo
    - turno
    - ciclo escolar

    Solo se traen maestros y grupos activos.
========================================================= */
$sql = "
    SELECT
        a.id_asignacion,
        m.id_maestro,
        m.nombre_maestro,
        g.id_grupo,
        g.nombre_grupo,
        g.ciclo_escolar,
        g.turno
    FROM asignaciones a
    INNER JOIN maestros m ON m.id_maestro = a.id_maestro
    INNER JOIN grupos g ON g.id_grupo = a.id_grupo
    WHERE m.activo = 1
    AND g.activo = 1
    ORDER BY g.turno ASC, g.nombre_grupo ASC, m.nombre_maestro ASC
";

$resultado = mysqli_query($conexion, $sql);

/* =========================================================
    SECCIÓN 4: VALIDAR RESULTADO DE LA CONSULTA
    ---------------------------------------------------------
    Si algo falla en la consulta, se devuelve error 500.
========================================================= */
if (!$resultado) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al consultar las asignaciones",
        "detalle" => mysqli_error($conexion)
    ], JSON_UNESCAPED_UNICODE);
    mysqli_close($conexion);
    exit;
}

/* =========================================================
    SECCIÓN 5: PREPARAR RESPUESTA JSON
    ---------------------------------------------------------
    Se recorre el resultado y se arma un arreglo con los
    datos que el archivo JS necesita para llenar los selects.
========================================================= */
$asignaciones = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $asignaciones[] = [
        "id_asignacion" => (int)$fila["id_asignacion"],
        "id_maestro" => (int)$fila["id_maestro"],
        "nombre_maestro" => $fila["nombre_maestro"],
        "id_grupo" => (int)$fila["id_grupo"],
        "nombre_grupo" => $fila["nombre_grupo"],
        "ciclo_escolar" => $fila["ciclo_escolar"],
        "turno" => $fila["turno"]
    ];
}

/* =========================================================
    SECCIÓN 6: DEVOLVER JSON AL NAVEGADOR
    ---------------------------------------------------------
    Este JSON será consumido por fetch() desde JS.
========================================================= */
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "ok" => true,
    "asignaciones" => $asignaciones
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

/* =========================================================
    SECCIÓN 7: CERRAR CONEXIÓN
========================================================= */
mysqli_close($conexion);
?>