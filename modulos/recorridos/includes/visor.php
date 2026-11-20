<dialog id="rec-visor" class="rec-visor" aria-label="Visor de medios">
  <div class="rec-visor__barra">
    <div class="rec-zoom" hidden>
      <button type="button" data-zoom="-1" aria-label="Reducir imagen">−</button>
      <output aria-live="polite">100 %</output>
      <button type="button" data-zoom="1" aria-label="Ampliar imagen">+</button>
    </div>
    <form method="dialog"><button autofocus>Cerrar</button></form>
  </div>
  <div class="rec-visor__marco"></div>
</dialog>
<script src="<?= e(RUTA_BASE . '/modulos/recorridos/js/visor.js?v=' . filemtime(__DIR__ . '/../js/visor.js')) ?>" defer></script>
