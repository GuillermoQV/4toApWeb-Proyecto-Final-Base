// El arrastre envía el mismo formulario que usan Subir y Bajar.
let origen = null;
for (const tarjeta of document.querySelectorAll('[data-posicion]')) {
  tarjeta.addEventListener('dragstart', evento => {
    origen = tarjeta;
    evento.dataTransfer.setData('text/plain', tarjeta.id);
  });
  tarjeta.addEventListener('dragend', () => { origen = null; });
  tarjeta.addEventListener('dragover', evento => {
    if (origen && origen !== tarjeta) evento.preventDefault();
  });
  tarjeta.addEventListener('drop', evento => {
    evento.preventDefault();
    if (!origen || origen === tarjeta) return;
    const formulario = origen.querySelector('form');
    const destino = formulario.querySelector('input[name="posicion"]');
    destino.value = tarjeta.dataset.posicion;
    destino.disabled = false;
    formulario.requestSubmit();
  });
}
