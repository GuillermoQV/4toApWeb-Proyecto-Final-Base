<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$recorrido = rec_recorrido_admin(rec_id($_GET['recorrido_id'] ?? null));
$recorridoId = (int)$recorrido['id'];
$id = rec_id($_POST['mover'] ?? null);
$posicion = rec_id($_POST['posicion'] ?? null);

rec_post('escenas.php?recorrido_id=' . $recorridoId . '#escena-' . $id, function () use ($recorridoId, $id, $posicion): void {
    if ($id === null || $posicion === null) {
        throw new DomainException('Elige una posición válida.');
    }
    rec_transaccion($recorridoId, fn () => rec_renumerar($recorridoId, $id, $posicion));
}, 'Orden actualizado.');

$escenas = rec_escenas($recorridoId);
rec_cabecera('Escenas de ' . $recorrido['titulo']);
?>
<p><a href="index.php">Volver a recorridos</a></p>
<p><a class="boton boton--primario" href="escena-editar.php?recorrido_id=<?= $recorridoId ?>">Nueva escena</a></p>
<?php if (!$escenas): ?><p class="vacio">Este recorrido todavía no tiene escenas.</p><?php endif; ?>
<div class="rejilla">
  <?php foreach ($escenas as $i => $escena): ?>
    <?php $id = (int)$escena['id']; ?>
    <article class="tarjeta tarjeta__cuerpo" id="escena-<?= $id ?>" draggable="true" data-posicion="<?= $i + 1 ?>">
      <h2 class="tarjeta__titulo"><?= $i + 1 ?>. <?= e($escena['titulo']) ?></h2>
      <p><?= e($escena['descripcion']) ?></p>
      <form class="fila" method="post">
        <?= campo_token() ?>
        <input type="hidden" name="mover" value="<?= $id ?>">
        <input type="hidden" name="posicion" disabled>
        <button class="boton boton--secundario boton--pequeno" name="posicion" value="<?= $i ?>" <?= $i === 0 ? 'disabled' : '' ?>>Subir</button>
        <button class="boton boton--secundario boton--pequeno" name="posicion" value="<?= $i + 2 ?>" <?= $i + 1 === count($escenas) ? 'disabled' : '' ?>>Bajar</button>
      </form>
      <ul>
        <li><a href="escena-editar.php?id=<?= $id ?>">Editar escena</a></li>
        <li><a href="puntos.php?escena_id=<?= $id ?>">Puntos de interés</a></li>
        <li><a href="medios.php?escena_id=<?= $id ?>">Cargar medios</a></li>
        <?php if ($recorrido['estado'] !== 'publicado'): ?>
          <li><a href="escena-eliminar.php?id=<?= $id ?>">Eliminar escena</a></li>
        <?php endif; ?>
      </ul>
    </article>
  <?php endforeach; ?>
</div>
<script src="../js/orden.js" defer></script>
<?php rec_pie(); ?>
