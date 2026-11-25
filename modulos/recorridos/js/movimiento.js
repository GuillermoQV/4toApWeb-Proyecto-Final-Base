// La aparición de las tarjetas respeta la preferencia de movimiento reducido.
if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
  for (const elemento of document.querySelectorAll('.rejilla .tarjeta, .rec-reproductor')) {
    elemento.animate([{ opacity: .82 }, { opacity: 1 }], { duration: 180 });
  }
}
