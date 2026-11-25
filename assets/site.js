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
			botaoDoTema.setAttribute('aria-label', temaAtual === 'dark' ? 'tema claro' : 'tema escuro');
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
					escolhaSalva = localStorage.getItem('vco-theme-override');
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
				var temaSistema = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
				if (proximo === temaSistema) {
					localStorage.removeItem('vco-theme-override');
				} else {
					localStorage.setItem('vco-theme-override', proximo);
				}
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
					avisar('não deu para copiar o endereço');
					return;
				}
				navigator.clipboard.writeText(urlDoTexto).then(
					function () {
						avisar('endereço copiado!');
					},
					function () {
						avisar('não deu para copiar o endereço');
					}
				);
			});
		}
	}

		/* ----------------------------------------------------------- curtidas / upvote */
	var upvoteButtons = document.querySelectorAll('[data-upvote-id]');

	upvoteButtons.forEach(function (btn) {
		var postId = btn.getAttribute('data-upvote-id');
		if (!postId) {
			return;
		}

		var labelEl = btn.querySelector('.upvote-label');
		var jaCurtiu = false;
		try {
			jaCurtiu = '1' === localStorage.getItem('vco-upvote-' + postId);
		} catch (e) {}

		if (jaCurtiu) {
			btn.classList.add('upvoted');
			if (labelEl) {
				labelEl.textContent = 'curtido';
			}
		}

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			if (btn.classList.contains('upvoted')) {
				return;
			}

			btn.classList.add('upvoted');
			if (labelEl) {
				labelEl.textContent = 'curtido';
			}

			try {
				localStorage.setItem('vco-upvote-' + postId, '1');
			} catch (e) {}

			fetch('/wp-json/vinicius/v1/curtir/' + postId, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (dados) {
					if (dados && typeof dados.curtidas !== 'undefined') {
						var countEl = btn.querySelector('.upvote-count');
						if (countEl) {
							countEl.textContent = dados.curtidas;
						}
					}
				})
				.catch(function () {});
		});
	});

	/* --------------------------------------------------------- menu mobile */
	var masthead = document.querySelector('[data-masthead]');
	var menuToggle = document.querySelector('[data-menu-toggle]');
	var mobileNav = document.querySelector('[data-mobile-nav]');
	var menuBackdrop = document.querySelector('[data-menu-backdrop]');

	if (masthead && menuToggle && mobileNav) {
		var fecharMenu = function (devolverFoco) {
			masthead.classList.remove('is-menu-open');
			if (menuBackdrop) {
				menuBackdrop.classList.remove('is-open');
			}
			menuToggle.setAttribute('aria-expanded', 'false');
			menuToggle.setAttribute('aria-label', 'abrir menu');
			if (devolverFoco) {
				menuToggle.focus();
			}
		};

		var abrirMenu = function () {
			masthead.classList.add('is-menu-open');
			if (menuBackdrop) {
				menuBackdrop.classList.add('is-open');
			}
			menuToggle.setAttribute('aria-expanded', 'true');
			menuToggle.setAttribute('aria-label', 'fechar menu');
		};

		menuToggle.addEventListener('click', function () {
			if (masthead.classList.contains('is-menu-open')) {
				fecharMenu(false);
			} else {
				abrirMenu();
			}
		});

		if (menuBackdrop) {
			menuBackdrop.addEventListener('click', function () {
				fecharMenu(false);
			});
		}

		mobileNav.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				fecharMenu(false);
			});
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && masthead.classList.contains('is-menu-open')) {
				fecharMenu(true);
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth > 768 && masthead.classList.contains('is-menu-open')) {
				fecharMenu(false);
			}
		});
	}

	/* ------------------------------------------------- barra de progresso */
	var barraProgresso = document.querySelector('[data-reading-progress]');

	if (barraProgresso) {
		var pendente = false;
		var atualizarProgresso = function () {
			pendente = false;
			var total = document.documentElement.scrollHeight - window.innerHeight;
			var ratio = total > 0 ? Math.min(1, Math.max(0, window.scrollY / total)) : 0;
			barraProgresso.style.transform = 'scaleX(' + ratio.toFixed(4) + ')';
		};

		window.addEventListener(
			'scroll',
			function () {
				if (!pendente) {
					pendente = true;
					window.requestAnimationFrame(atualizarProgresso);
				}
			},
			{ passive: true }
		);
		atualizarProgresso();
	}

	/* ------------------------------------------- filtros da estante (/leituras/) */
	var raizLeituras = document.querySelector('[data-readings-root]');

	if (raizLeituras) {
		var abasLeituras = raizLeituras.querySelectorAll('[data-reading-filter]');
		var secoesLeituras = raizLeituras.querySelectorAll('[data-reading-section]');
		var campoBuscaLeituras = raizLeituras.querySelector('[data-reading-search]');
		var avisoVazioLeituras = raizLeituras.querySelector('[data-reading-empty]');
		var filtroAtivo = 'todos';

		var normalizarTexto = function (str) {
			if (!str) {
				return '';
			}
			var s = str.toLowerCase();
			if (typeof s.normalize === 'function') {
				s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
			}
			return s.trim();
		};

		var aplicarFiltrosLeituras = function () {
			var termo = campoBuscaLeituras ? normalizarTexto(campoBuscaLeituras.value) : '';
			var totalVisiveis = 0;

			secoesLeituras.forEach(function (secao) {
				var tipoSecao = secao.getAttribute('data-reading-section');
				var secaoPermitida = filtroAtivo === 'todos' || filtroAtivo === tipoSecao;

				if (!secaoPermitida) {
					secao.hidden = true;
					return;
				}

				var itens = secao.querySelectorAll('[data-reading-item]');
				var visiveisNaSecao = 0;

				itens.forEach(function (item) {
					var alvo = normalizarTexto(item.getAttribute('data-reading-query') || '');
					var bateu = termo === '' || alvo.indexOf(termo) !== -1;
					item.hidden = !bateu;
					if (bateu) {
						visiveisNaSecao++;
						totalVisiveis++;
					}
				});

				secao.hidden = visiveisNaSecao === 0;
			});

			if (avisoVazioLeituras) {
				avisoVazioLeituras.hidden = totalVisiveis > 0;
			}
		};

		abasLeituras.forEach(function (aba) {
			aba.addEventListener('click', function () {
				filtroAtivo = aba.getAttribute('data-reading-filter') || 'todos';
				abasLeituras.forEach(function (outra) {
					var ativa = outra === aba;
					outra.classList.toggle('is-active', ativa);
					outra.setAttribute('aria-selected', ativa ? 'true' : 'false');
				});
				aplicarFiltrosLeituras();
			});
		});

		if (campoBuscaLeituras) {
			campoBuscaLeituras.addEventListener('input', aplicarFiltrosLeituras);
		}
	}

})();
