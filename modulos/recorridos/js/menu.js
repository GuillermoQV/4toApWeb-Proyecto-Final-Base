// El menú compacto usa los mismos enlaces del encabezado de la plataforma.
const navegacion = document.querySelector('.encabezado__barra > nav');
navegacion.id = 'rec-menu-principal';
navegacion.insertAdjacentHTML('beforebegin', `
  <button type="button" class="boton boton--secundario rec-menu" aria-controls="rec-menu-principal" aria-expanded="false">Menú</button>
`);
const botonMenu = navegacion.previousElementSibling;

botonMenu.addEventListener('click', () => {
  const abierto = botonMenu.getAttribute('aria-expanded') === 'true';
  botonMenu.setAttribute('aria-expanded', String(!abierto));
});
navegacion.addEventListener('keydown', evento => {
  if (evento.key === 'Escape' && matchMedia('(max-width: 64rem)').matches) {
    botonMenu.setAttribute('aria-expanded', 'false');
    botonMenu.focus();
  }
});
