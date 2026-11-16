<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$recorrido = array_key_exists('id', $_GET) ? rec_recorrido_admin(rec_id($_GET['id'])) : null;
$coordinacion = rol() === 'coordinacion';
$carreraId = $recorrido === null ? carrera_del_usuario() : (int)$recorrido['carrera_id'];
$titulo = $recorrido['titulo'] ?? '';
$descripcion = $recorrido['descripcion'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    $titulo = is_string($_POST['titulo'] ?? null) ? post('titulo') : '';
    $descripcion = is_string($_POST['descripcion'] ?? null) ? post('descripcion') : '';
    if ($recorrido === null && $coordinacion) {
        $carreraId = rec_id($_POST['carrera_id'] ?? null);
    }
    if ($titulo === '' || mb_strlen($titulo) > 160 || mb_strlen($descripcion) > 8000) {
        $error = 'Escribe un título de hasta 160 caracteres y una descripción de hasta 8000.';
    } elseif ($carreraId === null || ($recorrido === null && carrera($carreraId) === null)) {
        $error = 'Selecciona una carrera activa.';
    } else {
        requerir_carrera($carreraId);
        if ($recorrido === null) {
            $consulta = bd()->prepare('INSERT INTO rec_recorridos (carrera_id, titulo, descripcion) VALUES (?, ?, ?)');
            $consulta->execute([$carreraId, $titulo, $descripcion]);
        } else {
            $consulta = bd()->prepare('UPDATE rec_recorridos SET titulo = ?, descripcion = ? WHERE id = ? AND carrera_id = ?');
            $consulta->execute([$titulo, $descripcion, $recorrido['id'], $carreraId]);
        }
        mensaje('exito', 'Recorrido guardado.');
        redirigir('index.php');
    }
}

cabecera($recorrido === null ? 'Nuevo recorrido' : 'Editar recorrido');
?>
<section class="contenedor seccion">
  <p><a href="index.php">Volver a recorridos</a></p>
  <h1><?= $recorrido === null ? 'Nuevo recorrido' : 'Editar recorrido' ?></h1>
  <?php if ($error !== ''): ?><p class="alerta alerta--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form class="panel" method="post">
    <?= campo_token() ?>
    <?php if ($recorrido === null && $coordinacion): ?>
      <div class="campo">
        <label class="campo__etiqueta" for="carrera_id">Carrera</label>
        <select class="campo__control" id="carrera_id" name="carrera_id" required>
          <option value="">Selecciona una carrera</option>
          <?php foreach (carreras() as $carrera): ?>
            <option value="<?= e((string)$carrera['id']) ?>"<?= (int)$carrera['id'] === $carreraId ? ' selected' : '' ?>><?= e($carrera['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
    <div class="campo">
      <label class="campo__etiqueta" for="titulo">Título</label>
      <input class="campo__control" id="titulo" name="titulo" value="<?= e($titulo) ?>" maxlength="160" required>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="descripcion">Descripción</label>
      <textarea class="campo__control" id="descripcion" name="descripcion" maxlength="8000"><?= e($descripcion) ?></textarea>
    </div>
    <button class="boton boton--primario" type="submit">Guardar recorrido</button>
  </form>
</section>
<?php pie(); ?>
