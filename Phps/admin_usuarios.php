<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/auth_admin.php';
require_roles_admin(['master', 'admin']);

$tipo = $_GET['tipo'] ?? '';
$mensaje = $_GET['mensaje'] ?? '';
$info = $_GET['info'] ?? '';

$admin_actual_id = (int)($_SESSION['admin_id'] ?? 0);
$admin_actual_rol = $_SESSION['admin_rol'] ?? '';

$busqueda_u = isset($_GET['b_u']) ? trim($_GET['b_u']) : '';
$sql_where_u = "";
if (!empty($busqueda_u)) {
    $busqueda_esc_u = mysqli_real_escape_string($conexion, $busqueda_u);
    $sql_where_u = " WHERE usuario LIKE '%$busqueda_esc_u%' ";
}

$sql_count_u = "SELECT COUNT(*) as total FROM admins" . $sql_where_u;
$resultado_count_u = mysqli_query($conexion, $sql_count_u);
$total_registros_u = (int)mysqli_fetch_assoc($resultado_count_u)['total'];

$por_pagina = 10;
$total_paginas_u = ceil($total_registros_u / $por_pagina);
if ($total_paginas_u == 0) $total_paginas_u = 1;

$pagina_u = isset($_GET['p_u']) ? (int)$_GET['p_u'] : 1;
if ($pagina_u < 1) $pagina_u = 1;
if ($pagina_u > $total_paginas_u) $pagina_u = $total_paginas_u;

$offset_u = ($pagina_u - 1) * $por_pagina;

$consulta = mysqli_query($conexion, "
    SELECT
        id_admin,
        usuario,
        rol,
        activo,
        debe_cambiar_password,
        ultimo_acceso,
        fecha_creacion
    FROM admins
    $sql_where_u
    ORDER BY
        FIELD(rol, 'master', 'admin', 'subadmin'),
        usuario ASC
    LIMIT $por_pagina OFFSET $offset_u
");

// Preparar URL base para la paginación conservando filtros (si los hubiera)
$params_get_u = $_GET;
unset($params_get_u['p_u']);
$query_filtros_u = http_build_query($params_get_u);
$url_base_u = "?";
if (!empty($query_filtros_u)) {
    $url_base_u .= $query_filtros_u . "&";
}

if (!$consulta) {
    die('Error al consultar admins: ' . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de usuarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Styles/style_admin_usuarios.css">
</head>
<body>
<div class="contenedor">

    <div class="acciones-superiores">
        <a href="admin_asignaciones.php"><button type="button" class="btn-secundario">Volver a asignaciones</button></a>
    </div>

    <h1>Administración de usuarios</h1>

    <?php if ($tipo === 'ok' && $mensaje !== ''): ?>
        <div class="mensaje ok"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($tipo === 'error' && $mensaje !== ''): ?>
        <div class="mensaje error"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($info !== ''): ?>
        <div class="mensaje info"><?php echo htmlspecialchars($info); ?></div>
    <?php endif; ?>

    <?php if ($admin_actual_rol === 'master' || $admin_actual_rol === 'admin'): ?>
    <div class="card">
        <h2>Crear usuario</h2>
        <form action="acciones_usuarios_admin.php" method="POST">
            <input type="hidden" name="accion" value="crear_usuario">

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label for="rol">Rol</label>
            <select id="rol" name="rol" required>
                <?php if ($admin_actual_rol === 'master'): ?>
                    <option value="admin">Admin</option>
                    <option value="subadmin">Sub-admin</option>
                <?php elseif ($admin_actual_rol === 'admin'): ?>
                    <option value="subadmin">Sub-admin</option>
                <?php endif; ?>
            </select>

            <label>
                <input type="checkbox" name="activo" checked>
                Activo
            </label>

            <button type="submit">Crear usuario</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="tabla">
        <h2>Usuarios registrados</h2>
        
        <form method="GET" class="form-busqueda">
            <input type="text" name="b_u" value="<?php echo htmlspecialchars($busqueda_u); ?>" placeholder="Buscar usuario...">
            <button type="submit">Buscar</button>
        </form>
        <br>

        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Debe cambiar contraseña</th>
                    <th>Último acceso</th>
                    <th>Fecha creación</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($consulta)): ?>
                    <?php
                        $es_master_fila = ($fila['rol'] === 'master');
                        $es_mi_cuenta = ((int)$fila['id_admin'] === $admin_actual_id);

                        $puede_editar = false;
                        $puede_eliminar = false;

                        if ($admin_actual_rol === 'master') {
                            $puede_editar = true;
                            $puede_eliminar = !$es_master_fila && !$es_mi_cuenta;
                        } elseif ($admin_actual_rol === 'admin') {
                            $puede_editar = !$es_master_fila;
                            $puede_eliminar = !$es_master_fila && !$es_mi_cuenta;
                        }
                    ?>
                    <tr>
                        <td><?php echo (int)$fila['id_admin']; ?></td>
                        <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
                        <td><?php echo htmlspecialchars(nombre_rol_bonito($fila['rol'])); ?></td>
                        <td><?php echo ((int)$fila['activo'] === 1) ? 'Sí' : 'No'; ?></td>
                        <td><?php echo ((int)$fila['debe_cambiar_password'] === 1) ? 'Sí' : 'No'; ?></td>
                        <td><?php echo $fila['ultimo_acceso'] ? htmlspecialchars($fila['ultimo_acceso']) : 'Sin acceso'; ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_creacion']); ?></td>

                        <td class="fila-form">
                            <?php if ($puede_editar): ?>
                                <form action="acciones_usuarios_admin.php" method="POST">
                                    <input type="hidden" name="accion" value="editar_usuario">
                                    <input type="hidden" name="id_admin" value="<?php echo (int)$fila['id_admin']; ?>">

                                    <label>Usuario</label>
                                    <input type="text" name="usuario" value="<?php echo htmlspecialchars($fila['usuario']); ?>" required>

                                    <?php if ($admin_actual_rol === 'master' && !$es_master_fila): ?>
                                        <label>Rol</label>
                                        <select name="rol" required>
                                            <option value="admin" <?php echo ($fila['rol'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="subadmin" <?php echo ($fila['rol'] === 'subadmin') ? 'selected' : ''; ?>>Sub-admin</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($fila['rol']); ?>">
                                    <?php endif; ?>

                                    <label>Nueva contraseña (opcional)</label>
                                    <input type="password" name="password" placeholder="Dejar vacía para no cambiar">

                                    <label>
                                        <input type="checkbox" name="activo" <?php echo ((int)$fila['activo'] === 1) ? 'checked' : ''; ?>>
                                        Activo
                                    </label>

                                    <label>
                                        <input type="checkbox" name="debe_cambiar_password" <?php echo ((int)$fila['debe_cambiar_password'] === 1) ? 'checked' : ''; ?>>
                                        Debe cambiar contraseña
                                    </label>

                                    <button type="submit">Actualizar</button>
                                </form>
                            <?php else: ?>
                                <span class="bloqueado">No permitido</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($puede_eliminar): ?>
                                <form action="acciones_usuarios_admin.php" method="POST" onsubmit="return confirm('¿Eliminar este usuario?');">
                                    <input type="hidden" name="accion" value="eliminar_usuario">
                                    <input type="hidden" name="id_admin" value="<?php echo (int)$fila['id_admin']; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <span class="bloqueado">No permitido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        
        <?php if ($total_paginas_u > 0): ?>
            <div class="paginacion">
                <?php if ($pagina_u > 1): ?>
                    <a href="<?= $url_base_u . "p_u=" . ($pagina_u - 1) ?>" class="page-link">Anterior &larr;</a>
                <?php endif; ?>

                <?php 
                for ($i = 1; $i <= $total_paginas_u; $i++): 
                    if ($i == 1 || $i == $total_paginas_u || abs($i - $pagina_u) < 2):
                ?>
                    <a href="<?= $url_base_u . "p_u=" . $i ?>" class="page-link <?= ($i == $pagina_u) ? 'active' : '' ?>"><?= $i ?></a>
                <?php 
                    elseif ($i == 2 && $pagina_u > 3): 
                        echo "<span class='page-dots'>...</span>";
                    elseif ($i == $total_paginas_u - 1 && $pagina_u < $total_paginas_u - 2):
                        echo "<span class='page-dots'>...</span>";
                    endif;
                endfor; 
                ?>

                <?php if ($pagina_u < $total_paginas_u): ?>
                    <a href="<?= $url_base_u . "p_u=" . ($pagina_u + 1) ?>" class="page-link">Siguiente &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
<footer style="text-align: center; padding: 20px; font-size: 0.85rem; color: #4a4a4a; margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1);">
    <p>&copy; Sistema desarrollado y donado con orgullo al CBTis No. 153 por los estudiantes Francisco Fuentes Capilla e Iván Amaro Tlalpa (2026).</p>
</footer>
</body>
</html>