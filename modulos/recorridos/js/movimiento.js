(() => {
  if (!('IntersectionObserver' in window)) return;

  const elementos = document.querySelectorAll(
    '.rec-resultados > .tarjeta, .rec-administracion > .tarjeta, .rec-reproductor'
  );
  if (elementos.length === 0) return;

  const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)');
  const observador = new IntersectionObserver((entradas) => {
    let turno = 0;

    for (const { isIntersecting, target } of entradas) {
      if (!isIntersecting) continue;

      observador.unobserve(target);
      if (movimientoReducido.matches || target.contains(document.activeElement)) continue;

      if (target.classList.contains('tarjeta')) {
        const retraso = Math.min(turno, 3) * 40;
        target.style.setProperty('--rec-retraso-entrada', retraso + 'ms');
        turno += 1;
      }
      target.classList.add('rec-entrada');
    }
  });

  function terminarEntrada(evento) {
    if (evento.animationName !== 'rec-aparecer') return;
    evento.target.classList.remove('rec-entrada');
    evento.target.style.removeProperty('--rec-retraso-entrada');
  }

  document.addEventListener('animationend', terminarEntrada);
  document.addEventListener('animationcancel', terminarEntrada);
  elementos.forEach(elemento => observador.observe(elemento));
})();
