<?php
ini_set('display_errors', 0);
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        <svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    </span>
                    <span class="boton-inicio-texto">Inicio</span>
                </div>
            </a>
        </div>
    </div>
    <footer style="text-align: center; padding: 20px; font-size: 0.85rem; color: #4a4a4a; margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1);">
        <p>&copy; Sistema desarrollado y donado con orgullo al CBTis No. 153 por los estudiantes Francisco Fuentes Capilla e Iván Amaro Tlalpa (2026).</p>
    </footer>
</body>
</html>