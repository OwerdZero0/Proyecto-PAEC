<?php

// 2. Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8mb4");

// 3. Capturar filtros desde la URL (MÉTODO GET)
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin   = $_GET['fecha_fin'] ?? '';
$filtro_maestro = $_GET['maestro'] ?? '';
$filtro_grupo   = $_GET['grupo'] ?? '';
$filtro_material = $_GET['material'] ?? '';

// 4. Construir la consulta SQL con filtros dinámicos
$sql = "SELECT * FROM recoleccion WHERE 1=1";

if (!empty($filtro_fecha_inicio)) {
    $sql .= " AND fecha_entrega >= '" . mysqli_real_escape_string($conexion, $filtro_fecha_inicio) . "'";
}
if (!empty($filtro_fecha_fin)) {
    $sql .= " AND fecha_entrega <= '" . mysqli_real_escape_string($conexion, $filtro_fecha_fin) . "'";
}
if (!empty($filtro_maestro)) {
    $sql .= " AND responsable_entrega = '" . mysqli_real_escape_string($conexion, $filtro_maestro) . "'";
}
if (!empty($filtro_grupo)) {
    $sql .= " AND grupo_entrega = '" . mysqli_real_escape_string($conexion, $filtro_grupo) . "'";
}

if (!empty($filtro_material)) {
    $columna_material = "material_" . mysqli_real_escape_string($conexion, $filtro_material);
    $sql .= " AND $columna_material = 1";
}

$sql .= " ORDER BY fecha_entrega DESC";
$resultado = mysqli_query($conexion, $sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Recolección Semanal</title>
    <!-- Mismas fuentes y estilo que el admin -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Styles/style_consultar_recoleccion.css">
</head>
<body>

<div class="contenedor">
    
    <h1>Reporte de Recolección Semanal</h1>

    <!-- Tarjeta para filtros -->
    <div class="card">
        <h2>Filtros de Búsqueda</h2>
        <form method="GET" class="contenedor_filtros">
            <div class="campo_filtro">
                <label>Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($filtro_fecha_inicio) ?>">
            </div>

            <div class="campo_filtro">
                <label>Fecha Término:</label>
                <input type="date" name="fecha_fin" value="<?= htmlspecialchars($filtro_fecha_fin) ?>">
            </div>

            <!-- El JS llenará estos selects. Les pasamos data-selected para autoseleccionar -->
            <div class="campo_filtro">
                <label>Maestro:</label>
                <select name="maestro" id="maestro" data-selected="<?= htmlspecialchars($filtro_maestro) ?>">
                    <option value="">Cargando maestros...</option>
                </select>
            </div>

            <div class="campo_filtro">
                <label>Grupo:</label>
                <select name="grupo" id="grupo" data-selected="<?= htmlspecialchars($filtro_grupo) ?>">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="campo_filtro">
                <label>Producto entregado:</label>
                <select name="material">
                    <option value="">Todos</option>
                    <option value="pet" <?= ($filtro_material == 'pet') ? 'selected' : '' ?>>PET</option>
                    <option value="carton" <?= ($filtro_material == 'carton') ? 'selected' : '' ?>>Cartón</option>
                    <option value="tapas" <?= ($filtro_material == 'tapas') ? 'selected' : '' ?>>Tapas</option>
                    <option value="vidrio" <?= ($filtro_material == 'vidrio') ? 'selected' : '' ?>>Vidrio</option>
                    <option value="papel" <?= ($filtro_material == 'papel') ? 'selected' : '' ?>>Papel</option>
                </select>
            </div>

            <div class="botones_filtro">
                <button type="submit" class="btn-filtrar">Filtrar</button>
                <a href="consultar_recoleccion.php">
                    <button type="button" class="btn-limpiar">Limpiar</button>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla con los datos -->
    <div class="tabla">
        <h2>Resultados</h2>
        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th>Grupo</th>
                        <th>Materiales y Cantidades</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?= date("d/m/Y", strtotime($row['fecha_entrega'])) ?></td>
                                <td><?= htmlspecialchars($row['responsable_entrega']) ?></td>
                                <td><?= htmlspecialchars($row['grupo_entrega']) ?></td>
                                <td>
                                    <div class="badges-container">
                                    <?php 
                                        if($row['material_pet']) echo "<span class='badge badge-pet'>PET: {$row['cantidad_pet']}kg</span>";
                                        if($row['material_carton']) echo "<span class='badge badge-carton'>Cartón: {$row['cantidad_carton']}kg</span>";
                                        if($row['material_tapas']) echo "<span class='badge badge-tapas'>Tapas: {$row['cantidad_tapas']}kg</span>";
                                        if($row['material_vidrio']) echo "<span class='badge badge-vidrio'>Vidrio: {$row['cantidad_vidrio']}kg</span>";
                                        if($row['material_papel']) echo "<span class='badge badge-papel'>Papel: {$row['cantidad_papel']}kg</span>";
                                    ?>
                                    </div>
                                </td>
                                <td class="td-observaciones"><?= nl2br(htmlspecialchars($row['observaciones'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No se encontraron registros con los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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

<script src="../Scripts/script_consultar_recoleccion.js"></script>

</body>
</html>
<?php mysqli_close($conexion); ?>