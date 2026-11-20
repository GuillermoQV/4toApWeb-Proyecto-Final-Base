<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/escenas.php';

$escena = array_key_exists('id', $_GET) ? rec_escena_admin(rec_id($_GET['id'])) : null;
$recorrido = rec_recorrido_admin($escena === null ? rec_id($_GET['recorrido_id'] ?? null) : (int)$escena['recorrido_id']);
$url = 'escenas.php?recorrido_id=' . $recorrido['id'];
$titulo = $escena['titulo'] ?? '';
$descripcion = $escena['descripcion'] ?? '';
$ids = array_map('intval', array_column(rec_escenas_admin((int)$recorrido['id']), 'id'));
$totalPosiciones = count($ids) + ($escena === null ? 1 : 0);
$orden = (string)($escena === null ? $totalPosiciones : array_search((int)$escena['id'], $ids, true) + 1);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    $titulo = is_string($_POST['titulo'] ?? null) ? post('titulo') : '';
    $descripcion = is_string($_POST['descripcion'] ?? null) ? post('descripcion') : '';
    $orden = is_string($_POST['orden'] ?? null) ? post('orden') : '';
    $ordenId = entero($orden);
    if ($titulo === '' || mb_strlen($titulo) > 160 || mb_strlen($descripcion) > 8000
        || $ordenId === null || $ordenId > $totalPosiciones) {
        $error = 'Escribe un título válido, una descripción de hasta 8000 caracteres y una posición disponible.';
    } else {
        $db = bd();
        $db->beginTransaction();
        try {
            $recorridoId = (int)$recorrido['id'];
            rec_recorrido_admin($recorridoId, true);
            if ($escena === null) {
                $q = $db->prepare('INSERT INTO rec_escenas (recorrido_id,titulo,descripcion,orden) VALUES (?,?,?,?)');
                $q->execute([$recorridoId, $titulo, $descripcion, $ordenId]);
                $escenaId = (int)$db->lastInsertId();
            } else {
                $escenaId = (int)$escena['id'];
                $q = $db->prepare('UPDATE rec_escenas SET titulo=?, descripcion=? WHERE id=? AND recorrido_id=?');
                $q->execute([$titulo, $descripcion, $escenaId, $recorridoId]);
            }
            rec_renumerar($recorridoId, $escenaId, $ordenId);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            mensaje('error', 'No se pudo guardar la escena. Recarga la lista e inténtalo de nuevo.');
            redirigir($url);
        }
        mensaje('exito', 'Escena guardada.');
        redirigir($url);
    }
}

rec_cabecera($escena === null ? 'Nueva escena' : 'Editar escena');
?>
<section class="contenedor seccion rec-formulario">
  <p><a href="<?= e($url) ?>">Volver a escenas</a></p>
  <h1><?= $escena === null ? 'Nueva escena' : 'Editar escena' ?></h1>
  <?php if ($error !== ''): ?><p class="alerta alerta--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="panel">
    <?= campo_token() ?>
    <div class="campo">
      <label class="campo__etiqueta" for="titulo">Título</label>
      <input class="campo__control" id="titulo" name="titulo" value="<?= e($titulo) ?>" maxlength="160" required>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="descripcion">Descripción</label>
      <textarea class="campo__control" id="descripcion" name="descripcion" maxlength="8000"><?= e($descripcion) ?></textarea>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="orden">Posición en el recorrido</label>
      <select class="campo__control" id="orden" name="orden" style="width: min(5rem, 100%)" required>
        <?php for ($posicion = 1; $posicion <= $totalPosiciones; $posicion++): ?>
          <option value="<?= e((string)$posicion) ?>"<?= (string)$posicion === $orden ? ' selected' : '' ?>><?= e((string)$posicion) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button class="boton boton--primario" type="submit">Guardar escena</button>
  </form>
</section>
<?php pie(); ?>
