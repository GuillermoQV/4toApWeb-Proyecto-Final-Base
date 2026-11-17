<?php

declare(strict_types=1);

function rec_cabecera(string $titulo, string $area = ''): void
{
    $rutaModulo = RUTA_BASE . '/modulos/recorridos';
    cabecera($titulo, $area, $rutaModulo . '/css/modulo.css');
    ?>
<div class="contenedor rec-tema" hidden>
  <button class="boton boton--secundario" type="button" aria-pressed="false">Modo oscuro</button>
</div>
<script src="<?= e($rutaModulo . '/js/tema.js') ?>"></script>
<?php
}
