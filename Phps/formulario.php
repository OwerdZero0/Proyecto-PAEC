<?php
require_once __DIR__ . '/auth_admin.php';

/* =========================================================
    SECCIÓN 1: VALIDAR SESIÓN
    ---------------------------------------------------------
    Esta parte verifica que el usuario haya iniciado sesión.
    Si no está autenticado, lo redirige al login.
========================================================= */
if (!admin_logueado()) {
    header('Location: login_admin.php?destino=' . urlencode('formulario.php') . '&error=' . urlencode('Debes iniciar sesión para entrar al formulario.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- =====================================================
	    SECCIÓN 2: ESTILOS DE LA PÁGINA
	    -----------------------------------------------------
	    Aquí se carga el archivo CSS del formulario.
	===================================================== -->
	<link rel="stylesheet" href="../Styles/style_formulario.css">

	<title>Formulario</title>
</head>
<body>

	<!-- =====================================================
	    SECCIÓN 3: FORMULARIO PRINCIPAL
	    -----------------------------------------------------
	    Este formulario envía los datos a acciones_admin.php
	    para guardar la recolección.
	===================================================== -->
	<form class="formulario" action="acciones_admin.php" method="post">

		<!-- =================================================
		    SECCIÓN 4: TÍTULO PRINCIPAL
		================================================= -->
		<section class="contenedor_titulos">
			<div class="fondo_titulo">
				<h1 class="titulo_formulario" data-i18n="titulo_formulario">
					Formulario de recolección semanal de materiales reciclables
				</h1>
			</div>
		</section>

		<!-- =================================================
		    SECCIÓN 5: IDENTIFICACIÓN DE LA ENTREGA
		    -------------------------------------------------
		    Aquí se captura:
		    - fecha
		    - responsable
		    - grupo
		    
		    IMPORTANTE:
		    Los selects de responsable y grupo YA NO tienen
		    opciones escritas manualmente.
		    Ahora el JS los llenará automáticamente usando
		    los datos que reciba del archivo PHP que consulta
		    la base de datos.
		================================================= -->
		<section class="contenedor_identificacion">
			<div class="fondo_subtitulo">
				<h2 class="subtitulo_formulario" data-i18n="subtitulo1_1_formulario">
					Identificación de la entrega
				</h2>
			</div>
			
			<label class="pregunta" data-i18n="pregunta1_1_formulario">Fecha de la semana:</label>
			<input type="date" name="fecha_entrega" class="respuesta" required>
			<br><br>
			
			<label class="pregunta" data-i18n="pregunta1_2_formulario">
				Responsable de la entrega (tutor):
			</label>
			<select name="responsable_entrega" class="respuesta" required>
				<option value="">Seleccione un responsable</option>
			</select>
			<br><br>
			
			<label class="pregunta" data-i18n="pregunta1_3_formulario">Grupo:</label>
			<select name="grupo_entrega" class="respuesta" required>
				<option value="">Seleccione un grupo</option>
			</select>
		</section>

		<!-- =================================================
		    SECCIÓN 6: REGISTRO DE CANTIDADES
		    -------------------------------------------------
		    Aquí se seleccionan los materiales entregados y
		    sus cantidades.
		    
		    El JS se encargará de:
		    - habilitar/deshabilitar inputs numéricos
		    - volver obligatoria la cantidad cuando se marque
			un material
		================================================= -->
		<section class="contenedor_cantidades">
			<div class="fondo_subtitulo">
				<h2 class="subtitulo_formulario" data-i18n="subtitulo2_1_formulario">
					Registro de cantidades
				</h2>
			</div>
			
			<label class="pregunta" data-i18n="pregunta2_1_formulario">
				Selecciona los productos entregados:
			</label>
			<br><br>
			
			<div class="cantidad_entrega">
				<label class="respuesta">
					<input id="1" type="checkbox" class="check" name="material_pet" value="1"> 
					<span data-i18n="check1_formulario">PET (botellas de plástico)</span> 
					<input type="number" min="0" step="any" name="cantidad_pet" class="respuesta"> kg
				</label>
				
				<label class="respuesta">
					<input id="2" type="checkbox" class="check" name="material_carton" value="1"> 
					<span data-i18n="check2_formulario">Cartón</span>
					<input type="number" min="0" step="any" name="cantidad_carton" class="respuesta"> kg
				</label>
				
				<label class="respuesta">
					<input id="3" type="checkbox" class="check" name="material_tapas" value="1"> 
					<span data-i18n="check3_formulario">Tapas plástico</span>
					<input type="number" min="0" step="any" name="cantidad_tapas" class="respuesta"> kg
				</label>
				
				<label class="respuesta">
					<input id="4" type="checkbox" class="check" name="material_vidrio" value="1"> 
					<span data-i18n="check4_formulario">Vidrio</span>
					<input type="number" min="0" step="any" name="cantidad_vidrio" class="respuesta"> kg
				</label>
				
				<label class="respuesta">
					<input id="5" type="checkbox" class="check" name="material_electrodomesticos" value="1"> 
					<span data-i18n="check5_formulario">Aparatos electrónicos</span> 
					<input type="number" min="0" step="any" name="cantidad_electrodomesticos" class="respuesta"> kg
				</label>
				
				<label class="respuesta">
					<input id="6" type="checkbox" class="check" name="material_papel" value="1"> 
					<span data-i18n="check6_formulario">Papel (libros y libretas)</span> 
					<input type="number" min="0" step="any" name="cantidad_papel" class="respuesta"> kg
				</label>
			</div>
			
			<br><br>
			<label class="pregunta" data-i18n="pregunta2_2_formulario">Observaciones:</label>
			<textarea name="observaciones" class="respuesta" rows="4" required></textarea>
		</section>

		<!-- =================================================
		    SECCIÓN 7: BOTONES DEL FORMULARIO
		================================================= -->
		<section class="contenedor_botones">
			<button class="boton_enviar" type="submit">
				<span class="contenedor_icono_enviar">
					<svg viewBox="0 0 384 512" height="0.9em" class="icono_enviar">
						<path d="M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z"></path>
					</svg>
				</span>
				<p class="texto_enviar" data-i18n="boton_enviar_formulario">Enviar</p>
			</button>

			<a href="cerrar_admin.php">
				<button class="boton_inicio" type="button">
					<span class="contenedor_icono_inicio">
						<svg viewBox="0 0 576 512" class="icono_inicio">
							<path d="M280.4 148.3L96 300.1V464c0 8.8 7.2 16 16 16l112-.3c8.8 0 16-7.2 16-16V368c0-8.8 7.2-16 16-16h64.3c8.8 0 16 7.2 16 16v95.7c0 8.8 7.2 16 16 16L464 480c8.8 0 16-7.2 16-16V300L295.6 148.3c-6.4-5.2-15.8-5.2-22.2 0zM571.6 251.5l-61.5 50.2c-3 2.5-7.4 2.1-9.9-.9l-26.3-31.7c-2.5-3-2.1-7.4.9-9.9l61.5-50.2c12.1-9.9 14-27.9 4.1-40L488 86.5c-9.9-12.1-27.9-14-40-4.1L384 136.9V48c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16v88.9L128 82.4c-12.1-9.9-30.1-8-40 4.1L39.7 168.9c-9.9 12.1-8 30.1 4.1 40L276 373c6.4 5.2 15.8 5.2 22.2 0l273.4-221.5c12.1-9.9 14-27.9 4.1-40l-48.3-58.5c-9.9-12.1-27.9-14-40-4.1z"/>
						</svg>
					</span>
					<p class="texto_inicio" data-i18n="boton_inicio_formulario">Inicio</p>
				</button>
			</a>

			<button class="boton_basura" type="reset">
				<span class="contenedor_icono_basura">
					<svg viewBox="0 0 24 24" class="icono_basura">
						<path d="M3 6h18M9 6V4h6v2m-7 4v10m4-10v10m4-10v10M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
					</svg>
				</span>
				<p class="texto_basura" data-i18n="boton_basura_formulario">Borrar</p>
			</button>
		</section>
	</form>

	<!-- =====================================================
	    SECCIÓN 8: SCRIPTS
	    -----------------------------------------------------
	    1. script_formulario.js:
	        - carga responsables y grupos desde la BD
	        - relaciona maestro con grupo
	        - valida materiales y cantidades
	        - muestra confirmación al enviar
	    
	    2. script_traductor.js:
	        - mantiene tu sistema de traducción
	===================================================== -->
	<script src="../Scripts/script_formulario.js"></script>
	<script type="module" src="../Scripts/script_traductor.js"></script>
</body>
</html>