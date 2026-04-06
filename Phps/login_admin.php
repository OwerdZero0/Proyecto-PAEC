<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/auth_admin.php';

$error = $_GET['error'] ?? '';
$info = $_GET['info'] ?? '';
$destino = $_GET['destino'] ?? 'admin_asignaciones.php';

$destinos_permitidos = ['admin_asignaciones.php', 'admin_usuarios.php', 'formulario.php'];
if (!in_array($destino, $destinos_permitidos, true)) {
    $destino = 'admin_asignaciones.php';
}

if (admin_logueado()) {
    $rol = $_SESSION['admin_rol'] ?? '';

    if ($destino === 'admin_usuarios.php' && !in_array($rol, ['master', 'admin'], true)) {
        header('Location: admin_asignaciones.php?error=' . urlencode('No tienes permisos para entrar a administración de usuarios.'));
        exit;
    }

    header('Location: ' . $destino);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login administrador</title>
    <link rel="stylesheet" href="../Styles/style_login_admin.css">
</head>
<body>
    <div class="login-box">
        <h1>Acceso administrador</h1>

        <?php if ($error !== ''): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($info !== ''): ?>
            <div class="mensaje info"><?php echo htmlspecialchars($info); ?></div>
        <?php endif; ?>

        <form action="validar_admin.php" method="POST">
            <input type="hidden" name="destino" value="<?php echo htmlspecialchars($destino); ?>">

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>

        <div class="boton-inicio-wrap">
            <a href="../index.html" class="boton-inicio-link">
                <div class="boton-inicio-svg">
                    <span class="boton-inicio-icono" aria-hidden="true">
                        <svg viewBox="0 0 576 512" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M575.8 255.5C575.8 272.3 562.1 286 545.3 286H448V456C448 486.9 422.9 512 392 512H312C294.3 512 280 497.7 280 480V384C280 366.3 265.7 352 248 352H200C182.3 352 168 366.3 168 384V480C168 497.7 153.7 512 136 512H56C25.1 512 0 486.9 0 456V286H30.7C13.9 286 .2 272.3 .2 255.5C.2 246.8 3.9 238.5 10.4 232.8L266.4 8.8C279.3 -2.9 296.7 -2.9 309.6 8.8L565.6 232.8C572.1 238.5 575.8 246.8 575.8 255.5Z" fill="white"/>
                        </svg>
                    </span>
                    <span class="boton-inicio-texto">Inicio</span>
                </div>
            </a>
        </div>
    </div>
</body>
</html>