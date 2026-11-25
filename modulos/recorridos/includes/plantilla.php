<?php
declare(strict_types=1);

function rec_cabecera(string $titulo, string $area = '', string $id = 'rec-contenido'): void
{
    $ruta = RUTA_BASE . '/modulos/recorridos';
    cabecera($titulo, $area, $ruta . '/css/modulo.css');
    ?>
    <script defer src="<?= e($ruta . '/js/menu.js') ?>"></script>
    <script defer src="<?= e($ruta . '/js/movimiento.js') ?>"></script>
    <section class="contenedor seccion rec-pantalla" id="<?= e($id) ?>">
      <h1><?= e($titulo) ?></h1>
      <?php pintar_mensajes();
}

function rec_pie(): void
{
    echo '</section>';
    pie();
}

function rec_campos_texto(string $titulo, string $descripcion): void
{
    ?>
    <label class="campo__etiqueta" for="titulo">Título</label>
    <input class="campo__control" id="titulo" name="titulo" value="<?= e($titulo) ?>" maxlength="160" required>
    <label class="campo__etiqueta" for="descripcion">Descripción</label>
    <textarea class="campo__control" id="descripcion" name="descripcion" maxlength="8000" rows="4"><?= e($descripcion) ?></textarea>
    <?php
}

// Una opción tiene un id y el nombre que se muestra.
function rec_selector(string $campo, string $etiqueta, array $opciones, ?int $actual = null, bool $obligatorio = false): void
{
    ?>
    <label class="campo__etiqueta" for="<?= e($campo) ?>"><?= e($etiqueta) ?></label>
    <select class="campo__control" id="<?= e($campo) ?>" name="<?= e($campo) ?>" <?= $obligatorio ? 'required' : '' ?>>
      <?php foreach ($opciones as $id => $nombre): ?>
        <option value="<?= e((string)$id) ?>" <?= $id === $actual ? 'selected' : '' ?>><?= e($nombre) ?></option>
      <?php endforeach; ?>
    </select>
    <?php
}

// Recorrido y escena comparten el mismo formulario de confirmación.
function rec_confirmar_baja(array $fila, string $tipo, string $volver, string $incluye): void
{
    if ($fila['estado'] === 'publicado') {
        mensaje('error', 'Archiva el recorrido antes de continuar.');
        redirigir($volver);
    }
    $id = (int)$fila['id'];
    $recorridoId = (int)($fila['recorrido_id'] ?? $id);
    $exito = $tipo === 'escena' ? 'Escena eliminada.' : 'Recorrido eliminado.';
    rec_post($volver, fn () => rec_eliminar_con_medios($recorridoId, $tipo, $id), $exito);
    rec_cabecera('Eliminar ' . $tipo);
    ?>
    <p>Confirma la baja de «<?= e($fila['titulo']) ?>» y de sus <?= e($incluye) ?>.</p>
    <form method="post" class="fila">
      <?= campo_token() ?>
      <button class="boton boton--peligro">Eliminar</button>
      <a class="boton boton--secundario" href="<?= e($volver) ?>">Cancelar</a>
    </form>
    <?php
    rec_pie();
}
