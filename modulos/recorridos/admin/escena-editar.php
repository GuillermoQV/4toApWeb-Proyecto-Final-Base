<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/escenas.php';

$escena = array_key_exists('id', $_GET) ? rec_escena_admin(rec_id($_GET['id'])) : null;
$recorrido = rec_recorrido_admin($escena === null ? rec_id($_GET['recorrido_id'] ?? null) : (int)$escena['recorrido_id']);
$url = 'escenas.php?recorrido_id=' . $recorrido['id'];
$titulo = $escena['titulo'] ?? '';
$descripcion = $escena['descripcion'] ?? '';
$orden = (string)($escena['orden'] ?? '1');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    $titulo = is_string($_POST['titulo'] ?? null) ? post('titulo') : '';
    $descripcion = is_string($_POST['descripcion'] ?? null) ? post('descripcion') : '';
    $orden = is_string($_POST['orden'] ?? null) ? post('orden') : '';
    $ordenId = entero($orden);
    if ($titulo === '' || mb_strlen($titulo) > 160 || mb_strlen($descripcion) > 8000
        || $ordenId === null || $ordenId > 4294967295) {
        $error = 'Escribe un título válido, una descripción de hasta 8000 caracteres y un número de orden positivo.';
    } else {
        if ($escena === null) {
            $consulta = bd()->prepare('INSERT INTO rec_escenas (recorrido_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)');
            $consulta->execute([$recorrido['id'], $titulo, $descripcion, $ordenId]);
        } else {
            $consulta = bd()->prepare('UPDATE rec_escenas SET titulo = ?, descripcion = ?, orden = ? WHERE id = ? AND recorrido_id = ?');
            $consulta->execute([$titulo, $descripcion, $ordenId, $escena['id'], $recorrido['id']]);
        }
        mensaje('exito', 'Escena guardada.');
        redirigir($url);
    }
}

cabecera($escena === null ? 'Nueva escena' : 'Editar escena');
?>
<section class="contenedor seccion">
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
      <input class="campo__control" id="orden" name="orden" type="number" min="1" max="4294967295" value="<?= e($orden) ?>" required>
    </div>
    <button class="boton boton--primario" type="submit">Guardar escena</button>
  </form>
</section>
<?php pie(); ?>
