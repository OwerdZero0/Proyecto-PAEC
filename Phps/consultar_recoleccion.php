<?php
require_once __DIR__ . '/config.php';

// 2. Conexión a la base de datos
$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
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

// 4. Construir la consulta SQL con filtros dinámicos (Base)
$sql_condiciones = " FROM recoleccion WHERE 1=1";

if (!empty($filtro_fecha_inicio)) {
    $sql_condiciones .= " AND fecha_entrega >= '" . mysqli_real_escape_string($conexion, $filtro_fecha_inicio) . "'";
}
if (!empty($filtro_fecha_fin)) {
    $sql_condiciones .= " AND fecha_entrega <= '" . mysqli_real_escape_string($conexion, $filtro_fecha_fin) . "'";
}
if (!empty($filtro_maestro)) {
    $sql_condiciones .= " AND responsable_entrega = '" . mysqli_real_escape_string($conexion, $filtro_maestro) . "'";
}
if (!empty($filtro_grupo)) {
    $sql_condiciones .= " AND grupo_entrega = '" . mysqli_real_escape_string($conexion, $filtro_grupo) . "'";
}

if (!empty($filtro_material)) {
    $columna_material = "material_" . mysqli_real_escape_string($conexion, $filtro_material);
    $sql_condiciones .= " AND $columna_material = 1";
}

// 5. Paginación
$sql_count = "SELECT COUNT(*) as total" . $sql_condiciones;
$resultado_count = mysqli_query($conexion, $sql_count);
$row_count = mysqli_fetch_assoc($resultado_count);
$total_registros = (int)$row_count['total'];

$por_pagina = 30;
$total_paginas = ceil($total_registros / $por_pagina);
if ($total_paginas == 0) $total_paginas = 1;

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;

$offset = ($pagina_actual - 1) * $por_pagina;

// 6. Consulta final con orden y límites
$sql_final = "SELECT *" . $sql_condiciones . " ORDER BY fecha_entrega DESC LIMIT $por_pagina OFFSET $offset";
$resultado = mysqli_query($conexion, $sql_final);

// Preparar URL base para la paginación conservando filtros
$params_get = $_GET;
unset($params_get['pagina']);
$query_filtros = http_build_query($params_get);
$url_base_paginacion = "?";
if (!empty($query_filtros)) {
    $url_base_paginacion .= $query_filtros . "&";
}

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
                    <option value="electrodomesticos" <?= ($filtro_material == 'electrodomesticos') ? 'selected' : '' ?>>Aparatos electrónicos</option>
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
                                        if($row['material_electrodomesticos']) echo "<span class='badge badge-electrodomesticos'>Aparatos electrónicos: {$row['cantidad_electrodomesticos']}kg</span>";
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
        
        <!-- Renderizar paginación -->
        <?php if ($total_paginas > 1): ?>
            <div class="paginacion">
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?= $url_base_paginacion . "pagina=" . ($pagina_actual - 1) ?>" class="page-link">Anterior &larr;</a>
                <?php endif; ?>

                <?php 
                for ($i = 1; $i <= $total_paginas; $i++): 
                    // Siempre mostrar la primera, última y algunas cercanas a la actual
                    if ($i == 1 || $i == $total_paginas || abs($i - $pagina_actual) < 2):
                ?>
                    <a href="<?= $url_base_paginacion . "pagina=" . $i ?>" class="page-link <?= ($i == $pagina_actual) ? 'active' : '' ?>"><?= $i ?></a>
                <?php 
                    elseif ($i == 2 && $pagina_actual > 3): 
                        echo "<span class='page-dots'>...</span>";
                    elseif ($i == $total_paginas - 1 && $pagina_actual < $total_paginas - 2):
                        echo "<span class='page-dots'>...</span>";
                    endif;
                endfor; 
                ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?= $url_base_paginacion . "pagina=" . ($pagina_actual + 1) ?>" class="page-link">Siguiente &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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

<footer style="text-align: center; padding: 20px; font-size: 0.85rem; color: #4a4a4a; margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1);">
    <p>&copy; Sistema desarrollado y donado con orgullo al CBTis No. 153 por los estudiantes Francisco Fuentes Capilla e Iván Amaro Tlalpa (2026).</p>
</footer>
</body>
</html>
<?php mysqli_close($conexion); ?>