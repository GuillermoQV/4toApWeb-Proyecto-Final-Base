<?php
declare(strict_types=1);
require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/recorridos.php';

$recorrido = rec_recorrido_publico(rec_id($_GET['id'] ?? null));
$id = (int)$recorrido['id'];
$escenas = rec_escenas($id);
$posicion = 0;

// Buscamos el id pedido dentro de las escenas de este recorrido.
if (isset($_GET['escena'])) {
    $ids = array_map('intval', array_column($escenas, 'id'));
    $posicion = array_search(rec_id($_GET['escena']), $ids, true);
    if ($posicion === false) {
        no_encontrado('Escena no disponible en este recorrido.');
    }
}
$escena = $escenas[$posicion] ?? null;
rec_cabecera($escena['titulo'] ?? $recorrido['titulo'], $recorrido['area'], 'escenas');
?>
<p><a href="index.php">Inicio de recorridos</a></p>
<p class="leyenda"><?= e($recorrido['titulo']) ?> · <?= e($recorrido['carrera']) ?></p>
<?php if (!$escena): ?>
  <p class="vacio">Este recorrido todavía no tiene escenas disponibles.</p>
<?php else: ?>
  <?php
  $escenaId = (int)$escena['id'];
  $archivos = rec_archivos_por_punto($escenaId);
  $puntos = rec_puntos($escenaId);
  ?>
  <p><?= e($escena['descripcion']) ?></p>
  <nav class="fila rec-escena-nav" aria-label="Escenas del recorrido">
    <?php rec_enlace_escena($escenas[$posicion - 1] ?? null, $id, 'Anterior'); ?>
    <form class="fila" method="get" action="ver.php#escenas">
      <input type="hidden" name="id" value="<?= $id ?>">
      <?php rec_selector('escena', 'Escena ' . ($posicion + 1) . ' de ' . count($escenas), array_column($escenas, 'titulo', 'id'), $escenaId); ?>
      <button class="boton boton--primario">Ir a escena</button>
    </form>
    <?php rec_enlace_escena($escenas[$posicion + 1] ?? null, $id, 'Siguiente'); ?>
  </nav>
  <div class="panel rec-reproductor">
    <?php if (empty($archivos[0])): ?><p>Esta escena todavía no tiene medios principales.</p><?php endif; ?>
    <?php foreach ($archivos[0] ?? [] as $medio) {
        rec_mostrar_medio($medio, $escena['titulo']);
    } ?>
  </div>
  <?php if ($puntos): ?>
    <h2>Puntos de interés</h2>
    <div class="fila">
      <?php foreach ($puntos as $i => $punto): ?>
        <?php
        $clave = 'punto-' . (int)$punto['id'];
        $medios = $archivos[$punto['id']] ?? [];
        ?>
        <button class="boton boton--secundario" type="button" data-abrir="<?= e($clave) ?>" aria-haspopup="dialog"><?= $i + 1 ?>. <?= e($punto['titulo']) ?></button>
        <dialog class="panel rec-dialogo" id="<?= e($clave) ?>" aria-labelledby="<?= e($clave . '-titulo') ?>">
          <div class="fila fila--separada">
            <h2 id="<?= e($clave . '-titulo') ?>"><?= e($punto['titulo']) ?></h2>
            <form method="dialog"><button class="boton boton--secundario" autofocus>Cerrar</button></form>
          </div>
          <p><?= e($punto['descripcion']) ?></p>
          <div class="rec-carrusel">
            <?php foreach ($medios as $medio): ?>
              <div><?php rec_mostrar_medio($medio, $punto['titulo']); ?></div>
            <?php endforeach; ?>
          </div>
          <?php if (count($medios) > 1): ?>
            <div class="fila" aria-label="Archivos del punto">
              <button class="boton boton--secundario" type="button" data-paso="-1">Anterior</button>
              <span data-contador aria-live="polite"></span>
              <button class="boton boton--secundario" type="button" data-paso="1">Siguiente</button>
            </div>
          <?php endif; ?>
        </dialog>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/visor.php'; ?>
<script src="js/galeria.js" defer></script>
<?php rec_pie(); ?>
