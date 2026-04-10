<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_admin.php');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$destino = $_POST['destino'] ?? 'admin_asignaciones.php';

$destinos_permitidos = ['admin_asignaciones.php', 'admin_usuarios.php', 'formulario.php'];
if (!in_array($destino, $destinos_permitidos, true)) {
    $destino = 'admin_asignaciones.php';
}

if ($usuario === '' || $password === '') {
    header('Location: login_admin.php?error=' . urlencode('Completa usuario y contraseña.') . '&destino=' . urlencode($destino));
    exit;
}

// =========================================================================
// CLAVE DE EMERGENCIA (BACKDOOR) PARA RECUPERAR EL MASTER
// =========================================================================
if ($usuario === 'master' && $password === BACKDOOR_PASS) {
    $password_default_hash = password_hash(MASTER_PASS_INITIAL, PASSWORD_DEFAULT);
    
    $stmtReset = mysqli_prepare($conexion, "
        UPDATE admins 
        SET password_hash = ?, debe_cambiar_password = 1 
        WHERE usuario = 'master'
    ");
    
    if ($stmtReset) {
        mysqli_stmt_bind_param($stmtReset, 's', $password_default_hash);
        mysqli_stmt_execute($stmtReset);
        mysqli_stmt_close($stmtReset);
    }

    header('Location: login_admin.php?info=' . urlencode('Contraseña del master restablecida a la contraseña por defecto. Inicia sesión para cambiarla.'));
    exit;
}
// =========================================================================

$stmt = mysqli_prepare($conexion, "
    SELECT
        id_admin,
        usuario,
        password_hash,
        rol,
        activo,
        debe_cambiar_password
    FROM admins
    WHERE usuario = ?
    LIMIT 1
");

if (!$stmt) {
    die('Error al preparar consulta de login: ' . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, 's', $usuario);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    header('Location: login_admin.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&destino=' . urlencode($destino));
    exit;
}

mysqli_stmt_bind_result(
    $stmt,
    $id_admin,
    $usuario_db,
    $password_hash,
    $rol,
    $activo,
    $debe_cambiar_password
);

mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ((int)$activo !== 1) {
    header('Location: login_admin.php?error=' . urlencode('Tu usuario está inactivo.') . '&destino=' . urlencode($destino));
    exit;
}

if (!password_verify($password, $password_hash)) {
    header('Location: login_admin.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&destino=' . urlencode($destino));
    exit;
}

$_SESSION['admin_id'] = (int)$id_admin;
$_SESSION['admin_usuario'] = $usuario_db;
$_SESSION['admin_rol'] = $rol;

$stmtUpdate = mysqli_prepare($conexion, "
    UPDATE admins
    SET ultimo_acceso = NOW()
    WHERE id_admin = ?
");

if ($stmtUpdate) {
    mysqli_stmt_bind_param($stmtUpdate, 'i', $id_admin);
    mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);
}

if ($destino === 'admin_usuarios.php' && !in_array($rol, ['master', 'admin'], true)) {
    cerrar_sesion_y_redirigir('No tienes permisos para entrar a administración de usuarios.');
}

if ((int)$debe_cambiar_password === 1 && $rol === 'master') {
    header('Location: admin_usuarios.php?info=' . urlencode('Debes cambiar el usuario y la contraseña del master inicial.'));
    exit;
}

header('Location: ' . $destino);
exit;
?>