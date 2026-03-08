<?php
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion")
    or die("Problemas con la conexión: " . mysqli_connect_error());

mysqli_set_charset($conexion, "utf8mb4");

/*
TABLA 1: maestros
Guarda solo la información de los maestros
*/
$sql_maestros = "CREATE TABLE IF NOT EXISTS maestros (
    id_maestro INT AUTO_INCREMENT PRIMARY KEY,
    nombre_maestro VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

/*
TABLA 2: grupos
Guarda los grupos con su ciclo escolar y turno
*/
$sql_grupos = "CREATE TABLE IF NOT EXISTS grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL,
    ciclo_escolar VARCHAR(20) NOT NULL,
    turno ENUM('Matutino', 'Vespertino') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

/*
TABLA 3: asignaciones
Une a cada maestro con un grupo
Solo las asignaciones válidas deberán mostrarse en el formulario
*/
$sql_asignaciones = "CREATE TABLE IF NOT EXISTS asignaciones (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    id_maestro INT NOT NULL,
    id_grupo INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asignacion_maestro
        FOREIGN KEY (id_maestro) REFERENCES maestros(id_maestro)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_asignacion_grupo
        FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT unique_maestro UNIQUE (id_maestro),
    CONSTRAINT unique_grupo UNIQUE (id_grupo)
) ENGINE=InnoDB";

/*
EJECUTAR CREACIÓN DE TABLAS
*/
if (!mysqli_query($conexion, $sql_maestros)) {
    die("Error al crear tabla maestros: " . mysqli_error($conexion));
}

if (!mysqli_query($conexion, $sql_grupos)) {
    die("Error al crear tabla grupos: " . mysqli_error($conexion));
}

if (!mysqli_query($conexion, $sql_asignaciones)) {
    die("Error al crear tabla asignaciones: " . mysqli_error($conexion));
}

echo "Ingreso correcto bienvenido administrador.";

mysqli_close($conexion);
?>