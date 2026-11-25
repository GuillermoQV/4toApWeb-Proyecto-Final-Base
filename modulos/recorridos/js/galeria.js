// Cada botón abre el diálogo de un punto. El carrusel avanza por pantallas.
for (const boton of document.querySelectorAll('[data-abrir]')) {
  const dialogo = document.getElementById(boton.dataset.abrir);
  const carrusel = dialogo.querySelector('.rec-carrusel');
  const archivos = carrusel.children;
  const contador = dialogo.querySelector('[data-contador]');
  let actual = 0;

  function indicar(indice) {
    if (indice !== actual) archivos[actual]?.querySelector('video, audio')?.pause();
    actual = indice;
    if (contador) contador.textContent = `${actual + 1} de ${archivos.length}`;
  }

  boton.addEventListener('click', () => {
    dialogo.showModal();
    carrusel.scrollLeft = 0;
    indicar(0);
  });
  dialogo.addEventListener('close', () => {
    dialogo.querySelectorAll('video, audio').forEach(medio => medio.pause());
    boton.focus();
  });
  if (archivos.length < 2) continue;

  carrusel.addEventListener('scroll', () => {
    if (!carrusel.clientWidth) return;
    const indice = Math.round(carrusel.scrollLeft / carrusel.clientWidth);
    indicar(Math.max(0, Math.min(indice, archivos.length - 1)));
  }, { passive: true });

  function mover(paso) {
    const destino = (actual + paso + archivos.length) % archivos.length;
    carrusel.scrollLeft = destino * carrusel.clientWidth;
    indicar(destino);
  }
  for (const control of dialogo.querySelectorAll('[data-paso]')) {
    control.addEventListener('click', () => mover(Number(control.dataset.paso)));
  }
  dialogo.addEventListener('keydown', evento => {
    if (evento.target.matches('video, audio')) return;
    if (evento.key === 'ArrowLeft' || evento.key === 'ArrowRight') {
      evento.preventDefault();
      mover(evento.key === 'ArrowRight' ? 1 : -1);
    }
  });
}
