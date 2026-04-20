<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_admin.php';
require_roles_admin(['master', 'admin', 'subadmin']);

/* =========================
    CREAR TABLAS SI NO EXISTEN
========================= */
require_once __DIR__ . '/crear_tablas_admin.php';

/* =========================
    CONEXION
========================= */
$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)
    or die("Error en la conexión: " . mysqli_connect_error());

mysqli_set_charset($conexion, "utf8mb4");

/* =========================
    DATOS DE SESION ACTUAL
========================= */
$admin_actual_nombre = $_SESSION['admin_usuario'] ?? 'Administrador';
$admin_actual_rol = $_SESSION['admin_rol'] ?? '';

/* =========================
    MENSAJES
========================= */
$tipo_mensaje = $_GET["tipo"] ?? "";
$mensaje = $_GET["mensaje"] ?? "";

if ($mensaje === "" && isset($_GET["error"])) {
    $tipo_mensaje = "error";
    $mensaje = $_GET["error"];
}

if ($mensaje === "" && isset($_GET["info"])) {
    $tipo_mensaje = "ok";
    $mensaje = $_GET["info"];
}

/* =========================
    CONSULTAR MAESTROS
========================= */
$busqueda_m = isset($_GET['b_m']) ? trim($_GET['b_m']) : '';
$sql_where_m = "";
if (!empty($busqueda_m)) {
    $busqueda_esc_m = mysqli_real_escape_string($conexion, $busqueda_m);
    $sql_where_m = " WHERE nombre_maestro LIKE '%$busqueda_esc_m%' ";
}
$sql_count_m = "SELECT COUNT(*) as total FROM maestros" . $sql_where_m;
$resultado_count_m = mysqli_query($conexion, $sql_count_m);
$total_registros_m = (int)mysqli_fetch_assoc($resultado_count_m)['total'];

$por_pagina = 10;
$total_paginas_m = ceil($total_registros_m / $por_pagina);
if ($total_paginas_m == 0) $total_paginas_m = 1;

$pagina_m = isset($_GET['p_m']) ? (int)$_GET['p_m'] : 1;
if ($pagina_m < 1) $pagina_m = 1;
if ($pagina_m > $total_paginas_m) $pagina_m = $total_paginas_m;
$offset_m = ($pagina_m - 1) * $por_pagina;

$maestros = mysqli_query($conexion, "
    SELECT *
    FROM maestros
    $sql_where_m
    ORDER BY nombre_maestro ASC
    LIMIT $por_pagina OFFSET $offset_m
");

/* =========================
    CONSULTAR GRUPOS
========================= */
$busqueda_g = isset($_GET['b_g']) ? trim($_GET['b_g']) : '';
$sql_where_g = "";
if (!empty($busqueda_g)) {
    $busqueda_esc_g = mysqli_real_escape_string($conexion, $busqueda_g);
    $sql_where_g = " WHERE nombre_grupo LIKE '%$busqueda_esc_g%' OR ciclo_escolar LIKE '%$busqueda_esc_g%' OR turno LIKE '%$busqueda_esc_g%' ";
}
$sql_count_g = "SELECT COUNT(*) as total FROM grupos" . $sql_where_g;
$resultado_count_g = mysqli_query($conexion, $sql_count_g);
$total_registros_g = (int)mysqli_fetch_assoc($resultado_count_g)['total'];

$total_paginas_g = ceil($total_registros_g / $por_pagina);
if ($total_paginas_g == 0) $total_paginas_g = 1;

$pagina_g = isset($_GET['p_g']) ? (int)$_GET['p_g'] : 1;
if ($pagina_g < 1) $pagina_g = 1;
if ($pagina_g > $total_paginas_g) $pagina_g = $total_paginas_g;
$offset_g = ($pagina_g - 1) * $por_pagina;

$grupos = mysqli_query($conexion, "
    SELECT *
    FROM grupos
    $sql_where_g
    ORDER BY turno ASC, ciclo_escolar ASC, nombre_grupo ASC
    LIMIT $por_pagina OFFSET $offset_g
");

/* =========================
    CONSULTAR ASIGNACIONES
========================= */
$busqueda_a = isset($_GET['b_a']) ? trim($_GET['b_a']) : '';
$sql_where_a = "";
if (!empty($busqueda_a)) {
    $busqueda_esc_a = mysqli_real_escape_string($conexion, $busqueda_a);
    $sql_where_a = " WHERE m.nombre_maestro LIKE '%$busqueda_esc_a%' OR g.nombre_grupo LIKE '%$busqueda_esc_a%' OR g.ciclo_escolar LIKE '%$busqueda_esc_a%' OR g.turno LIKE '%$busqueda_esc_a%' ";
}
$sql_count_a = "SELECT COUNT(*) as total FROM asignaciones a INNER JOIN maestros m ON m.id_maestro = a.id_maestro INNER JOIN grupos g ON g.id_grupo = a.id_grupo" . $sql_where_a;
$resultado_count_a = mysqli_query($conexion, $sql_count_a);
$total_registros_a = (int)mysqli_fetch_assoc($resultado_count_a)['total'];

$total_paginas_a = ceil($total_registros_a / $por_pagina);
if ($total_paginas_a == 0) $total_paginas_a = 1;

$pagina_a = isset($_GET['p_a']) ? (int)$_GET['p_a'] : 1;
if ($pagina_a < 1) $pagina_a = 1;
if ($pagina_a > $total_paginas_a) $pagina_a = $total_paginas_a;
$offset_a = ($pagina_a - 1) * $por_pagina;

$asignaciones = mysqli_query($conexion, "
    SELECT
        a.id_asignacion,
        m.id_maestro,
        m.nombre_maestro,
        m.activo AS maestro_activo,
        g.id_grupo,
        g.nombre_grupo,
        g.ciclo_escolar,
        g.turno,
        g.activo AS grupo_activo
    FROM asignaciones a
    INNER JOIN maestros m ON m.id_maestro = a.id_maestro
    INNER JOIN grupos g ON g.id_grupo = a.id_grupo
    $sql_where_a
    ORDER BY g.turno ASC, g.ciclo_escolar ASC, g.nombre_grupo ASC
    LIMIT $por_pagina OFFSET $offset_a
");

function render_paginacion($total_paginas, $pagina_actual, $param_name) {
    if ($total_paginas <= 0) return;

    $params = $_GET;
    unset($params[$param_name]);
    $query = http_build_query($params);
    $url_base = "?" . ($query ? $query . "&" : "");

    echo '<div class="paginacion">';
    if ($pagina_actual > 1) {
        echo '<a href="' . htmlspecialchars($url_base . $param_name . '=' . ($pagina_actual - 1)) . '" class="page-link">Anterior &larr;</a>';
    }

    for ($i = 1; $i <= $total_paginas; $i++) {
        if ($i == 1 || $i == $total_paginas || abs($i - $pagina_actual) < 2) {
            $clase = ($i == $pagina_actual) ? 'active' : '';
            echo '<a href="' . htmlspecialchars($url_base . $param_name . '=' . $i) . '" class="page-link ' . $clase . '">' . $i . '</a>';
        } elseif ($i == 2 && $pagina_actual > 3) {
            echo "<span class='page-dots'>...</span>";
        } elseif ($i == $total_paginas - 1 && $pagina_actual < $total_paginas - 2) {
            echo "<span class='page-dots'>...</span>";
        }
    }

    if ($pagina_actual < $total_paginas) {
        echo '<a href="' . htmlspecialchars($url_base . $param_name . '=' . ($pagina_actual + 1)) . '" class="page-link">Siguiente &rarr;</a>';
    }
    echo '</div>';
}

/* =========================
    MAESTROS DISPONIBLES
========================= */
$maestros_disponibles = mysqli_query($conexion, "
    SELECT *
    FROM maestros
    WHERE activo = 1
    AND id_maestro NOT IN (
        SELECT id_maestro FROM asignaciones
    )
    ORDER BY nombre_maestro ASC
");

/* =========================
    GRUPOS DISPONIBLES
========================= */
$grupos_disponibles = mysqli_query($conexion, "
    SELECT *
    FROM grupos
    WHERE activo = 1
    AND id_grupo NOT IN (
        SELECT id_grupo FROM asignaciones
    )
    ORDER BY turno ASC, ciclo_escolar ASC, nombre_grupo ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Styles/style_admin_asignaciones.css?v=2">
</head>
<body>

<div class="contenedor">

    <div class="barra-sesion">
        <div class="barra-sesion-info">
            Sesión: <?php echo htmlspecialchars($admin_actual_nombre); ?> |
            Rol: <?php echo htmlspecialchars(nombre_rol_bonito($admin_actual_rol)); ?>
        </div>

        <div class="barra-sesion-botones">
            <?php if (es_admin_o_superior()): ?>
                <a href="admin_usuarios.php">
                    <button type="button">Administrar usuarios</button>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <h1>Panel administrador de maestros, grupos y asignaciones</h1>

    <?php if ($mensaje !== ""): ?>
        <div class="<?php echo ($tipo_mensaje === 'ok') ? 'mensaje-ok' : 'mensaje-error'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="grid-admin">

        <div class="card">
            <h2>Agregar maestro</h2>

            <form action="acciones_admin.php" method="POST">
                <input type="hidden" name="accion" value="agregar_maestro">

                <label for="nombre_maestro">Nombre del maestro</label>
                <input type="text" id="nombre_maestro" name="nombre_maestro" required>

                <button type="submit">Guardar maestro</button>
            </form>
        </div>

        <div class="card">
            <h2>Agregar grupo</h2>

            <form action="acciones_admin.php" method="POST">
                <input type="hidden" name="accion" value="agregar_grupo">

                <label for="nombre_grupo">Nombre del grupo</label>
                <input type="text" id="nombre_grupo" name="nombre_grupo" placeholder="Ejemplo: 1AMC" required>

                <label for="ciclo_escolar">Ciclo escolar</label>
                <input type="text" id="ciclo_escolar" name="ciclo_escolar" placeholder="Ejemplo: Febrero 2026 - Julio 2026" required>

                <label for="turno">Turno</label>
                <select id="turno" name="turno" required>
                    <option value="">Selecciona una opción</option>
                    <option value="Matutino">Matutino</option>
                    <option value="Vespertino">Vespertino</option>
                </select>

                <button type="submit">Guardar grupo</button>
            </form>
        </div>

        <div class="card">
            <h2>Asignar maestro a grupo</h2>

            <form action="acciones_admin.php" method="POST">
                <input type="hidden" name="accion" value="agregar_asignacion">

                <label for="id_maestro">Maestro disponible</label>
                <select id="id_maestro" name="id_maestro" required>
                    <option value="">Selecciona un maestro</option>
                    <?php while ($m = mysqli_fetch_assoc($maestros_disponibles)): ?>
                        <option value="<?php echo $m["id_maestro"]; ?>">
                            <?php echo htmlspecialchars($m["nombre_maestro"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="id_grupo">Grupo disponible</label>
                <select id="id_grupo" name="id_grupo" required>
                    <option value="">Selecciona un grupo</option>
                    <?php while ($g = mysqli_fetch_assoc($grupos_disponibles)): ?>
                        <option value="<?php echo $g["id_grupo"]; ?>">
                            <?php
                            echo htmlspecialchars(
                                $g["nombre_grupo"] . " | " .
                                $g["ciclo_escolar"] . " | " .
                                $g["turno"]
                            );
                            ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Guardar asignación</button>
            </form>
        </div>

    </div>

    <div class="tabla">
        <h2>Lista de maestros</h2>
        
        <form method="GET" class="form-busqueda">
            <input type="text" name="b_m" value="<?php echo htmlspecialchars($busqueda_m); ?>" placeholder="Buscar maestro...">
            <button type="submit">Buscar</button>
        </form>

        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Activo</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php mysqli_data_seek($maestros, 0); ?>
                <?php while ($maestro = mysqli_fetch_assoc($maestros)): ?>
                    <tr>
                        <td><?php echo $maestro["id_maestro"]; ?></td>
                        <td><?php echo htmlspecialchars($maestro["nombre_maestro"]); ?></td>
                        <td><?php echo ((int)$maestro["activo"] === 1) ? "Sí" : "No"; ?></td>
                        <td>
                            <form action="acciones_admin.php" method="POST">
                                <input type="hidden" name="accion" value="editar_maestro">
                                <input type="hidden" name="id_maestro" value="<?php echo $maestro["id_maestro"]; ?>">

                                <label>Nombre</label>
                                <input type="text" name="nombre_maestro" value="<?php echo htmlspecialchars($maestro["nombre_maestro"]); ?>" required>

                                <label class="fila-check">
                                    <input type="checkbox" name="activo" <?php echo ((int)$maestro["activo"] === 1) ? "checked" : ""; ?>>
                                    Activo
                                </label>

                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <form action="acciones_admin.php" method="POST" class="form-eliminar" data-tipo="maestro">
                                <input type="hidden" name="accion" value="eliminar_maestro">
                                <input type="hidden" name="id_maestro" value="<?php echo $maestro["id_maestro"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        
        <?php render_paginacion($total_paginas_m, $pagina_m, 'p_m'); ?>
    </div>

    <div class="tabla">
        <h2>Lista de grupos</h2>

        <form method="GET" class="form-busqueda">
            <input type="text" name="b_g" value="<?php echo htmlspecialchars($busqueda_g); ?>" placeholder="Buscar grupo, ciclo o turno...">
            <button type="submit">Buscar</button>
        </form>

        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Grupo</th>
                    <th>Ciclo escolar</th>
                    <th>Turno</th>
                    <th>Activo</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php mysqli_data_seek($grupos, 0); ?>
                <?php while ($grupo = mysqli_fetch_assoc($grupos)): ?>
                    <tr>
                        <td><?php echo $grupo["id_grupo"]; ?></td>
                        <td><?php echo htmlspecialchars($grupo["nombre_grupo"]); ?></td>
                        <td><?php echo htmlspecialchars($grupo["ciclo_escolar"]); ?></td>
                        <td><?php echo htmlspecialchars($grupo["turno"]); ?></td>
                        <td><?php echo ((int)$grupo["activo"] === 1) ? "Sí" : "No"; ?></td>
                        <td>
                            <form action="acciones_admin.php" method="POST">
                                <input type="hidden" name="accion" value="editar_grupo">
                                <input type="hidden" name="id_grupo" value="<?php echo $grupo["id_grupo"]; ?>">

                                <label>Nombre del grupo</label>
                                <input type="text" name="nombre_grupo" value="<?php echo htmlspecialchars($grupo["nombre_grupo"]); ?>" required>

                                <label>Ciclo escolar</label>
                                <input type="text" name="ciclo_escolar" value="<?php echo htmlspecialchars($grupo["ciclo_escolar"]); ?>" required>

                                <label>Turno</label>
                                <select name="turno" required>
                                    <option value="Matutino" <?php echo ($grupo["turno"] === "Matutino") ? "selected" : ""; ?>>Matutino</option>
                                    <option value="Vespertino" <?php echo ($grupo["turno"] === "Vespertino") ? "selected" : ""; ?>>Vespertino</option>
                                </select>

                                <label class="fila-check">
                                    <input type="checkbox" name="activo" <?php echo ((int)$grupo["activo"] === 1) ? "checked" : ""; ?>>
                                    Activo
                                </label>

                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <form action="acciones_admin.php" method="POST" class="form-eliminar" data-tipo="grupo">
                                <input type="hidden" name="accion" value="eliminar_grupo">
                                <input type="hidden" name="id_grupo" value="<?php echo $grupo["id_grupo"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        
        <?php render_paginacion($total_paginas_g, $pagina_g, 'p_g'); ?>
    </div>

    <div class="tabla">
        <h2>Asignaciones actuales</h2>

        <form method="GET" class="form-busqueda">
            <input type="text" name="b_a" value="<?php echo htmlspecialchars($busqueda_a); ?>" placeholder="Buscar maestro, grupo, ciclo o turno...">
            <button type="submit">Buscar</button>
        </form>

        <div class="tabla-scroll">
        <table class="tabla-asignaciones">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Maestro</th>
                    <th>Grupo</th>
                    <th>Ciclo escolar</th>
                    <th>Turno</th>
                    <th>Editar relación</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($asig = mysqli_fetch_assoc($asignaciones)): ?>
                    <tr>
                        <td><?php echo $asig["id_asignacion"]; ?></td>
                        <td><?php echo htmlspecialchars($asig["nombre_maestro"]); ?></td>
                        <td><?php echo htmlspecialchars($asig["nombre_grupo"]); ?></td>
                        <td><?php echo htmlspecialchars($asig["ciclo_escolar"]); ?></td>
                        <td><?php echo htmlspecialchars($asig["turno"]); ?></td>
                        <td>
                            <form action="acciones_admin.php" method="POST">
                                <input type="hidden" name="accion" value="editar_asignacion">
                                <input type="hidden" name="id_asignacion" value="<?php echo $asig["id_asignacion"]; ?>">

                                <label>Maestro</label>
                                <select name="id_maestro" required>
                                    <?php
                                    $id_asignacion_actual = (int)$asig['id_asignacion'];
                                    $id_maestro_actual = (int)$asig['id_maestro'];

                                    $lista_maestros_editar = mysqli_query($conexion, "
                                        SELECT *
                                        FROM maestros
                                        WHERE activo = 1
                                        AND (
                                            id_maestro = $id_maestro_actual
                                            OR id_maestro NOT IN (
                                                SELECT id_maestro
                                                FROM asignaciones
                                                WHERE id_asignacion <> $id_asignacion_actual
                                            )
                                        )
                                        ORDER BY nombre_maestro ASC
                                    ");
                                    while ($mEdit = mysqli_fetch_assoc($lista_maestros_editar)):
                                    ?>
                                        <option value="<?php echo $mEdit["id_maestro"]; ?>"
                                            <?php echo ((int)$mEdit["id_maestro"] === $id_maestro_actual) ? "selected" : ""; ?>>
                                            <?php echo htmlspecialchars($mEdit["nombre_maestro"]); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <label>Grupo</label>
                                <select name="id_grupo" required>
                                    <?php
                                    $id_grupo_actual = (int)$asig['id_grupo'];

                                    $lista_grupos_editar = mysqli_query($conexion, "
                                        SELECT *
                                        FROM grupos
                                        WHERE activo = 1
                                        AND (
                                            id_grupo = $id_grupo_actual
                                            OR id_grupo NOT IN (
                                                SELECT id_grupo
                                                FROM asignaciones
                                                WHERE id_asignacion <> $id_asignacion_actual
                                            )
                                        )
                                        ORDER BY turno ASC, ciclo_escolar ASC, nombre_grupo ASC
                                    ");
                                    while ($gEdit = mysqli_fetch_assoc($lista_grupos_editar)):
                                    ?>
                                        <option value="<?php echo $gEdit["id_grupo"]; ?>"
                                            <?php echo ((int)$gEdit["id_grupo"] === $id_grupo_actual) ? "selected" : ""; ?>>
                                            <?php
                                            echo htmlspecialchars(
                                                $gEdit["nombre_grupo"] . " | " .
                                                $gEdit["ciclo_escolar"] . " | " .
                                                $gEdit["turno"]
                                            );
                                            ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <form action="acciones_admin.php" method="POST" class="form-eliminar" data-tipo="asignación">
                                <input type="hidden" name="accion" value="eliminar_asignacion">
                                <input type="hidden" name="id_asignacion" value="<?php echo $asig["id_asignacion"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        
        <?php render_paginacion($total_paginas_a, $pagina_a, 'p_a'); ?>
    </div>

</div>

<br><br>

<div class="boton-inicio-wrap">
    <a href="cerrar_admin.php" class="boton-inicio-link">
        <div class="boton-inicio-svg">
            <span class="boton-inicio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
            </span>
            <span class="boton-inicio-texto">Inicio</span>
        </div>
    </a>
</div>

<script src="../Scripts/admin_asignaciones.js"></script>
<footer style="text-align: center; padding: 20px; font-size: 0.85rem; color: #4a4a4a; margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1);">
    <p>&copy; Sistema desarrollado y donado con orgullo al CBTis No. 153 por los estudiantes Francisco Fuentes Capilla e Iván Amaro Tlalpa (2026).</p>
</footer>
</body>
</html>
<?php
mysqli_close($conexion);
?>