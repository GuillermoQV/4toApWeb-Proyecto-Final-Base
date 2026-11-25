# Recorridos virtuales del equipo A-4

Módulo del **Explorador Vocacional UTBB**. Un editor crea la visita de su carrera. Coordinación decide cuándo aparece en el catálogo. El visitante navega entre escenas y abre los puntos de interés.

## Puesta en marcha

1. Clona el fork del equipo dentro de `C:\xampp\htdocs\explorador-vocacional`. Copia `config/config.ejemplo.php` a `config/config.php`.
2. Inicia Apache y MySQL en XAMPP. En phpMyAdmin importa primero `sql/01-nucleo.sql` y después `modulos/recorridos/esquema.sql`.
3. En desarrollo abre `http://localhost/explorador-vocacional/herramientas/crear-usuarios.php` para crear las cuentas de prueba.
4. Usa `/auth/entrar.php` para iniciar sesión. El panel se abre en `/modulos/recorridos/admin/` y el catálogo en `/modulos/recorridos/`, dentro de la URL del proyecto.

| Cuenta de prueba | Qué hace | Contraseña de prueba |
| --- | --- | --- |
| `con@utbb.mx` | Edita la carrera de Contaduría | `Demo1234` |
| `coordinacion@utbb.mx` | Publica, archiva y desarchiva | `Demo1234` |

`RUTA_BASE` debe coincidir con la carpeta del proyecto en Apache. Ajusta `BD_PUERTO` si MySQL de XAMPP usa un puerto distinto de 3306.

## Recorrido para la exposición

Crea **Una visita a Contaduría**. Guarda estas escenas y carga sus imágenes ilustrativas desde **Cargar medios → Escena completa**.

| Orden | Título | Descripción de muestra | Imagen en `img/` |
| --- | --- | --- | --- |
| 1 | Entrada principal | Aquí inicia la visita y puedes ubicar los espacios del campus. | `entrada.webp` |
| 2 | Biblioteca | Explora un espacio para estudiar y preparar trabajos en equipo. | `biblioteca.webp` |
| 3 | Laboratorio de Contaduría | Conoce el área donde se practican registros y reportes contables. | `laboratorio.webp` |

En el laboratorio crea un punto llamado **Mesa de registro**. Carga una imagen adicional o un video y elige el nombre del punto como ubicación. Entra como coordinación y pulsa **Publicar**. Abre el catálogo desde una ventana privada y prueba **Anterior**, **Siguiente** e **Ir a escena**.

## Código y datos

- `admin/` recibe formularios. `rec_post()` verifica el token con el núcleo, guarda el cambio y redirige.
- `includes/` reúne consultas, funciones de edición y partes de la vista.
- `index.php` lista recorridos publicados. `ver.php` muestra la escena elegida y sus puntos.
- `medio.php` entrega archivos publicados y autoriza la revisión de borradores al editor de esa carrera o a coordinación.
- `css/modulo.css` adapta las pantallas. `js/` agrega acciones de interfaz como el visor y el arrastre.

Las cuatro tablas relacionan recorridos, escenas, puntos de interés y medios. Cuando `punto_id` queda vacío, el archivo pertenece a la escena completa.

Las imágenes de prueba son ilustrativas. Los archivos que cargues se guardan en `C:\xampp\ev-rec-medios` en una instalación típica de XAMPP. Apache crea la carpeta al subir el primer archivo si tiene permiso para escribir. La base guarda el nombre interno del archivo.

| Carga | Formato | Límite del módulo |
| --- | --- | --- |
| Imagen | JPEG, PNG o WebP | 5 MB |
| Video de punto | MP4 | 30 MB |

La escena admite una imagen principal. Los videos y audios de escena guardados anteriormente se siguen reproduciendo. Si necesitas subir un MP4 de hasta 30 MB, ajusta `upload_max_filesize=30M` y `post_max_size=32M` en `php.ini` y reinicia Apache.

## Prueba antes de entregar

1. Guarda escenas, cambia el orden y revisa el resultado después de recargar.
2. Abre el punto y recorre su galería. Usa la imagen principal para abrir el visor con zoom.
3. Archiva el recorrido. Comprueba que el visitante ya no abre ni la visita ni sus medios. Usa **Desarchivar** para volver a borrador.
4. Confirma que un editor de otra carrera recibe acceso denegado al intentar editar.

Se utilizó ChatGPT para preparar el código y la guía. El equipo puede registrar las pruebas ejecutadas durante su propia integración.
