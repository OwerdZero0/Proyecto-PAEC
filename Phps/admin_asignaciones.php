<?php
require_once __DIR__ . '/auth_admin.php';
require_roles_admin(['master', 'admin', 'subadmin']);

/* =========================
   CREAR TABLAS SI NO EXISTEN
========================= */
require_once __DIR__ . '/crear_tablas_admin.php';

/* =========================
   CONEXION
========================= */
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion")
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
$maestros = mysqli_query($conexion, "
    SELECT *
    FROM maestros
    ORDER BY nombre_maestro ASC
");

/* =========================
   CONSULTAR GRUPOS
========================= */
$grupos = mysqli_query($conexion, "
    SELECT *
    FROM grupos
    ORDER BY turno ASC, ciclo_escolar ASC, nombre_grupo ASC
");

/* =========================
   CONSULTAR ASIGNACIONES
========================= */
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
    ORDER BY g.turno ASC, g.ciclo_escolar ASC, g.nombre_grupo ASC
");

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
    <link rel="stylesheet" href="../Styles/style_admin_asignaciones.css">
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

            <a href="cerrar_admin.php">
                <button type="button">Cerrar sesión</button>
            </a>
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
                <input type="text" id="ciclo_escolar" name="ciclo_escolar" placeholder="Ejemplo: 2025-2026" required>

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
    </div>

    <div class="tabla">
        <h2>Lista de grupos</h2>

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
    </div>

    <div class="tabla">
        <h2>Asignaciones actuales</h2>

        <input
            type="text"
            id="buscador_asignaciones"
            placeholder="Buscar maestro, grupo, ciclo o turno"
        >

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
    </div>

</div>

<br><br>

<div class="boton-inicio-wrap">
    <a href="../index.html" class="boton-inicio-link">
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
</body>
</html>
<?php
mysqli_close($conexion);
?>