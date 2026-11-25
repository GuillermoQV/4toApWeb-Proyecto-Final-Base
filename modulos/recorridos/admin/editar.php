<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$recorrido = isset($_GET['id']) ? rec_recorrido_admin(rec_id($_GET['id'])) : null;
$eligeCarrera = !$recorrido && rol() === 'coordinacion';
$carreraId = $eligeCarrera ? rec_id($_POST['carrera_id'] ?? null) : carrera_del_usuario();
[$titulo, $descripcion] = rec_textos($recorrido);

rec_post('index.php', function () use ($recorrido, $carreraId, $titulo, $descripcion): void {
    rec_validar_textos($titulo, $descripcion);
    if ($recorrido) {
        rec_consulta('UPDATE rec_recorridos SET titulo = ?, descripcion = ? WHERE id = ?',
            [$titulo, $descripcion, $recorrido['id']]);
        return;
    }
    if ($carreraId === null || carrera($carreraId) === null) {
        throw new DomainException('Selecciona una carrera activa.');
    }
    requerir_carrera($carreraId);
    rec_consulta('INSERT INTO rec_recorridos (carrera_id, titulo, descripcion) VALUES (?, ?, ?)',
        [$carreraId, $titulo, $descripcion]);
}, 'Recorrido guardado.');

rec_cabecera($recorrido ? 'Editar recorrido' : 'Nuevo recorrido');
?>
<p><a href="index.php">Volver a recorridos</a></p>
<form method="post" class="panel rec-formulario">
  <?= campo_token() ?>
  <?php if ($eligeCarrera) {
      $opciones = ['' => 'Selecciona una carrera'] + array_column(carreras(), 'nombre', 'id');
      rec_selector('carrera_id', 'Carrera', $opciones, $carreraId, true);
  } ?>
  <?php rec_campos_texto($titulo, $descripcion); ?>
  <button class="boton boton--primario">Guardar recorrido</button>
</form>
<?php rec_pie(); ?>
