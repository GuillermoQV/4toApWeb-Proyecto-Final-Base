<?php

declare(strict_types=1);

function rec_cabecera(string $titulo, string $area = ''): void
{
    $rutaModulo = RUTA_BASE . '/modulos/recorridos';
    cabecera($titulo, $area, $rutaModulo . '/css/modulo.css');
    ?>
<div class="contenedor rec-tema" hidden>
  <button class="boton boton--secundario rec-tema__boton" type="button" aria-label="Modo oscuro" aria-pressed="false">
    <span class="rec-tema__icono rec-tema__sol" aria-hidden="true"></span>
    <span class="rec-tema__icono rec-tema__luna" aria-hidden="true"></span>
  </button>
</div>
<script src="<?= e($rutaModulo . '/js/tema.js') ?>"></script>
<script src="<?= e($rutaModulo . '/js/movimiento.js') ?>" defer></script>
<?php
}
