<?php
$conexion = mysqli_connect("localhost", "root", "root", "baseRecoleccion") // Nombre de la base de datos baseRecoleccion
    or die("Problemas con la conexión: " . mysqli_connect_error());

$sql = "INSERT INTO recoleccion( --Nombre de la tabla recoleccion
            `fecha_entrega`, --DATE
            responsable_entrega, --VARCHAR (80)
            grupo_entrega, --VARCHAR (50)
            material_pet, --BOOLEAN 
            cantidad_pet, --DECIMAL (10,2)
            material_carton, --BOOLEAN
            cantidad_carton, --DECIMAL (10,2)
            material_tapas, --BOOLEAN
            cantidad_tapas, --DECIMAL (10,2)
            material_vidrio, --BOOLEAN
            cantidad_vidrio, --DECIMAL (10,2)
            material_electrodomesticos, --BOOLEAN
            cantidad_electrodomesticos, --DECIMAL (10,2)
            material_papel, --BOOLEAN
            cantidad_papel, --DECIMAL (10,2)
            observaciones --VARCHAR (250)
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
