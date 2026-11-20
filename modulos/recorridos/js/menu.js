// Usa los mismos enlaces del encabezado que proporciona el núcleo.
(() => {
  const nav = document.querySelector('.encabezado__barra > nav');
  if (!nav) return;

  const compacto = matchMedia('(max-width: 64rem)');
  const boton = document.createElement('button');
  boton.type = 'button';
  boton.className = 'boton boton--secundario rec-menu';
  boton.textContent = 'Menú';
  nav.id = 'rec-menu-principal';
  boton.setAttribute('aria-controls', nav.id);
  boton.setAttribute('aria-expanded', 'false');
  nav.before(boton);

  boton.addEventListener('click', () => {
    boton.setAttribute('aria-expanded', String(boton.getAttribute('aria-expanded') === 'false'));
  });
  nav.addEventListener('keydown', evento => {
    if (evento.key === 'Escape' && compacto.matches) {
      boton.setAttribute('aria-expanded', 'false');
      boton.focus();
    }
  });
  compacto.addEventListener('change', () => {
    boton.setAttribute('aria-expanded', 'false');
    if (compacto.matches && nav.contains(document.activeElement)) boton.focus();
    if (!compacto.matches && document.activeElement === boton) nav.querySelector('a')?.focus();
  });
})();
