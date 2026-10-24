/**
 * Comportamentos essenciais do site (viniciuscodes)
 * 1. Alternador de tema claro / escuro
 * 2. Voltar ao topo
 * 3. Compartilhamento nativo e cópia de link
 */
(function () {
	'use strict';

	var raiz = document.documentElement;

	/* ---------------------------------------------------------------- tema */
	var botaoDoTema = document.querySelector('[data-theme-toggle]');

	var anunciarTema = function () {
		var temaAtual = raiz.dataset.theme;
		if (botaoDoTema) {
			botaoDoTema.setAttribute('aria-checked', temaAtual === 'dark' ? 'true' : 'false');
		}

		var notByAiImg = document.querySelector('.not-by-ai-img');
		if (notByAiImg) {
			var novoSrc = temaAtual === 'dark'
				? notByAiImg.getAttribute('data-dark-src')
				: notByAiImg.getAttribute('data-light-src');
			if (novoSrc && notByAiImg.getAttribute('src') !== novoSrc) {
				notByAiImg.setAttribute('src', novoSrc);
			}
		}
	};

	if (botaoDoTema) {
		anunciarTema();

		if (window.matchMedia) {
			var mediaQueryEscuro = window.matchMedia('(prefers-color-scheme: dark)');
			var atualizarTemaDoSistema = function (e) {
				var escolhaSalva = null;
				try {
					escolhaSalva = localStorage.getItem('vco-theme');
				} catch (err) {}

				if (!escolhaSalva) {
					raiz.dataset.theme = e.matches ? 'dark' : 'light';
					anunciarTema();
				}
			};

			if (typeof mediaQueryEscuro.addEventListener === 'function') {
				mediaQueryEscuro.addEventListener('change', atualizarTemaDoSistema);
			} else if (typeof mediaQueryEscuro.addListener === 'function') {
				mediaQueryEscuro.addListener(atualizarTemaDoSistema);
			}
		}

		botaoDoTema.addEventListener('click', function () {
			var proximo = raiz.dataset.theme === 'dark' ? 'light' : 'dark';
			raiz.dataset.theme = proximo;
			anunciarTema();
			try {
				localStorage.setItem('vco-theme', proximo);
			} catch (e) {}
		});
	}



	/* --------------------------------------------------------- compartilhar */
	var blocoDeCompartilhar = document.querySelector('[data-post-share]');

	if (blocoDeCompartilhar) {
		var urlDoTexto = blocoDeCompartilhar.getAttribute('data-url') || window.location.href;
		var tituloDoTexto = blocoDeCompartilhar.getAttribute('data-title') || document.title;
		var areaDeStatus = blocoDeCompartilhar.querySelector('[data-share-status]');
		var botaoNativo = blocoDeCompartilhar.querySelector('[data-share-native]');
		var botaoCopiar = blocoDeCompartilhar.querySelector('[data-share-copy]');
		var relogioDoAviso = 0;

		var avisar = function (mensagem) {
			if (!areaDeStatus) {
				return;
			}
			areaDeStatus.textContent = mensagem;
			window.clearTimeout(relogioDoAviso);
			relogioDoAviso = window.setTimeout(function () {
				areaDeStatus.textContent = '';
			}, 4000);
		};

		if (botaoNativo && typeof navigator.share === 'function') {
			botaoNativo.hidden = false;
			botaoNativo.addEventListener('click', function () {
				navigator.share({ title: tituloDoTexto, url: urlDoTexto }).catch(function (erro) {
					if (erro && erro.name !== 'AbortError') {
						avisar('não foi possível compartilhar');
					}
				});
			});
		}

		if (botaoCopiar) {
			botaoCopiar.hidden = false;
			botaoCopiar.addEventListener('click', function () {
				if (!navigator.clipboard) {
					avisar('não deu para copiar o link');
					return;
				}
				navigator.clipboard.writeText(urlDoTexto).then(
					function () {
						avisar('link copiado!');
					},
					function () {
						avisar('não deu para copiar o link');
					}
				);
			});
		}
	}
})();
