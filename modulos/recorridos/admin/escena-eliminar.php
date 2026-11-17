<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/escenas.php';
require_once __DIR__ . '/../includes/medios.php';

$escena = rec_escena_admin(rec_id($_GET['id'] ?? null));
$url = 'escenas.php?recorrido_id=' . $escena['recorrido_id'];
if ($escena['estado'] === 'publicado') {
    mensaje('error', 'Archiva el recorrido antes de eliminar una escena.');
    redirigir($url);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    try {
        rec_baja_escena_o_punto($escena, null);
        mensaje('exito', 'Escena eliminada.');
    } catch (DomainException $error) {
        mensaje('error', $error->getMessage());
    } catch (Throwable $error) {
        error_log('REC: ' . $error->getMessage());
        mensaje('error', 'No se pudo eliminar la escena.');
    }
    redirigir($url);
}

rec_cabecera('Eliminar escena');
?>
<section class="contenedor seccion">
  <h1>Eliminar escena</h1>
  <p>Confirma la baja de «<?= e($escena['titulo']) ?>» y de sus puntos y medios.</p>
  <form method="post">
    <?= campo_token() ?>
    <button class="boton boton--peligro" type="submit">Eliminar</button>
    <a class="boton boton--secundario" href="<?= e($url) ?>">Cancelar</a>
  </form>
</section>
<?php pie(); ?>
