(() => {
  const clave = 'ev.recorridos.tema';
  const controles = document.querySelector('.rec-tema');
  const boton = controles.querySelector('button');

  function aplicarTema(oscuro) {
    document.documentElement.toggleAttribute('data-rec-oscuro', oscuro);
    boton.setAttribute('aria-pressed', String(oscuro));
  }

  function cargarTema() {
    try {
      aplicarTema(localStorage.getItem(clave) === 'oscuro');
    } catch {
      // El botón sigue disponible si el navegador bloquea el almacenamiento.
    }
  }

  cargarTema();
  controles.hidden = false;

  boton.addEventListener('click', () => {
    const oscuro = boton.getAttribute('aria-pressed') !== 'true';
    aplicarTema(oscuro);
    try {
      localStorage.setItem(clave, oscuro ? 'oscuro' : 'claro');
    } catch {
      // La preferencia se aplica a la página actual.
    }
  });

  window.addEventListener('pageshow', (evento) => {
    if (evento.persisted) cargarTema();
  });
})();
