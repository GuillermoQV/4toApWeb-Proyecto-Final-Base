(() => {
  let origen;
  for (const tarjeta of document.querySelectorAll('[data-posicion]')) {
    tarjeta.addEventListener('dragstart', evento => {
      if (evento.target !== tarjeta) {
        evento.preventDefault();
        return;
      }
      origen = tarjeta;
      tarjeta.classList.add('rec-arrastrando');
      evento.dataTransfer.effectAllowed = 'move';
      evento.dataTransfer.setData('text/plain', tarjeta.dataset.posicion);
    });
    tarjeta.addEventListener('dragover', evento => {
      if (origen && origen !== tarjeta) {
        evento.preventDefault();
        evento.dataTransfer.dropEffect = 'move';
        tarjeta.classList.add('rec-destino');
      }
    });
    tarjeta.addEventListener('dragleave', evento => {
      if (!tarjeta.contains(evento.relatedTarget)) tarjeta.classList.remove('rec-destino');
    });
    tarjeta.addEventListener('dragend', () => {
      tarjeta.classList.remove('rec-arrastrando');
      origen = null;
      document.querySelectorAll('.rec-destino').forEach(el => el.classList.remove('rec-destino'));
    });
    tarjeta.addEventListener('drop', evento => {
      evento.preventDefault();
      if (!origen || origen === tarjeta) return;
      const formulario = origen.querySelector('.rec-orden');
      const destino = document.createElement('input');
      destino.type = 'hidden';
      destino.name = 'posicion';
      destino.value = tarjeta.dataset.posicion;
      formulario.append(destino);
      formulario.requestSubmit();
    });
  }
})();
