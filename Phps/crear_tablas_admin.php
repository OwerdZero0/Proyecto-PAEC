<?php

/* =========================
   CONEXION INICIAL AL SERVIDOR
========================= */

$servidor = "localhost";
$usuario_bd = "root";
$password_bd = "root";
$nombre_bd = "baseRecoleccion";

$conexion = mysqli_connect($servidor, $usuario_bd, $password_bd);

if (!$conexion) {
    die("Error al conectar con MySQL: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");

/* =========================
   CREAR BASE DE DATOS SI NO EXISTE
========================= */

$sql_crear_bd = "CREATE DATABASE IF NOT EXISTS `$nombre_bd` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (!mysqli_query($conexion, $sql_crear_bd)) {
    die("Error al crear la base de datos: " . mysqli_error($conexion));
}

/* =========================
   SELECCIONAR BASE DE DATOS
========================= */

if (!mysqli_select_db($conexion, $nombre_bd)) {
    die("Error al seleccionar la base de datos: " . mysqli_error($conexion));
}

mysqli_set_charset($conexion, "utf8mb4");

/* =========================
   CREAR TABLA ADMINS
========================= */

$sql_tabla_admins = "
CREATE TABLE IF NOT EXISTS admins (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('master', 'admin', 'subadmin') NOT NULL DEFAULT 'subadmin',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_acceso DATETIME NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($conexion, $sql_tabla_admins)) {
    die("Error al crear la tabla admins: " . mysqli_error($conexion));
}

/* =========================
   CREAR TABLA MAESTROS
========================= */

$sql_tabla_maestros = "
CREATE TABLE IF NOT EXISTS maestros (
    id_maestro INT AUTO_INCREMENT PRIMARY KEY,
    nombre_maestro VARCHAR(120) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre_maestro (nombre_maestro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($conexion, $sql_tabla_maestros)) {
    die("Error al crear la tabla maestros: " . mysqli_error($conexion));
}

/* =========================
   CREAR TABLA GRUPOS
========================= */

$sql_tabla_grupos = "
CREATE TABLE IF NOT EXISTS grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(50) NOT NULL,
    ciclo_escolar VARCHAR(30) NOT NULL,
    turno ENUM('Matutino', 'Vespertino') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_grupo_ciclo_turno (nombre_grupo, ciclo_escolar, turno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($conexion, $sql_tabla_grupos)) {
    die("Error al crear la tabla grupos: " . mysqli_error($conexion));
}

/* =========================
   CREAR TABLA ASIGNACIONES
========================= */

$sql_tabla_asignaciones = "
CREATE TABLE IF NOT EXISTS asignaciones (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    id_maestro INT NOT NULL,
    id_grupo INT NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_asignacion_maestro (id_maestro),
    UNIQUE KEY uk_asignacion_grupo (id_grupo),
    CONSTRAINT fk_asignaciones_maestro
        FOREIGN KEY (id_maestro) REFERENCES maestros(id_maestro)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_asignaciones_grupo
        FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($conexion, $sql_tabla_asignaciones)) {
    die("Error al crear la tabla asignaciones: " . mysqli_error($conexion));
}

/* =========================
   CREAR TABLA RECOLECCION
========================= */

$sql_tabla_recoleccion = "
CREATE TABLE IF NOT EXISTS recoleccion (
    id_recoleccion INT AUTO_INCREMENT PRIMARY KEY,
    fecha_entrega DATE NOT NULL,
    responsable_entrega VARCHAR(80) NOT NULL,
    grupo_entrega VARCHAR(50) NOT NULL,
    material_pet TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_pet DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_carton TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_carton DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_tapas TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_tapas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_vidrio TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_vidrio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_electrodomesticos TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_electrodomesticos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_papel TINYINT(1) NOT NULL DEFAULT 0,
    cantidad_papel DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observaciones VARCHAR(250) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($conexion, $sql_tabla_recoleccion)) {
    die("Error al crear la tabla recoleccion: " . mysqli_error($conexion));
}

/* =========================
   VERIFICAR SI EXISTE UN MASTER
========================= */

$sql_verificar_master = "SELECT id_admin FROM admins WHERE rol = 'master' LIMIT 1";
$resultado_master = mysqli_query($conexion, $sql_verificar_master);

if (!$resultado_master) {
    die("Error al verificar el usuario master: " . mysqli_error($conexion));
}

/* =========================
   CREAR MASTER INICIAL SI NO EXISTE
========================= */

if (mysqli_num_rows($resultado_master) === 0) {
    $usuario_inicial = "master";
    $password_inicial = "Master123!";
    $hash_inicial = password_hash($password_inicial, PASSWORD_DEFAULT);
    $rol_inicial = "master";
    $activo_inicial = 1;
    $debe_cambiar_password_inicial = 1;

    $stmt_insert_master = mysqli_prepare($conexion, "
        INSERT INTO admins (
            usuario,
            password_hash,
            rol,
            activo,
            debe_cambiar_password
        ) VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt_insert_master) {
        die("Error al preparar inserción del master inicial: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param(
        $stmt_insert_master,
        "sssii",
        $usuario_inicial,
        $hash_inicial,
        $rol_inicial,
        $activo_inicial,
        $debe_cambiar_password_inicial
    );

    if (!mysqli_stmt_execute($stmt_insert_master)) {
        die("Error al crear el master inicial: " . mysqli_stmt_error($stmt_insert_master));
    }

    mysqli_stmt_close($stmt_insert_master);
}

mysqli_free_result($resultado_master);
?>