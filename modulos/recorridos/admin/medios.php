<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$escena = rec_escena_admin(rec_id($_GET['escena_id'] ?? null));
$id = (int)$escena['id'];
$eliminar = isset($_POST['eliminar']);

rec_post('medios.php?escena_id=' . $id, function () use ($escena, $eliminar): void {
    if ($eliminar) {
        rec_eliminar_medio($escena, rec_id($_POST['eliminar']));
    } else {
        rec_subir_medio($escena, $_POST['tipo'] ?? '', $_POST['punto_id'] ?? '');
    }
}, $eliminar ? 'Medio eliminado.' : 'Medio guardado.');

rec_cabecera('Medios de ' . $escena['titulo']);
?>
<p><a href="escenas.php?recorrido_id=<?= (int)$escena['recorrido_id'] ?>">Volver a escenas</a></p>
<form class="panel rec-formulario" method="post" enctype="multipart/form-data">
  <?= campo_token() ?>
  <?php
  rec_selector('tipo', 'Tipo', ['imagen' => 'Imagen JPEG, PNG o WebP (5 MB)', 'video' => 'Video MP4 (30 MB)']);
  $opciones = ['' => 'Escena completa'] + array_column(rec_puntos($id), 'titulo', 'id');
  rec_selector('punto_id', 'Ubicación', $opciones);
  ?>
  <label class="campo__etiqueta" for="archivo">Archivo</label>
  <input class="campo__control" id="archivo" name="archivo" type="file" accept="image/jpeg,image/png,image/webp,video/mp4" required>
  <p>La escena admite una imagen principal. Los puntos admiten varias imágenes y videos.</p>
  <button class="boton boton--primario">Subir medio</button>
</form>
<h2>Archivos de esta escena</h2>
<?php $medios = rec_medios_de_escena($id); ?>
<?php if (!$medios): ?><p>La escena aún no tiene medios.</p><?php endif; ?>
<ul>
  <?php foreach ($medios as $medio): ?>
    <li class="fila">
      <a href="../medio.php?id=<?= (int)$medio['id'] ?>" data-visor="<?= e($medio['tipo']) ?>"><?= e(ucfirst($medio['tipo'])) ?> · <?= e($medio['punto'] ?? 'Escena completa') ?></a>
      <form method="post">
        <?= campo_token() ?>
        <button class="boton boton--peligro boton--pequeno" name="eliminar" value="<?= (int)$medio['id'] ?>">Eliminar medio</button>
      </form>
    </li>
  <?php endforeach; ?>
</ul>
<?php require __DIR__ . '/../includes/visor.php'; ?>
<?php rec_pie(); ?>
