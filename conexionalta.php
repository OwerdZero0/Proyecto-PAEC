<?php
$conexion = mysqli_connect("localhost", "root", "root", "base1")
    or die("Problemas con la conexión: " . mysqli_connect_error());

$sql = "INSERT INTO alumnos(
            `fecha_entrega`,
            responsable_entrega,
            grupo_entrega,
            material_pet,
            cantidad_pet,
            material_carton,
            cantidad_carton,
            material_tapas,
            cantidad_tapas,
            material_vidrio,
            cantidad_vidrio,
            material_electrodomesticos,
            cantidad_electrodomesticos,
            material_papel,
            cantidad_papel,
            observaciones
        ) VALUES (
            '$_POST[fecha_entrega]',
            '$_POST[responsable_entrega]',
            '$_POST[grupo_entrega]',
            '$_POST[material_pet]',
            '$_POST[cantidad_pet]',
            '$_POST[material_carton]',
            '$_POST[cantidad_carton]',
            '$_POST[material_tapas]',
            '$_POST[cantidad_tapas]',
            '$_POST[material_vidrio]',
            '$_POST[cantidad_vidrio]',
            '$_POST[material_electrodomesticos]',
            '$_POST[cantidad_electrodomesticos]',
            '$_POST[material_papel]',
            '$_POST[cantidad_papel]',
            '$_POST[observaciones]'
        )";

mysqli_query($conexion, $sql)
    or die("Problemas en el insert: " . mysqli_error($conexion));

mysqli_close($conexion);

echo "La información fue dada de alta con éxito.";
?>
