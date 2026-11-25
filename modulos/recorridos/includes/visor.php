<dialog id="rec-visor" class="panel rec-dialogo rec-visor" aria-label="Visor de medios">
  <div class="fila fila--separada">
    <div class="fila rec-zoom" hidden>
      <button class="boton boton--secundario" type="button" data-zoom="-1" aria-label="Reducir imagen">−</button>
      <output aria-live="polite">100 %</output>
      <button class="boton boton--secundario" type="button" data-zoom="1" aria-label="Ampliar imagen">+</button>
    </div>
    <form method="dialog"><button class="boton boton--secundario" autofocus>Cerrar</button></form>
  </div>
  <div class="rec-visor__marco"></div>
</dialog>
<script src="<?= e(RUTA_BASE . '/modulos/recorridos/js/visor.js') ?>" defer></script>
