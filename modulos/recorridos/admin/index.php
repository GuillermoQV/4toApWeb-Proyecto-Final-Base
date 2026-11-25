<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$coordinacion = rol() === 'coordinacion';
$carreraId = carrera_del_usuario();
$puedeCrear = $coordinacion || $carreraId !== null;
[$recorridos, $pagina, $paginas] = rec_paginar($puedeCrear ? rec_listado($coordinacion ? null : $carreraId, false) : []);
rec_cabecera('Administrar recorridos');
?>
<p><a href="<?= e(RUTA_BASE . '/panel.php') ?>">Volver al panel</a></p>
<?php if ($puedeCrear): ?>
  <p><a class="boton boton--primario" href="editar.php">Nuevo recorrido</a></p>
<?php endif; ?>
<?php if (!$recorridos): ?><p class="vacio">Todavía no hay recorridos en tu panel.</p><?php endif; ?>
<div class="rejilla">
  <?php foreach ($recorridos as $fila): ?>
    <?php $id = (int)$fila['id']; ?>
    <article class="tarjeta tarjeta__cuerpo">
      <h2 class="tarjeta__titulo"><?= e($fila['titulo']) ?></h2>
      <p><?= e($fila['carrera']) ?> · <strong><?= e(ucfirst($fila['estado'])) ?></strong></p>
      <ul>
        <li><a href="editar.php?id=<?= $id ?>">Editar recorrido</a></li>
        <li><a href="escenas.php?recorrido_id=<?= $id ?>">Administrar escenas</a></li>
        <?php if ($fila['estado'] === 'publicado'): ?>
          <li><a href="../ver.php?id=<?= $id ?>">Ver recorrido público</a></li>
        <?php else: ?>
          <li><a href="eliminar.php?id=<?= $id ?>">Eliminar recorrido</a></li>
        <?php endif; ?>
      </ul>
      <?php if ($coordinacion): ?>
        <?php $accion = ['borrador' => 'Publicar', 'publicado' => 'Archivar', 'archivado' => 'Desarchivar'][$fila['estado']]; ?>
        <form method="post" action="estado.php">
          <?= campo_token() ?>
          <input type="hidden" name="id" value="<?= $id ?>">
          <button class="boton boton--primario" name="accion" value="<?= e(strtolower($accion)) ?>"><?= e($accion) ?></button>
        </form>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
<?php rec_paginador($pagina, $paginas); ?>
<?php rec_pie(); ?>
