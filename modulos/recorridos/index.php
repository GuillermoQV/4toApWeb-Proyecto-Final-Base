<?php
declare(strict_types=1);
require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/recorridos.php';

$filtro = $_GET['carrera'] ?? '';
$carreraId = rec_id($filtro);
$valido = $filtro === '' || ($carreraId !== null && carrera($carreraId));
[$recorridos, $pagina, $paginas] = rec_paginar($valido ? rec_listado($carreraId) : []);
rec_cabecera('Recorridos virtuales');
?>
<form class="panel fila rec-filtro" method="get">
  <?php
  $opciones = ['' => 'Todas las carreras'] + array_column(carreras(), 'nombre', 'id');
  rec_selector('carrera', 'Carrera', $opciones, $carreraId);
  ?>
  <button class="boton boton--primario">Filtrar</button>
</form>
<?php if (!$valido): ?>
  <p class="alerta alerta--error" role="alert">Selecciona una carrera del listado.</p>
<?php elseif (!$recorridos): ?>
  <p class="vacio">Todavía no hay recorridos publicados para esta selección.</p>
<?php endif; ?>
<div class="rejilla">
  <?php foreach ($recorridos as $fila): ?>
    <article class="tarjeta tarjeta__cuerpo">
      <p class="leyenda"><?= e($fila['carrera']) ?></p>
      <h2 class="tarjeta__titulo"><?= e($fila['titulo']) ?></h2>
      <p><?= e($fila['descripcion']) ?></p>
      <a class="boton boton--primario" href="ver.php?id=<?= (int)$fila['id'] ?>">Abrir recorrido</a>
    </article>
  <?php endforeach; ?>
</div>
<?php rec_paginador($pagina, $paginas, $carreraId); ?>
<?php rec_pie(); ?>
