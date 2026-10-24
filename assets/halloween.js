/**
 * Efeitos animados de Halloween leves e temporários.
 * Morcegos sutis e fantasminhas flutuando discretamente pelo fundo.
 */
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const container = document.createElement('div');
  container.id = 'halloween-floating-layer';
  container.setAttribute('aria-hidden', 'true');
  container.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9999;overflow:hidden;';
  document.body.appendChild(container);

  const emojis = ['🦇', '👻', '🕷️', '🦇'];

  function spawnCreature() {
    if (document.hidden) return;
    const el = document.createElement('span');
    const emoji = emojis[Math.floor(Math.random() * emojis.length)];
    el.textContent = emoji;

    // Tamanho e opacidade suaves
    const size = 18 + Math.random() * 16;
    const startY = 15 + Math.random() * 70; // altura relativa da tela (%)
    const duration = 12 + Math.random() * 10; // entre 12s e 22s
    const fromLeft = Math.random() > 0.5;

    el.style.cssText = `
      position: absolute;
      top: ${startY}vh;
      ${fromLeft ? 'left: -50px;' : 'right: -50px;'}
      font-size: ${size}px;
      opacity: 0.65;
      user-select: none;
      filter: drop-shadow(0 2px 5px rgba(0,0,0,0.25));
      transition: transform ${duration}s linear, top ${duration}s ease-in-out;
      will-change: transform;
    `;

    container.appendChild(el);

    // Inicia o voo após renderizar
    requestAnimationFrame(() => {
      const targetX = fromLeft ? window.innerWidth + 100 : -(window.innerWidth + 100);
      const targetY = (Math.random() - 0.5) * 120; // leve ondulação vertical
      const scaleX = fromLeft ? 1 : -1;
      el.style.transform = `translate3d(${targetX}px, ${targetY}px, 0) scaleX(${scaleX})`;
    });

    // Remove do DOM após a travessia
    setTimeout(() => {
      el.remove();
    }, duration * 1000);
  }

  // Primeiro spawn logo no início
  setTimeout(spawnCreature, 1200);

  // Intervalo calmo para não poluir nem pesar a CPU (a cada 6 a 9 segundos)
  setInterval(() => {
    if (container.childElementCount < 3) {
      spawnCreature();
    }
  }, 7000);
})();