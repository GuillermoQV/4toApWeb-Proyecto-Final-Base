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
    [$puntos, $medios, $mediosDePunto] = rec_contenido_publico((int)$escena['id']);
}
rec_cabecera($recorrido['titulo'], $recorrido['area']);
?>
<section class="contenedor seccion">

  <h1><?= e($recorrido['titulo']) ?></h1>
  <p class="leyenda"><?= e($recorrido['carrera']) ?></p>
  <?php if ($recorrido['descripcion'] !== ''): ?><p><?= e($recorrido['descripcion']) ?></p><?php endif; ?>

  <nav id="escenas" class="rec-navegacion" aria-label="Escenas del recorrido">
    <a class="boton boton--secundario" href="index.php">Inicio de recorridos</a>
    <?php foreach ($escenas as $opcion): ?>
      <?php $activa = $opcion['id'] === $escena['id']; ?>
      <a class="boton <?= $activa ? 'boton--primario' : 'boton--secundario' ?>" <?= $activa ? 'aria-current="page"' : '' ?>
         href="<?= e('ver.php?id=' . $id . '&escena=' . $opcion['id'] . '#escenas') ?>"><?= e($opcion['titulo']) ?></a>
    <?php endforeach; ?>
  </nav>
  <?php if ($escena === null): ?>
    <p class="vacio">Este recorrido todavía no tiene escenas disponibles.</p>
  <?php else: ?>
    <div class="rec-reproductor">
      <h2><?= e($escena['titulo']) ?></h2>
      <?php if ($escena['descripcion'] !== ''): ?><p><?= e($escena['descripcion']) ?></p><?php endif; ?>

      <?php if ($medios === []): ?><p>Esta escena todavía no tiene medios.</p><?php endif; ?>
      <?php $imagenMostrada = false; ?>
      <?php foreach ($medios as $medio): ?>
        <?php $src = 'medio.php?id=' . $medio['id']; ?>
        <?php if ($medio['tipo'] === 'imagen' && !$imagenMostrada): ?>
          <figure class="rec-imagen">
            <img src="<?= e($src) ?>" alt="<?= e('Imagen de la escena ' . $escena['titulo']) ?>">
          </figure>
          <?php $imagenMostrada = true; ?>
        <?php elseif ($medio['tipo'] === 'video'): ?>
          <video class="rec-video" src="<?= e($src) ?>" controls playsinline preload="metadata"
                 aria-label="<?= e('Video de ' . $escena['titulo']) ?>"></video>
        <?php elseif ($medio['tipo'] === 'audio'): ?>
          <audio class="rec-audio" src="<?= e($src) ?>" controls preload="none"
                 aria-label="<?= e('Narración de ' . $escena['titulo']) ?>"></audio>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if ($puntos !== []): ?>
        <section aria-labelledby="titulo-puntos">
          <h3 id="titulo-puntos">Puntos de interés</h3>
          <ol class="rec-lista-puntos">
            <?php foreach ($puntos as $indice => $punto): ?>
              <?php $clave = 'punto-' . $punto['id']; ?>
              <li>
                <button type="button" class="boton boton--secundario rec-abrir-punto"
                        data-abrir="<?= e($clave) ?>" aria-haspopup="dialog" aria-controls="<?= e($clave) ?>">
                  <span class="rec-numero"><?= e((string)($indice + 1)) ?></span>
                  <span><?= e($punto['titulo']) ?></span>
                </button>
              </li>
            <?php endforeach; ?>
          </ol>
        </section>
      <?php endif; ?>

      <?php foreach ($puntos as $punto): ?>
        <?php $clave = 'punto-' . $punto['id']; $galeria = $mediosDePunto[(int)$punto['id']] ?? []; ?>
        <dialog class="rec-dialogo" id="<?= e($clave) ?>" aria-labelledby="<?= e($clave . '-titulo') ?>">
          <div class="rec-dialogo__cabecera">
            <h3 id="<?= e($clave . '-titulo') ?>"><?= e($punto['titulo']) ?></h3>
            <form method="dialog"><button class="boton boton--secundario" autofocus>Cerrar</button></form>
          </div>
          <?php if ($punto['descripcion'] !== ''): ?><p class="rec-dialogo__descripcion"><?= e($punto['descripcion']) ?></p><?php endif; ?>
          <?php foreach ($galeria as $i => $archivo): ?>
            <div data-diapositiva <?= $i ? 'hidden' : '' ?>>
              <?php $src = 'medio.php?id=' . $archivo['id']; ?>
              <?php if ($archivo['tipo'] === 'imagen'): ?>
                <img src="<?= e($src) ?>" loading="lazy" alt="<?= e($punto['titulo'] . ' · Imagen ' . ($i + 1)) ?>">
              <?php elseif ($archivo['tipo'] === 'video'): ?>
                <video src="<?= e($src) ?>" preload="none" controls playsinline></video>
              <?php else: ?>
                <audio src="<?= e($src) ?>" preload="none" controls></audio>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if (count($galeria) > 1): ?>
            <nav class="rec-navegacion" aria-label="Archivos del punto">
              <button class="boton boton--secundario" data-paso="-1">Anterior</button>
              <span aria-live="polite" data-contador></span>
              <button class="boton boton--secundario" data-paso="1">Siguiente</button>
            </nav>
          <?php endif; ?>
        </dialog>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<script src="js/galeria.js" defer></script>
<?php pie(); ?>
