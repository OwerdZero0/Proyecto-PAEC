<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_admin.php';

/* =========================================================
   SECCIÓN 1: VALIDAR SESIÓN Y ROL
   ---------------------------------------------------------
   Este archivo solo puede ser usado por usuarios logueados
   con rol master o admin.
========================================================= */
require_roles_admin(['master', 'admin']);

/* =========================================================
   SECCIÓN 2: CONEXIÓN A LA BASE DE DATOS
   ---------------------------------------------------------
   Si auth_admin.php ya dejó disponible $conexion, se usa.
   Si no existe, se crea una conexión aquí como respaldo.
========================================================= */
if (!isset($conexion) || !$conexion) {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)
        or die("Error en la conexión: " . mysqli_connect_error());

    mysqli_set_charset($conexion, "utf8mb4");
}

/* =========================================================
   SECCIÓN 3: FUNCIONES AUXILIARES
   ---------------------------------------------------------
   Estas funciones ayudan a:
   - limpiar texto recibido por POST
   - redirigir siempre a admin_usuarios.php
========================================================= */
function limpiar($conexion, $valor) {
    return mysqli_real_escape_string($conexion, trim((string)$valor));
}

function redirigir_usuarios($tipo, $mensaje) {
    $tipo = urlencode($tipo);
    $mensaje = urlencode($mensaje);
    header("Location: admin_usuarios.php?tipo={$tipo}&mensaje={$mensaje}");
    exit;
}

/* =========================================================
   SECCIÓN 4: VALIDAR MÉTODO
   ---------------------------------------------------------
   Solo se aceptan peticiones POST porque este archivo
   procesa formularios.
========================================================= */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir_usuarios("error", "Acceso no permitido.");
}

/* =========================================================
   SECCIÓN 5: OBTENER DATOS DE SESIÓN Y ACCIÓN
   ---------------------------------------------------------
   Se identifica:
   - el usuario actual
   - su rol
   - la acción enviada desde el formulario
========================================================= */
$admin_actual_id = (int)($_SESSION['admin_id'] ?? 0);
$admin_actual_rol = trim((string)($_SESSION['admin_rol'] ?? ''));
$accion = trim((string)($_POST['accion'] ?? ''));

if ($accion === '') {
    redirigir_usuarios("error", "No se recibió ninguna acción.");
}

/* =========================================================
   SECCIÓN 6: CREAR USUARIO
   ---------------------------------------------------------
   Reglas:
   - master puede crear admin y subadmin
   - admin solo puede crear subadmin
========================================================= */
if ($accion === 'crear_usuario') {

    $usuario = limpiar($conexion, $_POST['usuario'] ?? '');
    $password = trim((string)($_POST['password'] ?? ''));
    $rol = trim((string)($_POST['rol'] ?? ''));
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($usuario === '' || $password === '' || $rol === '') {
        redirigir_usuarios("error", "Todos los campos son obligatorios.");
    }

    /* -----------------------------------------------------
       VALIDAR ROL SEGÚN QUIÉN CREA
    ----------------------------------------------------- */
    if ($admin_actual_rol === 'master') {
        if ($rol !== 'admin' && $rol !== 'subadmin') {
            redirigir_usuarios("error", "Rol inválido.");
        }
    } elseif ($admin_actual_rol === 'admin') {
        if ($rol !== 'subadmin') {
            redirigir_usuarios("error", "Solo puedes crear usuarios tipo sub-admin.");
        }
    } else {
        redirigir_usuarios("error", "No tienes permiso para crear usuarios.");
    }

    if (strlen($usuario) > 50) {
        redirigir_usuarios("error", "El nombre de usuario es demasiado largo.");
    }

    $sql_verificar = "SELECT id_admin FROM admins WHERE usuario = '$usuario' LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir_usuarios("error", "Ese usuario ya existe.");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $debe_cambiar_password = 1;

    $stmt = mysqli_prepare($conexion, "
        INSERT INTO admins (
            usuario,
            password_hash,
            rol,
            activo,
            debe_cambiar_password
        ) VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        redirigir_usuarios("error", "Error al preparar la creación del usuario.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssii",
        $usuario,
        $password_hash,
        $rol,
        $activo,
        $debe_cambiar_password
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        redirigir_usuarios("ok", "Usuario creado correctamente.");
    }

    $error_stmt = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    redirigir_usuarios("error", "Error al crear usuario: " . $error_stmt);
}

/* =========================================================
   SECCIÓN 7: EDITAR USUARIO
   ---------------------------------------------------------
   Reglas:
   - master puede editar a cualquiera
   - admin no puede editar al master
   - se puede cambiar:
     * usuario
     * rol (solo master y no sobre el master)
     * contraseña
     * activo
     * debe_cambiar_password
========================================================= */
if ($accion === 'editar_usuario') {
    $id_admin = (int)($_POST['id_admin'] ?? 0);
    $usuario = limpiar($conexion, $_POST['usuario'] ?? '');
    $rol_post = trim((string)($_POST['rol'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));
    $activo = isset($_POST['activo']) ? 1 : 0;
    $debe_cambiar_password = isset($_POST['debe_cambiar_password']) ? 1 : 0;

    if ($id_admin <= 0 || $usuario === '') {
        redirigir_usuarios("error", "Datos inválidos para editar usuario.");
    }

    if (strlen($usuario) > 50) {
        redirigir_usuarios("error", "El nombre de usuario es demasiado largo.");
    }

    /* -----------------------------------------------------
       Consultar el usuario objetivo para conocer su rol
    ----------------------------------------------------- */
    $sql_objetivo = "SELECT id_admin, usuario, rol FROM admins WHERE id_admin = $id_admin LIMIT 1";
    $resultado_objetivo = mysqli_query($conexion, $sql_objetivo);

    if (!$resultado_objetivo || mysqli_num_rows($resultado_objetivo) === 0) {
        redirigir_usuarios("error", "El usuario a editar no existe.");
    }

    $usuario_objetivo = mysqli_fetch_assoc($resultado_objetivo);
    $rol_actual_objetivo = $usuario_objetivo['rol'];
    $es_master_objetivo = ($rol_actual_objetivo === 'master');
    $es_mi_cuenta = ($id_admin === $admin_actual_id);

    /* -----------------------------------------------------
       Validar permisos
    ----------------------------------------------------- */
    if ($admin_actual_rol === 'admin' && $es_master_objetivo) {
        redirigir_usuarios("error", "No tienes permiso para editar al usuario master.");
    }

    if ($admin_actual_rol !== 'master' && $admin_actual_rol !== 'admin') {
        redirigir_usuarios("error", "No tienes permiso para editar usuarios.");
    }

    /* -----------------------------------------------------
       Determinar el rol final
       - Si el objetivo es master, debe seguir siendo master
       - Solo master puede cambiar roles de otros usuarios
    ----------------------------------------------------- */
    $rol_final = $rol_actual_objetivo;

    if ($es_master_objetivo) {
        $rol_final = 'master';
    } else {
        if ($admin_actual_rol === 'master') {
            if ($rol_post !== 'admin' && $rol_post !== 'subadmin') {
                redirigir_usuarios("error", "Rol inválido.");
            }
            $rol_final = $rol_post;
        } else {
            $rol_final = $rol_actual_objetivo;
        }
    }

    /* -----------------------------------------------------
       Evitar usuario duplicado
    ----------------------------------------------------- */
    $sql_verificar = "SELECT id_admin
                      FROM admins
                      WHERE usuario = '$usuario'
                      AND id_admin <> $id_admin
                      LIMIT 1";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        redirigir_usuarios("error", "Ya existe otro usuario con ese nombre.");
    }

    /* -----------------------------------------------------
       Regla extra:
       evitar que el master se desactive a sí mismo
    ----------------------------------------------------- */
    if ($es_mi_cuenta && $admin_actual_rol === 'master' && $activo === 0) {
        redirigir_usuarios("error", "El usuario master no puede desactivarse a sí mismo.");
    }

    /* -----------------------------------------------------
       Construir actualización
       Si se escribió nueva contraseña, se actualiza.
       Si no, se mantiene la actual.
    ----------------------------------------------------- */
    if ($password !== '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conexion, "
            UPDATE admins
            SET usuario = ?,
                password_hash = ?,
                rol = ?,
                activo = ?,
                debe_cambiar_password = ?
            WHERE id_admin = ?
        ");

        if (!$stmt) {
            redirigir_usuarios("error", "Error al preparar la actualización del usuario.");
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sssiii",
            $usuario,
            $password_hash,
            $rol_final,
            $activo,
            $debe_cambiar_password,
            $id_admin
        );
    } else {
        $stmt = mysqli_prepare($conexion, "
            UPDATE admins
            SET usuario = ?,
                rol = ?,
                activo = ?,
                debe_cambiar_password = ?
            WHERE id_admin = ?
        ");

        if (!$stmt) {
            redirigir_usuarios("error", "Error al preparar la actualización del usuario.");
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ssiii",
            $usuario,
            $rol_final,
            $activo,
            $debe_cambiar_password,
            $id_admin
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        if ($password !== '' && $es_master_objetivo && $es_mi_cuenta) {
            redirigir_usuarios("ok", "La contraseña del master se actualizó correctamente.");
        }

        redirigir_usuarios("ok", "Usuario actualizado correctamente.");
    }

    $error_stmt = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    redirigir_usuarios("error", "Error al actualizar usuario: " . $error_stmt);
}

/* =========================================================
   SECCIÓN 8: ELIMINAR USUARIO
   ---------------------------------------------------------
   Reglas:
   - master puede eliminar admin y subadmin
   - admin puede eliminar admin/subadmin, pero no master
   - nadie puede eliminar su propia cuenta desde aquí
========================================================= */
if ($accion === 'eliminar_usuario') {
    $id_admin = (int)($_POST['id_admin'] ?? 0);

    if ($id_admin <= 0) {
        redirigir_usuarios("error", "ID de usuario inválido.");
    }

    $sql_objetivo = "SELECT id_admin, rol, usuario FROM admins WHERE id_admin = $id_admin LIMIT 1";
    $resultado_objetivo = mysqli_query($conexion, $sql_objetivo);

    if (!$resultado_objetivo || mysqli_num_rows($resultado_objetivo) === 0) {
        redirigir_usuarios("error", "El usuario a eliminar no existe.");
    }

    $usuario_objetivo = mysqli_fetch_assoc($resultado_objetivo);
    $rol_objetivo = $usuario_objetivo['rol'];
    $es_master_objetivo = ($rol_objetivo === 'master');
    $es_mi_cuenta = ($id_admin === $admin_actual_id);

    if ($es_mi_cuenta) {
        redirigir_usuarios("error", "No puedes eliminar tu propia cuenta.");
    }

    if ($es_master_objetivo) {
        redirigir_usuarios("error", "No se puede eliminar al usuario master.");
    }

    if ($admin_actual_rol !== 'master' && $admin_actual_rol !== 'admin') {
        redirigir_usuarios("error", "No tienes permiso para eliminar usuarios.");
    }

    $sql_eliminar = "DELETE FROM admins WHERE id_admin = $id_admin LIMIT 1";

    if (mysqli_query($conexion, $sql_eliminar)) {
        redirigir_usuarios("ok", "Usuario eliminado correctamente.");
    } else {
        redirigir_usuarios("error", "Error al eliminar usuario: " . mysqli_error($conexion));
    }
}

/* =========================================================
   SECCIÓN 9: ACCIÓN NO RECONOCIDA
========================================================= */
redirigir_usuarios("error", "Acción no reconocida.");
?>