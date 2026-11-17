<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../includes/recorridos.php';
require_once __DIR__ . '/../includes/medios.php';

$id = rec_id($_GET['id'] ?? null);
$recorrido = rec_recorrido_admin($id);
if ($recorrido['estado'] === 'publicado') {
    mensaje('error', 'Archiva el recorrido antes de eliminarlo.');
    redirigir('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rec_token_post();
    $bd = bd();
    $bd->beginTransaction();
    try {
        $recorrido = rec_recorrido_admin($id, true);
        if ($recorrido['estado'] === 'publicado') {
            $bd->rollBack();
            mensaje('error', 'Archiva el recorrido antes de eliminarlo.');
            redirigir('index.php');
        }

        $consulta = $bd->prepare(
            'SELECT m.nombre_archivo FROM rec_escenas e
             LEFT JOIN rec_medios m ON e.id = m.escena_id
             WHERE e.recorrido_id = ? FOR UPDATE'
        );
        $consulta->execute([$id]);
        $archivos = $consulta->fetchAll(PDO::FETCH_COLUMN);
        $consulta = $bd->prepare('DELETE FROM rec_recorridos WHERE id = ?');
        $consulta->execute([$id]);
        $bd->commit();
    } catch (Throwable $error) {
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        error_log('REC: ' . $error->getMessage());
        mensaje('error', 'No se pudo eliminar el recorrido.');
        redirigir('index.php');
    }

    foreach ($archivos as $nombre) {
        if ($nombre === null) {
            continue;
        }
        try {
            rec_limpiar_medio($nombre);
        } catch (RuntimeException $error) {
            error_log('REC: ' . $error->getMessage());
        }
    }
    mensaje('exito', 'Recorrido eliminado.');
    redirigir('index.php');
}

rec_cabecera('Eliminar recorrido');
?>
<section class="contenedor seccion">
  <h1>Eliminar recorrido</h1>
  <p>Confirma la baja de «<?= e($recorrido['titulo']) ?>». También se eliminarán sus escenas, puntos y medios.</p>
  <form method="post">
    <?= campo_token() ?>
    <button class="boton boton--peligro" type="submit">Eliminar</button>
    <a class="boton boton--secundario" href="index.php">Cancelar</a>
  </form>
</section>
<?php pie(); ?>
