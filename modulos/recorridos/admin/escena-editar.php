<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$escena = isset($_GET['id']) ? rec_escena_admin(rec_id($_GET['id'])) : null;
$recorridoId = (int)($escena['recorrido_id'] ?? rec_recorrido_admin(rec_id($_GET['recorrido_id'] ?? null))['id']);
$url = 'escenas.php?recorrido_id=' . $recorridoId;
[$titulo, $descripcion] = rec_textos($escena);
$escenaId = $escena ? (int)$escena['id'] : null;
$ids = array_map('intval', array_column(rec_escenas($recorridoId), 'id'));
$total = count($ids) + ($escenaId === null ? 1 : 0);
$posicion = array_search($escenaId, $ids, true);
$orden = rec_es_post() ? rec_id($_POST['orden'] ?? null) : ($posicion === false ? $total : $posicion + 1);

rec_post($url, function () use ($recorridoId, $escenaId, $titulo, $descripcion, $orden, $total): void {
    rec_validar_textos($titulo, $descripcion);
    if ($orden === null || $orden > $total) {
        throw new DomainException('Elige una posición disponible.');
    }
    rec_guardar_escena($recorridoId, $escenaId, $titulo, $descripcion, $orden);
}, 'Escena guardada.');

rec_cabecera($escena ? 'Editar escena' : 'Nueva escena');
?>
<p><a href="<?= e($url) ?>">Volver a escenas</a></p>
<form method="post" class="panel rec-formulario">
  <?= campo_token() ?>
  <?php rec_campos_texto($titulo, $descripcion); ?>
  <label class="campo__etiqueta" for="orden">Posición en el recorrido</label>
  <select class="campo__control rec-posicion" id="orden" name="orden" required>
    <?php for ($i = 1; $i <= $total; $i++): ?>
      <option value="<?= $i ?>" <?= $i === $orden ? 'selected' : '' ?>><?= $i ?></option>
    <?php endfor; ?>
  </select>
  <button class="boton boton--primario">Guardar escena</button>
</form>
<?php rec_pie(); ?>
