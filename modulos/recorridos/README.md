# Recorridos virtuales del equipo A-4

## Instalación local

1. Configura el proyecto base, inicia Apache y MariaDB desde XAMPP e importa **sql/01-nucleo.sql**.
2. Importa **modulos/recorridos/esquema.sql**. Crea las cuatro tablas vacías. El contenido se captura desde el panel cuando los equipos entreguen sus materiales.
3. En desarrollo, genera usuarios mediante **herramientas/crear-usuarios.php**. Las cuentas de prueba son **con@utbb.mx** (editor de Contaduría) y **coordinacion@utbb.mx** (coordinación). La contraseña de prueba de ambas es **Demo1234**.

## Alta de contenido

Crea un recorrido para la carrera correspondiente, agrega sus escenas y carga los medios que envíe cada equipo. Elige una imagen principal por escena. En cada punto de interés puedes cargar imágenes y videos MP4. El botón de cada punto abre un carrusel con el título, la descripción y los archivos en orden de carga. Se cierra con el botón Cerrar o con Escape. Desde Cargar medios puedes abrir o eliminar cada archivo individualmente. Los videos y audios de escena ya existentes se pueden seguir reproduciendo. Si una escena antigua tiene varias imágenes principales, se muestra la primera y el panel conserva las demás para revisarlas. Conserva el recorrido en borrador hasta revisar el material.

El selector de **sol y luna** cambia el tema de las pantallas públicas y de administración de Recorridos. La preferencia se recuerda en el navegador. Las animaciones respetan la opción de reducir el movimiento del dispositivo.

Las tarjetas y el reproductor aparecen con una transición breve cuando entran en pantalla. El contenido sigue visible si el navegador no admite Intersection Observer.

## Contrato del módulo

- Un recorrido pertenece a una carrera del núcleo y pasa por borrador, publicado y archivado. Desde **Administrar recorridos**, coordinación puede pulsar **Desarchivar** para devolverlo a borrador con sus escenas, puntos y medios. Para mostrarlo de nuevo en el catálogo, vuelve a pulsar **Publicar**.
- Cada escena pertenece a un recorrido. En el formulario, elige la posición en la lista desplegable. También puedes arrastrar su tarjeta sobre la posición deseada o usar Subir y Bajar. Al crear, mover o eliminar escenas se guardan posiciones consecutivas. El reproductor muestra las escenas en ese orden y resalta la seleccionada.
- Los puntos aparecen debajo de los medios de su escena. Se numeran por orden de creación y sus botones se distribuyen según el ancho de pantalla.
- Cada medio pertenece a una escena. Si se asigna a un punto, la llave foránea exige que ese punto pertenezca a la misma escena.
- La carga está en **admin/medios.php?escena_id=ID**. Desde el panel de escenas enlaza a **medios.php?escena_id=ID**.
- La vista pública puede usar **medio.php?id=ID** en el atributo src de imagen, video o audio. Usa **descarga=1** para ofrecer una descarga. Los publicados están disponibles al público. Un borrador o archivado solo se entrega a coordinación y al editor de la carrera.
- Para mostrar los archivos de una escena, filtra por **escena_id** y **punto_id IS NULL**. Para mostrar los archivos de un punto, filtra por **punto_id** y **escena_id**.

El catálogo y el panel muestran nueve recorridos por página, con tres columnas en escritorio. El filtro de carrera se conserva al paginar.

## Archivos

Los archivos se guardan fuera de la raíz web, en una carpeta hermana llamada **ev-rec-medios**. En XAMPP suele ser **C:\xampp\ev-rec-medios**. Apache necesita poder escribir ahí. Si no puede crear la carpeta, créala con los permisos apropiados.

| Tipo | Formatos | Límite del módulo |
|---|---|---:|
| Imagen | JPEG, PNG, WebP | 5 MB |
| Video | MP4 | 30 MB |
| Narración previa | MP3, M4A (reproducción de archivos existentes) | — |

En XAMPP el límite efectivo también depende de PHP. Para usar el máximo del módulo puedes establecer **upload_max_filesize=30M** y **post_max_size=32M** en php.ini y reiniciar Apache. Usa archivos pequeños en la primera prueba.

Al dar de baja un recorrido, escena o punto las llaves foráneas eliminan los registros dependientes. Para limpiar el disco en la integración, recupera los nombres antes de la baja y llama **rec_limpiar_medio()** después de confirmar la operación en base de datos.

## Pruebas para la integración

1. Crea un borrador, una escena y un punto desde el panel del equipo. Si quieres probar la carga, usa un archivo local y comprueba que aparece en **admin/medios.php**. Agrega una imagen al punto y prueba su enlace.
2. Repite el acceso al panel con el editor de otra carrera. Debe denegarse.
3. Como visitante, prueba un enlace de medio del recorrido en borrador, después de publicarlo y después de archivarlo. Solo debe abrir durante el estado publicado.
4. Prueba video y audio desde el reproductor, incluyendo saltar a otra posición del video. Comprueba que una carga de tipo incompatible muestre un aviso.
5. Registra quién probó, los resultados y los hallazgos en el reporte del equipo.

Se utilizó ChatGPT para preparar un borrador del esquema, código de medios e instrucciones. El equipo debe revisar el código y documentar las pruebas que realmente ejecute.
