/**
 * Scroll Infinito da Home com Progressive Enhancement (IntersectionObserver).
 *
 * 100% amigável para SEO:
 * - O HTML inicial vem com a paginação regular do WordPress (`<a rel="next">`).
 * - Se o leitor rolar até próximo do final, o script busca silenciosamente a próxima página.
 * - Os novos itens são inseridos com efeito de fade-in.
 * - A URL do navegador é sincronizada com `window.history.replaceState` (/page/2/, etc.).
 * - O botão de curtir das novas notas é inicializado dinamicamente.
 */
(function () {
	'use strict';

	var feedLista = document.querySelector('#fluxo .post-list');
	var feedNav = document.querySelector('[data-feed-nav]');
	var sentinela = document.querySelector('[data-feed-sentinel]');

	if (!feedLista || !feedNav || !sentinela || !('IntersectionObserver' in window)) {
		return;
	}

	var carregando = false;
	var proximaUrl = null;

	var obterProximoLink = function (elementoNav) {
		if (!elementoNav) {
			return null;
		}
		var link = elementoNav.querySelector('a.paginacao__botao[rel="next"]') ||
		           elementoNav.querySelector('a[rel="next"]');
		return link ? link.getAttribute('href') : null;
	};

	proximaUrl = obterProximoLink(feedNav);

	if (!proximaUrl) {
		return;
	}

	// Oculta a barra de paginação clássica para humanos no navegador (fica transparente/invisível para visual, mantida no DOM para fallback)
	feedNav.style.display = 'none';

	var indicadorCarregando = document.createElement('div');
	indicadorCarregando.className = 'feed-carregando';
	indicadorCarregando.textContent = 'carregando mais publicações…';
	indicadorCarregando.style.display = 'none';
	sentinela.parentNode.insertBefore(indicadorCarregando, sentinela);

	var inicializarCurtidaItem = function (item) {
		var btn = item.querySelector('[data-post-like]');
		if (!btn) {
			return;
		}

		var postId = btn.getAttribute('data-post-like');
		if (!postId) {
			return;
		}

		var jaCurtiu = false;
		try {
			jaCurtiu = '1' === localStorage.getItem('vco-like-' + postId);
		} catch (e) {}

		if (jaCurtiu) {
			btn.classList.add('post-like__btn--liked');
		}

		btn.addEventListener('click', function () {
			if (btn.classList.contains('post-like__btn--liked')) {
				return;
			}

			btn.classList.add('post-like__btn--liked');
			try {
				localStorage.setItem('vco-like-' + postId, '1');
			} catch (e) {}

			fetch('/wp-json/vinicius/v1/curtir/' + postId, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' }
			})
				.then(function (r) { return r.json(); })
				.then(function (dados) {
					if (dados && dados.curtidas) {
						var countSpan = btn.querySelector('.post-like__count');
						if (!countSpan) {
							countSpan = document.createElement('span');
							countSpan.className = 'post-like__count';
							btn.appendChild(countSpan);
						}
						countSpan.textContent = '(' + dados.curtidas + ')';
					}
				})
				.catch(function () {});
		});
	};

	var observer = new IntersectionObserver(function (entries) {
		if (entries[0].isIntersecting && !carregando && proximaUrl) {
			carregando = true;
			indicadorCarregando.style.display = 'flex';

			fetch(proximaUrl)
				.then(function (res) {
					return res.text();
				})
				.then(function (html) {
					var parser = new DOMParser();
					var doc = parser.parseFromString(html, 'text/html');
					var novosItens = doc.querySelectorAll('#fluxo .post-list > li');

					if (novosItens && novosItens.length > 0) {
						novosItens.forEach(function (item) {
							item.classList.add('post-item--animado');
							feedLista.appendChild(item);
							inicializarCurtidaItem(item);
						});

						// Atualiza a URL na barra de endereços (/page/2/, etc.)
						try {
							window.history.replaceState(null, '', proximaUrl);
						} catch (e) {}

						var novoNav = doc.querySelector('[data-feed-nav]');
						proximaUrl = obterProximoLink(novoNav);

						if (!proximaUrl) {
							observer.disconnect();
							indicadorCarregando.remove();
						}
					} else {
						observer.disconnect();
						indicadorCarregando.remove();
					}
				})
				.catch(function () {
					// Em caso de falha de conexão, reexibe a paginação tradicional
					feedNav.style.display = '';
					indicadorCarregando.remove();
					observer.disconnect();
				})
				.finally(function () {
					carregando = false;
					if (indicadorCarregando) {
						indicadorCarregando.style.display = 'none';
					}
				});
		}
	}, {
		rootMargin: '300px 0px'
	});

	observer.observe(sentinela);
})();
