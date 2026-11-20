(() => {
  const visor = document.getElementById('rec-visor');
  const marco = visor.querySelector('.rec-visor__marco');
  const zoom = visor.querySelector('.rec-zoom');
  const [menos, mas] = zoom.querySelectorAll('button');
  const niveles = [1, 1.5, 2, 3];
  let nivel = 0;

  function ajustar() {
    const foto = marco.querySelector('img');
    if (!foto || !foto.naturalWidth || !visor.open) return;
    const ajuste = Math.min(marco.clientWidth / foto.naturalWidth, marco.clientHeight / foto.naturalHeight);
    const escala = ajuste * niveles[nivel];
    foto.style.width = Math.max(1, Math.floor(foto.naturalWidth * escala)) + 'px';
    foto.style.height = Math.max(1, Math.floor(foto.naturalHeight * escala)) + 'px';
    zoom.querySelector('output').textContent = Math.round(niveles[nivel] * 100) + ' %';
    menos.disabled = nivel === 0;
    mas.disabled = nivel === niveles.length - 1;
  }
  new ResizeObserver(ajustar).observe(marco);
  for (const boton of [menos, mas]) {
    boton.addEventListener('click', () => {
      nivel += Number(boton.dataset.zoom);
      ajustar();
      marco.scrollLeft = (marco.scrollWidth - marco.clientWidth) / 2;
      marco.scrollTop = (marco.scrollHeight - marco.clientHeight) / 2;
    });
  }
  for (const enlace of document.querySelectorAll('[data-visor]')) {
    enlace.addEventListener('click', evento => {
      if (evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return;
      evento.preventDefault();
      const tipo = enlace.dataset.visor;
      const archivo = document.createElement({imagen: 'img', video: 'video', audio: 'audio'}[tipo]);
      nivel = 0;
      zoom.hidden = tipo !== 'imagen';
      if (tipo === 'imagen') {
        archivo.alt = enlace.querySelector('img')?.alt || enlace.textContent.trim();
        archivo.addEventListener('load', ajustar, {once: true});
        menos.disabled = true;
        mas.disabled = true;
        zoom.querySelector('output').textContent = '100 %';
      } else {
        archivo.controls = true;
        archivo.playsInline = true;
      }
      archivo.src = enlace.href;
      marco.replaceChildren(archivo);
      visor.showModal();
      marco.scrollTo(0, 0);
    });
  }
  visor.addEventListener('close', () => {
    marco.querySelector('audio, video')?.pause();
    marco.replaceChildren();
  });
})();
