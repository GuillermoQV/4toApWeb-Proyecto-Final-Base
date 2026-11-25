// El enlace sigue abriendo el archivo cuando JavaScript está desactivado.
const visor = document.getElementById('rec-visor');
const marco = visor.querySelector('.rec-visor__marco');
const zoom = visor.querySelector('.rec-zoom');
const [menos, mas] = zoom.querySelectorAll('[data-zoom]');
const niveles = [1, 1.5, 2, 3];
let nivel = 0;

function ajustarImagen() {
  const imagen = marco.querySelector('img');
  if (!visor.open || !imagen?.naturalWidth) return;
  const ajuste = Math.min(marco.clientWidth / imagen.naturalWidth, marco.clientHeight / imagen.naturalHeight);
  imagen.style.width = `${imagen.naturalWidth * ajuste * niveles[nivel]}px`;
  zoom.querySelector('output').textContent = `${niveles[nivel] * 100} %`;
  menos.disabled = nivel === 0;
  mas.disabled = nivel === niveles.length - 1;
}

for (const boton of [menos, mas]) {
  boton.addEventListener('click', () => {
    nivel += Number(boton.dataset.zoom);
    ajustarImagen();
  });
}
window.addEventListener('resize', ajustarImagen);
for (const enlace of document.querySelectorAll('[data-visor]')) {
  enlace.addEventListener('click', evento => {
    if (evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return;
    evento.preventDefault();
    const tipo = enlace.dataset.visor;
    const archivo = document.createElement({ imagen: 'img', video: 'video', audio: 'audio' }[tipo]);
    nivel = 0;
    menos.disabled = true;
    mas.disabled = true;
    zoom.hidden = tipo !== 'imagen';
    zoom.querySelector('output').textContent = '100 %';
    if (tipo === 'imagen') {
      archivo.alt = enlace.querySelector('img')?.alt || enlace.textContent.trim();
      archivo.addEventListener('load', ajustarImagen, { once: true });
    } else {
      archivo.controls = true;
      archivo.playsInline = true;
    }
    marco.replaceChildren(archivo);
    visor.showModal();
    archivo.src = enlace.href;
    marco.scrollTo(0, 0);
  });
}
visor.addEventListener('close', () => {
  marco.querySelector('video, audio')?.pause();
  marco.replaceChildren();
});
