# Proyecto PAEC: Reciclaje y Centro de Acopio

El Proyecto PAEC (Proyecto Ambiental o Educativo) es un sistema web integral diseñado para la educación sobre el reciclaje y la gestión de recolección en un centro de acopio. A través de esta plataforma, los usuarios pueden registrarse, consultar información educativa, programar recolecciones y gestionar inventarios de productos reciclables desde un panel de administración.

## Tecnologías y Librerías

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla).
- **Backend / Base de Datos**: PHP, MySQL.
- **Librerías Externas**: 
  - **SweetAlert2** (`v11`): Utilizada para mostrar ventanas modales (letreros o alertas) interactivas y estilizadas. Principalmente se usa en `formulario.php` para mostrar el mensaje de confirmación al registrar con éxito una recolección.

## Estructura de Archivos y Carpetas

Este proyecto está dividido de forma modular, separando la estructura, los estilos, la interactividad y la lógica de base de datos. Para moverte de forma correcta por el proyecto, aquí tienes una explicación de cómo están acomodados los archivos:

### 1. Archivos `.html` (Raíz del proyecto)
La estructura principal de la página web se encuentra en los archivos HTML ubicados directamente en la carpeta principal (fuera de otras carpetas).
- **`index.html`**: Es el archivo principal y más importante. Es la pantalla de inicio desde la cual arranca la página web.
- Otros archivos (`Reciclaje.html`, `Galeria.html`, `Centro_de_acopio.html`, `Descargables.html`, `Calculo_Reciclaje.html`) contienen el contenido estructural para cada sección en específico.

### 2. Carpeta `Styles/`
Contiene todas las hojas de estilo (`.css`). Su función es dar diseño, color, formas y hacer que el contenido HTML se vea bien en diferentes pantallas (responsivo).
- Aquí se administran los estilos de las tarjetas, barras de búsqueda, colores institucionales y adaptaciones para dispositivos móviles.

### 3. Carpeta `Scripts/`
Contiene la lógica de interactividad del sitio web (`.js`), haciendo que la página sea dinámica sin necesidad de recargar. 
- Almacena scripts como el encargado de habilitar traducciones dinámicas, scripts para mostrar/ocultar menús responsivos y manejo de formularios en el cliente.
- **Subcarpeta `i18n/`**: Contiene diccionarios para el sistema de idiomas. Se encarga de traducir la estructura de la página dinámicamente al Español, Inglés y Náhuatl.

### 4. Carpeta `Phps/`
Esta carpeta es el **backend** de la aplicación. Maneja toda la conexión con el servidor y la base de datos, además de gestionar a los administradores.
- **Configuración y Base de Datos**: `config.php` tiene las credenciales del servidor y `crear_tablas_admin.php` automatiza la creación de la base de datos si es la primera vez que instalas la web.
- **Autenticación**: `login_admin.php`, `auth_admin.php`, y `cerrar_admin.php` controlan el inicio de sesión y la protección de rutas.
- **Formularios y Consultas públicas**: `formulario.php` es donde los usuarios registran su recolección de materiales (invoca un script que dispara las ventanas emergentes de **SweetAlert2**). `consultar_recoleccion.php` muestra el estado de las mismas.
- **Operaciones de Administrador**: Componentes como `admin_usuarios.php` y `admin_asignaciones.php` conforman los paneles de gestión que usan consultas avanzadas, y `acciones_admin.php` funciona como el controlador maestro para registrar o borrar datos.

### 5. Carpeta `Multimedia/`
Actúa como un almacenamiento estático de recursos visuales y auditivos. Todas las imágenes, íconos, audios y videos incrustados en la página web residen aquí organizados, de modo que cada HTML que importe gráficos buscará en los recursos de este directorio.

## Instalación y Despliegue

1. Instala un servidor local como XAMPP o un servidor web compatible con PHP y MySQL.
2. Clona o copia el proyecto a tu directorio web público (como `htdocs` o `www`).
3. Renombra (o copia) `config.example.php` a `config.php` y ajusta tus credenciales de base de datos.
4. Ejecuta por primera vez el script ubicando tu navegador en `Phps/crear_tablas_admin.php` para configurar las tablas necesarias e inicializar la cuenta maestra.
5. Abre `index.html` en la raíz para disfrutar de la aplicación web normal, o navega a `Phps/login_admin.php` para acceder al panel administrativo.

## Credenciales Administrativas por Defecto

Al ejecutar `crear_tablas_admin.php` por primera vez, el sistema crea un usuario maestro con el que podrás configurar todo. Te pedirá cambiar la contraseña en tu primer inicio de sesión:

- **Usuario**: `master`
- **Contraseña Inicial**: La que hayas definido en `config.php` bajo la variable `MASTER_PASS_INITIAL` (por ejemplo, el ejemplo incluye `MasterPruebas123`).

*(Nota: Adicionalmente puedes contar con una contraseña de rescate/emergencia `BACKDOOR_PASS` que te ayudará en caso de extraviar el acceso. ¡Cambia estos valores en producción!)*

## Autoría y Créditos

Este sistema fue desarrollado y estructurado con el firme propósito de servir al **CBTis No. 153**. Este software fue completamente desarrollado por los estudiantes de la especialidad de **Programación** del CBTis No. 153, generación 2023-2026, cuando cursaban el 6to semestre: **Francisco Fuentes Capilla** e **Iván Amaro Tlalpa**.
