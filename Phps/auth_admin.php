<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/crear_tablas_admin.php';

function admin_logueado(): bool
{
    return isset($_SESSION['admin_id'], $_SESSION['admin_usuario'], $_SESSION['admin_rol']);
}

function cerrar_sesion_y_redirigir(string $mensaje = 'Tu sesión fue cerrada.'): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: login_admin.php?error=' . urlencode($mensaje));
    exit;
}

function require_login_admin(): void
{
    if (!admin_logueado()) {
        header('Location: login_admin.php');
        exit;
    }
}

function require_roles_admin(array $rolesPermitidos): void
{
    require_login_admin();

    $rol = $_SESSION['admin_rol'] ?? '';

    if (!in_array($rol, $rolesPermitidos, true)) {
        cerrar_sesion_y_redirigir('No tienes permisos para entrar a esa sección.');
    }
}

function es_master(): bool
{
    return admin_logueado() && ($_SESSION['admin_rol'] === 'master');
}

function es_admin(): bool
{
    return admin_logueado() && ($_SESSION['admin_rol'] === 'admin');
}

function es_subadmin(): bool
{
    return admin_logueado() && ($_SESSION['admin_rol'] === 'subadmin');
}

function es_admin_o_superior(): bool
{
    return admin_logueado() && in_array($_SESSION['admin_rol'], ['master', 'admin'], true);
}

function es_subadmin_o_superior(): bool
{
    return admin_logueado() && in_array($_SESSION['admin_rol'], ['master', 'admin', 'subadmin'], true);
}

function nombre_rol_bonito(string $rol): string
{
    switch ($rol) {
        case 'master':
            return 'Master';
        case 'admin':
            return 'Admin';
        case 'subadmin':
            return 'Sub-admin';
        default:
            return $rol;
    }
}
?>