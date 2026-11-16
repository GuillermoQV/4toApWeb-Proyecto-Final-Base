<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/escenas.php';
require_once __DIR__ . '/../includes/medios.php';

$escena = rec_escena_admin(rec_id($_GET['escena_id'] ?? null));
$escenaId = (int)$escena['id'];
$url = 'puntos.php?escena_id=' . $escenaId;
$punto = array_key_exists('id', $_GET) ? rec_punto_admin(rec_id($_GET['id']), $escenaId) : null;
$datos = $punto ?? ['titulo' => '', 'descripcion' => '', 'x' => '50', 'y' => '50'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    $accion = $_POST['accion'] ?? null;
    if ($accion === 'eliminar' && $punto !== null) {
        try {
            rec_baja_escena_o_punto($escena, (int)$punto['id']);
            mensaje('exito', 'Punto eliminado.');
        } catch (DomainException $fallo) {
            mensaje('error', $fallo->getMessage());
        } catch (Throwable $fallo) {
            error_log('REC: ' . $fallo->getMessage());
            mensaje('error', 'No se pudo eliminar el punto.');
        }
        redirigir($url);
    }
    if ($accion !== 'guardar') {
        http_response_code(400);
        exit('Acción no válida.');
    }
    foreach (['titulo', 'descripcion', 'x', 'y'] as $campo) {
        $datos[$campo] = is_string($_POST[$campo] ?? null) ? post($campo) : '';
    }
    $x = $datos['x'];
    $y = $datos['y'];
    if ($datos['titulo'] === '' || mb_strlen($datos['titulo']) > 160 || mb_strlen($datos['descripcion']) > 8000
        || !is_numeric($x) || !is_numeric($y) || $x < 0 || $x > 100 || $y < 0 || $y > 100) {
        $error = 'Escribe un título válido y dos coordenadas entre 0 y 100. La descripción admite hasta 8000 caracteres.';
    } else {
        $valores = [$datos['titulo'], $datos['descripcion'], (float)$x, (float)$y, $escenaId];
        if ($punto === null) {
            $consulta = bd()->prepare('INSERT INTO rec_puntos (titulo, descripcion, x, y, escena_id) VALUES (?, ?, ?, ?, ?)');
        } else {
            $consulta = bd()->prepare('UPDATE rec_puntos SET titulo = ?, descripcion = ?, x = ?, y = ? WHERE escena_id = ? AND id = ?');
            $valores[] = $punto['id'];
        }
        $consulta->execute($valores);
        mensaje('exito', 'Punto guardado.');
        redirigir($url);
    }
}

$puntos = rec_puntos_admin($escenaId);
cabecera('Puntos de interés');
?>
<section class="contenedor seccion">
  <p><a href="escenas.php?recorrido_id=<?= e((string)$escena['recorrido_id']) ?>">Volver a escenas</a></p>
  <h1>Puntos de interés de <?= e($escena['titulo']) ?></h1>
  <?php pintar_mensajes(); ?>
  <?php if ($puntos === []): ?>
    <p>Esta escena todavía no tiene puntos.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($puntos as $fila): ?>
        <li><a href="<?= e($url . '&id=' . $fila['id']) ?>"><?= e($fila['titulo']) ?></a> (<?= e((string)$fila['x']) ?>%, <?= e((string)$fila['y']) ?>%)</li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <h2><?= $punto === null ? 'Nuevo punto' : 'Editar punto' ?></h2>
  <?php if ($error !== ''): ?><p class="alerta alerta--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="panel">
    <?= campo_token() ?>
    <div class="campo">
      <label class="campo__etiqueta" for="titulo">Título</label>
      <input class="campo__control" id="titulo" name="titulo" value="<?= e($datos['titulo']) ?>" maxlength="160" required>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="descripcion">Descripción</label>
      <textarea class="campo__control" id="descripcion" name="descripcion" maxlength="8000"><?= e($datos['descripcion']) ?></textarea>
    </div>
    <p>La posición se mide desde la esquina superior izquierda de la imagen.</p>
    <?php foreach (['x' => 'Horizontal', 'y' => 'Vertical'] as $eje => $etiqueta): ?>
      <div class="campo">
        <label class="campo__etiqueta" for="<?= e($eje) ?>"><?= e($etiqueta) ?> (%)</label>
        <input class="campo__control" id="<?= e($eje) ?>" name="<?= e($eje) ?>" type="number"
               min="0" max="100" step="0.01" value="<?= e((string)$datos[$eje]) ?>" required>
      </div>
    <?php endforeach; ?>
    <button class="boton boton--primario" name="accion" value="guardar">Guardar punto</button>
    <?php if ($punto !== null): ?><a class="boton boton--secundario" href="<?= e($url) ?>">Nuevo punto</a><?php endif; ?>
  </form>
  <?php if ($punto !== null): ?>
    <details class="mt-6">
      <summary>Eliminar este punto</summary>
      <form method="post">
        <?= campo_token() ?>
        <p>También se eliminarán las imágenes asociadas al punto.</p>
        <button class="boton boton--peligro" name="accion" value="eliminar">Confirmar eliminación</button>
      </form>
    </details>
  <?php endif; ?>
</section>
<?php pie(); ?>
