<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';

require_once __DIR__ . '/../includes/medios.php';

$entradaEscena = $_GET['escena_id'] ?? null;
$escenaId = is_string($entradaEscena) ? entero($entradaEscena) : null;
$escena = $escenaId === null ? null : rec_contexto_escena($escenaId);
if ($escena === null) {
    no_encontrado('Selecciona una escena existente.');
}

$carreraId = (int)$escena['carrera_id'];
requerir_carrera($carreraId);

$urlPanel = 'medios.php?escena_id=' . $escenaId;
$puntos = rec_puntos_de_escena($escenaId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['token'] ?? null)) {
        http_response_code(400);
        exit('Petición no válida. Si el archivo es grande, revisa post_max_size en PHP.');
    }
    verificar_token();

    if (isset($_POST['eliminar'])) {
        $id = is_string($_POST['eliminar']) ? entero($_POST['eliminar']) : null;
        try {
            rec_eliminar_medio($id ?? 0, $escenaId, $carreraId);
            mensaje('exito', 'Medio eliminado.');
        } catch (DomainException $error) {
            mensaje('error', $error->getMessage());
        }
        redirigir($urlPanel);
    }
    $tipo = $_POST['tipo'] ?? '';
    $punto = $_POST['punto_id'] ?? '';
    $archivo = $_FILES['archivo'] ?? null;
    $puntoId = is_string($punto) && $punto !== '' ? entero($punto) : null;
    $puntosValidos = array_map('intval', array_column($puntos, 'id'));

    if (!is_string($tipo) || !isset(REC_TIPOS_MEDIO[$tipo]) || !is_array($archivo)) {
        mensaje('error', 'Selecciona un tipo de medio y un archivo.');
        redirigir($urlPanel);
    }
    if ($punto !== '' && ($puntoId === null || !in_array($puntoId, $puntosValidos, true))) {
        mensaje('error', 'El punto de interés debe pertenecer a esta escena.');
        redirigir($urlPanel);
    }
    if ($puntoId !== null && $tipo === 'audio') {
        mensaje('error', 'Los puntos de interés admiten imágenes y videos.');
        redirigir($urlPanel);
    }

    $reglas = REC_TIPOS_MEDIO[$tipo];
    try {
        $nombre = guardar_archivo($archivo, rec_carpeta_medios(), $reglas['mime'], $reglas['limite']);
    } catch (RuntimeException $error) {
        mensaje('error', $error->getMessage());
        redirigir($urlPanel);
    }

    try {
        $guardado = rec_registrar_medio($escenaId, $carreraId, $puntoId, $tipo, $nombre);
    } catch (DomainException $error) {
        rec_limpiar_medio($nombre);
        mensaje('error', $error->getMessage());
        redirigir($urlPanel);
    } catch (PDOException $error) {
        error_log('REC: no se pudo registrar el medio. ' . $error->getMessage());
        $guardado = false;
    }

    if ($guardado) {
        mensaje('exito', 'Medio guardado.');
    } else {
        rec_limpiar_medio($nombre);
        mensaje('error', 'No se pudo registrar el medio. Revisa que la escena y el punto sigan disponibles.');
    }
    redirigir($urlPanel);
}

$medios = rec_medios_de_escena($escenaId);
rec_cabecera('Medios de escena');
?>
<section class="contenedor seccion">
  <p><a href="escenas.php?recorrido_id=<?= e((string)$escena['recorrido_id']) ?>">Volver a escenas</a></p>
  <h1>Medios de <?= e($escena['titulo']) ?></h1>
  <?php pintar_mensajes(); ?>
  <form class="panel" action="<?= e($urlPanel) ?>" method="post" enctype="multipart/form-data">
    <?= campo_token() ?>
    <div class="campo">
      <label class="campo__etiqueta" for="tipo">Tipo de medio</label>
      <select class="campo__control" id="tipo" name="tipo" required>
        <option value="imagen">Imagen (hasta 5 MB)</option>
        <option value="video">Video MP4 (hasta 30 MB)</option>

      </select>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="punto_id">Ubicación del medio</label>
      <select class="campo__control" id="punto_id" name="punto_id">
        <option value="">Escena completa</option>
        <?php foreach ($puntos as $punto): ?>
          <option value="<?= e((string)$punto['id']) ?>"><?= e($punto['titulo']) ?></option>
        <?php endforeach; ?>
      </select>
      <p>La escena completa admite una sola imagen. En cada punto puedes subir varias imágenes y videos.</p>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="archivo">Archivo</label>
      <input class="campo__control" id="archivo" name="archivo" type="file" required
             accept="image/jpeg,image/png,image/webp,video/mp4">
    </div>
    <button class="boton boton--primario" type="submit">Subir medio</button>
  </form>

  <h2>Archivos de esta escena</h2>
  <?php if ($medios === []): ?>
    <p>La escena aún no tiene medios.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($medios as $medio): ?>
        <li>
          <?= e($medio['tipo']) ?> · <?= e($medio['punto'] ?? 'Escena completa') ?>
          <a href="../medio.php?id=<?= e((string)$medio['id']) ?>">Abrir medio <?= e((string)$medio['id']) ?></a>
          <form method="post" action="<?= e($urlPanel) ?>">
            <?= campo_token() ?>
            <button class="boton boton--peligro boton--pequeno" name="eliminar" value="<?= e((string)$medio['id']) ?>">Eliminar medio</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
<?php pie(); ?>
