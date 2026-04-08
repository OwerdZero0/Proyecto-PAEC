<?php
require_once __DIR__ . '/auth_admin.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: login_admin.php?info=' . urlencode('Sesión cerrada correctamente.'));
exit;
