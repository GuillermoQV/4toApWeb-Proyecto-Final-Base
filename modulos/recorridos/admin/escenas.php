<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/escenas.php';

$recorrido = rec_recorrido_admin(rec_id($_GET['recorrido_id'] ?? null));
$recorridoId = (int)$recorrido['id'];
$escenas = rec_escenas_admin($recorridoId);

rec_cabecera('Escenas del recorrido');
?>
<section class="contenedor seccion">
  <p><a href="index.php">Volver a recorridos</a></p>
  <h1>Escenas de <?= e($recorrido['titulo']) ?></h1>
  <?php pintar_mensajes(); ?>
  <p><a class="boton boton--primario" href="escena-editar.php?recorrido_id=<?= e((string)$recorridoId) ?>">Nueva escena</a></p>
  <p>El número de orden indica qué escena aparece primero en el recorrido.</p>
  <?php if ($escenas === []): ?>
    <p class="vacio">Este recorrido todavía no tiene escenas.</p>
  <?php else: ?>
    <div class="rejilla">
      <?php foreach ($escenas as $escena): ?>
        <?php $id = e((string)$escena['id']); ?>
        <article class="tarjeta">
          <div class="tarjeta__cuerpo">
            <h2 class="tarjeta__titulo"><?= e($escena['titulo']) ?></h2>
            <p>Orden <?= e((string)$escena['orden']) ?></p>
            <?php if ($escena['descripcion'] !== ''): ?><p><?= e($escena['descripcion']) ?></p><?php endif; ?>
            <ul>
              <li><a href="escena-editar.php?id=<?= $id ?>">Editar y cambiar el orden</a></li>
              <li><a href="puntos.php?escena_id=<?= $id ?>">Puntos de interés</a></li>
              <li><a href="medios.php?escena_id=<?= $id ?>">Cargar medios</a></li>
              <?php if ($recorrido['estado'] !== 'publicado'): ?>
                <li><a href="escena-eliminar.php?id=<?= $id ?>">Eliminar escena</a></li>
              <?php endif; ?>
            </ul>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php pie(); ?>
