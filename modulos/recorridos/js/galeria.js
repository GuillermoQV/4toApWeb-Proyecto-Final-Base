for (const abrir of document.querySelectorAll('[data-abrir]')) {
  const ventana = document.getElementById(abrir.dataset.abrir);
  const medios = [...ventana.querySelectorAll('[data-diapositiva]')];
  const contador = ventana.querySelector('[data-contador]');
  let actual = 0;

  function mostrar(indice) {
    if (!medios.length) return;
    medios[actual].querySelector('video, audio')?.pause();
    medios[actual].hidden = true;
    actual = (indice + medios.length) % medios.length;
    medios[actual].hidden = false;
    if (contador) contador.textContent = (actual + 1) + ' / ' + medios.length;
  }
  abrir.addEventListener('click', () => {
    mostrar(0);
    ventana.showModal();
  });
  ventana.addEventListener('close', () => {
    ventana.querySelectorAll('video, audio').forEach(medio => medio.pause());
    abrir.focus();
  });
  for (const boton of ventana.querySelectorAll('[data-paso]')) {
    boton.addEventListener('click', () => mostrar(actual + Number(boton.dataset.paso)));
  }
}
