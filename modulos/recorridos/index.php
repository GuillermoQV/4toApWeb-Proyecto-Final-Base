<?php

declare(strict_types=1);

require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/plantilla.php';
require_once __DIR__ . '/includes/publico.php';

$filtro = $_GET['carrera'] ?? '';
$id = is_string($filtro) ? entero($filtro) : null;
$invalido = $filtro !== '' && ($id === null || carrera($id) === null);
$recorridos = $invalido ? [] : rec_listado_publico($id);

rec_cabecera('Recorridos virtuales');
?>
<section class="contenedor seccion">
  <h1>Recorridos virtuales</h1>
  <form class="panel rec-filtro" method="get">
    <div class="campo">
      <label class="campo__etiqueta" for="carrera">Carrera</label>
      <select class="campo__control" name="carrera" id="carrera">
        <option value="">Todas las carreras</option>
        <?php foreach (carreras() as $carrera): ?>
          <option value="<?= e((string)$carrera['id']) ?>"<?= (int)$carrera['id'] === $id ? ' selected' : '' ?>><?= e($carrera['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="boton boton--primario" type="submit">Filtrar</button>
  </form>

  <?php if ($invalido): ?>
    <p class="alerta alerta--error" role="alert">Selecciona una carrera del listado.</p>
  <?php elseif ($recorridos === []): ?>
    <p class="vacio">Todavía no hay recorridos publicados<?= $id === null ? '.' : ' para esta carrera.' ?></p>
  <?php else: ?>
    <div class="rejilla rec-resultados">
      <?php foreach ($recorridos as $recorrido): ?>
        <article class="tarjeta">
          <div class="tarjeta__cuerpo">
            <p class="leyenda"><?= e($recorrido['carrera']) ?></p>
            <h2 class="tarjeta__titulo"><?= e($recorrido['titulo']) ?></h2>
            <?php if ($recorrido['descripcion'] !== ''): ?><p><?= e($recorrido['descripcion']) ?></p><?php endif; ?>
          </div>
          <div class="tarjeta__pie">
            <a class="boton boton--secundario" href="ver.php?id=<?= e((string)$recorrido['id']) ?>">Abrir recorrido</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php pie(); ?>
