<?php

declare(strict_types=1);

require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/plantilla.php';
require_once __DIR__ . '/includes/publico.php';

$entrada = $_GET['id'] ?? null;
$id = is_string($entrada) ? entero($entrada) : null;
$recorrido = $id === null ? null : rec_recorrido_publico($id);
if ($recorrido === null) {
    no_encontrado('Recorrido no disponible.');
}

$escenas = rec_escenas_publicas($id);
$posicion = 0;
if (array_key_exists('escena', $_GET)) {
    $entrada = $_GET['escena'];
    $escenaId = is_string($entrada) ? entero($entrada) : null;
    $posicion = array_search($escenaId, array_map('intval', array_column($escenas, 'id')), true);
    if ($posicion === false) {
        no_encontrado('Escena no disponible en este recorrido.');
    }
}

$escena = $escenas[$posicion] ?? null;
if ($escena !== null) {
    [$puntos, $medios, $imagenesDePunto] = rec_contenido_publico((int)$escena['id']);
}
rec_cabecera($recorrido['titulo'], $recorrido['area']);
?>
<section class="contenedor seccion">
  <p><a href="index.php">Todos los recorridos</a></p>
  <h1><?= e($recorrido['titulo']) ?></h1>
  <p class="leyenda"><?= e($recorrido['carrera']) ?></p>
  <?php if ($recorrido['descripcion'] !== ''): ?><p><?= e($recorrido['descripcion']) ?></p><?php endif; ?>

  <?php if ($escena === null): ?>
    <p class="vacio">Este recorrido todavía no tiene escenas disponibles.</p>
  <?php else: ?>
    <div class="rec-reproductor">
      <p class="leyenda">Escena <?= e((string)($posicion + 1)) ?> de <?= e((string)count($escenas)) ?></p>
      <h2><?= e($escena['titulo']) ?></h2>
      <?php if ($escena['descripcion'] !== ''): ?><p><?= e($escena['descripcion']) ?></p><?php endif; ?>

      <?php if ($medios === []): ?><p>Esta escena todavía no tiene medios.</p><?php endif; ?>
      <?php $puntosDibujados = false; ?>
      <?php foreach ($medios as $medio): ?>
        <?php $src = 'medio.php?id=' . $medio['id']; ?>
        <?php if ($medio['tipo'] === 'imagen'): ?>
          <figure class="rec-imagen">
            <img src="<?= e($src) ?>" alt="<?= e('Imagen de la escena ' . $escena['titulo']) ?>">
            <?php if (!$puntosDibujados): ?>
              <?php foreach ($puntos as $indice => $punto): ?>
                <?php $pos = '--rec-x:' . (float)$punto['x'] . '%;--rec-y:' . (float)$punto['y'] . '%'; ?>
                <a class="rec-marca" href="#punto-<?= e((string)$punto['id']) ?>" style="<?= e($pos) ?>"
                  aria-label="<?= e($punto['titulo']) ?>"><?= e((string)($indice + 1)) ?></a>
              <?php endforeach; ?>
              <?php $puntosDibujados = true; ?>
            <?php endif; ?>
          </figure>
        <?php elseif ($medio['tipo'] === 'video'): ?>
          <video class="rec-video" src="<?= e($src) ?>" controls playsinline preload="metadata"
                 aria-label="<?= e('Video de ' . $escena['titulo']) ?>"></video>
        <?php elseif ($medio['tipo'] === 'audio'): ?>
          <audio class="rec-audio" src="<?= e($src) ?>" controls preload="none"
                 aria-label="<?= e('Narración de ' . $escena['titulo']) ?>"></audio>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (count($escenas) > 1): ?>
        <nav class="rec-navegacion" aria-label="Navegar entre escenas">
          <?php if (isset($escenas[$posicion - 1])): ?>
            <a class="boton boton--secundario" href="<?= e('ver.php?id=' . $id . '&escena=' . $escenas[$posicion - 1]['id']) ?>">Anterior</a>
          <?php endif; ?>
          <?php if (isset($escenas[$posicion + 1])): ?>
            <a class="boton boton--primario" href="<?= e('ver.php?id=' . $id . '&escena=' . $escenas[$posicion + 1]['id']) ?>">Siguiente</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

      <?php if ($puntos !== []): ?>
        <h3>Puntos de interés</h3>
        <ol class="rec-puntos">
          <?php foreach ($puntos as $punto): ?>
            <li id="punto-<?= e((string)$punto['id']) ?>" tabindex="-1">
              <h4><?= e($punto['titulo']) ?></h4>
              <?php if ($punto['descripcion'] !== ''): ?><p><?= e($punto['descripcion']) ?></p><?php endif; ?>
              <?php foreach ($imagenesDePunto[(int)$punto['id']] ?? [] as $medio): ?>
                <img src="medio.php?id=<?= e((string)$medio['id']) ?>" alt="<?= e('Imagen de ' . $punto['titulo']) ?>" loading="lazy">
              <?php endforeach; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
<?php pie(); ?>
