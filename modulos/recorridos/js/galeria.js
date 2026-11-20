for (const abrir of document.querySelectorAll('[data-abrir]')) {
  const ventana = document.getElementById(abrir.dataset.abrir);
  const pista = ventana.querySelector('.rec-carrusel');
  const medios = [...ventana.querySelectorAll('[data-diapositiva]')];
  const puntos = [...ventana.querySelectorAll('[data-indice]')];
  const contador = ventana.querySelector('[data-contador]');
  const reducido = matchMedia('(prefers-reduced-motion: reduce)');
  let actual = 0;

  function indicar(indice) {
    medios[actual]?.querySelector('video, audio')?.pause();
    actual = indice;
    puntos.forEach((punto, i) => punto.setAttribute('aria-current', String(i === indice)));
    if (contador) contador.textContent = (indice + 1) + ' / ' + medios.length;
  }

  function ir(indice) {
    const destino = (indice + medios.length) % medios.length;
    pista.scrollTo({left: destino * pista.clientWidth, behavior: reducido.matches ? 'instant' : 'smooth'});
  }

  abrir.addEventListener('click', () => {
    ventana.showModal();
    if (pista) pista.scrollLeft = 0;
    indicar(0);
  });
  ventana.addEventListener('close', () => {
    ventana.querySelectorAll('video, audio').forEach(medio => medio.pause());
    abrir.focus();
  });
  if (medios.length < 2) continue;
  pista.addEventListener('scroll', () => {
    if (!pista.clientWidth) return;
    const indice = Math.max(0, Math.min(medios.length - 1, Math.round(pista.scrollLeft / pista.clientWidth)));
    if (actual !== indice) indicar(indice);
  }, {passive: true});
  for (const boton of ventana.querySelectorAll('[data-paso]')) {
    boton.addEventListener('click', () => ir(actual + Number(boton.dataset.paso)));
  }
  puntos.forEach((punto, i) => punto.addEventListener('click', () => ir(i)));
  ventana.addEventListener('keydown', evento => {
    if (evento.target.matches('video, audio')) return;
    if (evento.key === 'ArrowRight' || evento.key === 'ArrowLeft') {
      evento.preventDefault();
      ir(actual + (evento.key === 'ArrowRight' ? 1 : -1));
    }
  });
}
