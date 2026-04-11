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
            <div class="password-wrap">
                <input type="password" id="password" name="password" required>
                <button
                    type="button"
                    class="toggle-password"
                    id="togglePassword"
                    aria-label="Mostrar contraseña"
                    aria-pressed="false"
                    title="Mostrar contraseña"
                >
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <g class="eye-closed">
                            <path d="M3 3L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10.6 6.5C11.05 6.17 11.51 6 12 6c4.2 0 7.52 2.55 9 6-0.46 1.07-1.08 2.04-1.82 2.87" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.53 6.53C4.99 7.61 3.78 9.16 3 12c1.8 4.2 5.4 7 9 7 1.52 0 2.94-.31 4.2-.88" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                        <g class="eye-open">
                            <path d="M2 12C3.8 7.8 7.4 5 12 5s8.2 2.8 10 7c-1.8 4.2-5.4 7-10 7s-8.2-2.8-10-7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
                        </g>
                    </svg>
                </button>
            </div>

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

    <script src="../Scripts/script_login_admin.js"></script>
</body>
</html>
