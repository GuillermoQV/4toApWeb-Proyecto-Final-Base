<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$escena = rec_escena_admin(rec_id($_GET['escena_id'] ?? null));
$escenaId = (int)$escena['id'];
$url = 'puntos.php?escena_id=' . $escenaId;
$punto = isset($_GET['id']) ? rec_punto_admin(rec_id($_GET['id']), $escenaId) : null;
[$titulo, $descripcion] = rec_textos($punto);
$accion = $_POST['accion'] ?? '';

rec_post($url, function () use ($accion, $punto, $escena, $escenaId, $titulo, $descripcion): void {
    if ($accion === 'eliminar' && $punto) {
        rec_eliminar_con_medios((int)$escena['recorrido_id'], 'punto', (int)$punto['id']);
        return;
    }
    if ($accion !== 'guardar') {
        throw new DomainException('Elige una acción válida.');
    }
    rec_validar_textos($titulo, $descripcion);
    if ($punto) {
        rec_consulta('UPDATE rec_puntos SET titulo = ?, descripcion = ? WHERE id = ? AND escena_id = ?',
            [$titulo, $descripcion, $punto['id'], $escenaId]);
    } else {
        // Las coordenadas se guardan para conservar el esquema del módulo.
        rec_consulta('INSERT INTO rec_puntos (escena_id, titulo, descripcion, x, y) VALUES (?, ?, ?, 50, 50)',
            [$escenaId, $titulo, $descripcion]);
    }
}, $accion === 'eliminar' ? 'Punto eliminado.' : 'Punto guardado.');

rec_cabecera('Puntos de interés de ' . $escena['titulo']);
?>
<p><a href="escenas.php?recorrido_id=<?= (int)$escena['recorrido_id'] ?>">Volver a escenas</a></p>
<ul>
  <?php foreach (rec_puntos($escenaId) as $fila): ?>
    <li><a href="<?= e($url . '&id=' . (int)$fila['id']) ?>"><?= e($fila['titulo']) ?></a></li>
  <?php endforeach; ?>
</ul>
<h2><?= $punto ? 'Editar punto' : 'Nuevo punto' ?></h2>
<form class="panel rec-formulario" method="post">
  <?= campo_token() ?>
  <?php rec_campos_texto($titulo, $descripcion); ?>
  <button class="boton boton--primario" name="accion" value="guardar">Guardar punto</button>
</form>
<?php if ($punto): ?>
  <form class="fila" method="post">
    <?= campo_token() ?>
    <button class="boton boton--peligro" name="accion" value="eliminar"
            onclick="return confirm('¿Eliminar este punto y sus imágenes y videos?')">Eliminar punto</button>
    <a href="<?= e($url) ?>">Nuevo punto</a>
  </form>
<?php endif; ?>
<?php rec_pie(); ?>
