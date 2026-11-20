<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../includes/paginacion.php';
require_once __DIR__ . '/../includes/recorridos.php';

$esCoordinacion = rol() === 'coordinacion';
$carreraId = carrera_del_usuario();
$recorridos = $esCoordinacion || $carreraId !== null
    ? rec_recorridos_admin($esCoordinacion ? null : $carreraId)
    : [];

[$recorridos, $pagina, $paginas] = rec_paginar($recorridos);

rec_cabecera('Administrar recorridos');
?>
<section class="contenedor seccion">
  <p><a href="<?= e(RUTA_BASE . '/panel.php') ?>">Volver al panel</a></p>
  <h1>Administrar recorridos</h1>
  <?php pintar_mensajes(); ?>
  <?php if ($esCoordinacion || $carreraId !== null): ?>
    <p><a class="boton boton--primario" href="editar.php">Nuevo recorrido</a></p>
  <?php endif; ?>
  <?php if ($recorridos === []): ?>
    <p class="vacio">Todavía no hay recorridos en tu panel.</p>
  <?php else: ?>
    <div class="rejilla rec-administracion">
      <?php foreach ($recorridos as $fila): ?>
        <?php $id = e((string)$fila['id']); ?>
        <article class="tarjeta">
          <div class="tarjeta__cuerpo">
            <h2 class="tarjeta__titulo"><?= e($fila['titulo']) ?></h2>
            <p><?= e($fila['carrera']) ?> · <span class="insignia insignia--<?= e($fila['estado']) ?>"><?= e(ucfirst($fila['estado'])) ?></span></p>
            <ul class="rec-enlaces-panel">
              <li><a href="editar.php?id=<?= $id ?>">Editar recorrido</a></li>
              <li><a href="escenas.php?recorrido_id=<?= $id ?>">Administrar escenas</a></li>
              <?php if ($fila['estado'] === 'publicado'): ?>
                <li><a href="../ver.php?id=<?= $id ?>">Ver recorrido público</a></li>
              <?php else: ?>
                <li><a class="rec-eliminar" href="eliminar.php?id=<?= $id ?>">Eliminar recorrido</a></li>
              <?php endif; ?>
            </ul>
            <?php if ($esCoordinacion): ?>
              <form method="post" action="estado.php">
                <?= campo_token() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <?php if ($fila['estado'] === 'borrador'): ?>
                  <button class="boton boton--primario boton--pequeno" name="accion" value="publicar">Publicar</button>
                <?php elseif ($fila['estado'] === 'publicado'): ?>
                  <button class="boton boton--secundario boton--pequeno" name="accion" value="archivar">Archivar</button>
                <?php else: ?>
                  <button class="boton boton--secundario boton--pequeno" name="accion" value="desarchivar">Desarchivar</button>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php rec_paginador($pagina, $paginas); ?>
  <?php endif; ?>
</section>
<?php pie(); ?>
