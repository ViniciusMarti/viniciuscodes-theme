/**
 * Os quatro comportamentos do site que precisam de JavaScript.
 *
 * Por que só estes quatro: todos dependem de um clique, e uma
 * página estática não reage a clique. Todo o resto da interface — inclusive a
 * navegação que se recolhe em tela estreita — é feito com recursos nativos do
 * navegador (`<details>`) e com CSS.
 *
 * A restauração do tema guardado NÃO está aqui: ela precisa rodar antes da
 * primeira pintura e vive no `header.php`. Ver o comentário lá.
 *
 * Este arquivo é carregado por `wp_enqueue_script()`, no rodapé, e não escreve
 * nada no HTML.
 */
(function () {
	'use strict';

	/* ---------------------------------------------------------------- tema */
	var botaoDoTema = document.querySelector('[data-theme-toggle]');

	if (botaoDoTema) {
		var raiz = document.documentElement;

		/*
		 * O estado do interruptor precisa dizer qual tema está NO AR, e não qual
		 * o HTML supôs. Quem chega com uma escolha guardada recebeu o
		 * `data-theme` no <head>, antes da pintura; o botão, esse, nasceu com o
		 * valor neutro. Este é o primeiro ponto do carregamento em que os dois
		 * existem ao mesmo tempo, então é aqui que eles se encontram.
		 */
		var anunciarTema = function () {
			var temaAtual = raiz.dataset.theme;
			botaoDoTema.setAttribute('aria-checked', temaAtual === 'dark' ? 'true' : 'false');

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

		anunciarTema();

		botaoDoTema.addEventListener('click', function () {
			var proximo = raiz.dataset.theme === 'dark' ? 'light' : 'dark';

			raiz.dataset.theme = proximo;
			anunciarTema();

			try {
				localStorage.setItem('vco-theme', proximo);
			} catch (e) {
				/*
				 * Armazenamento indisponível — janela anônima, cookies
				 * bloqueados. A troca continua funcionando nesta visita; ela só
				 * não é lembrada na próxima.
				 */
			}
		});
	}

	/* -------------------------------------------------- menu lateral (drawer) */
	/*
	 * Dois comportamentos, um so botao:
	 *
	 *  - Telas estreitas (< 860px): o painel e um drawer sobreposto, que abre
	 *    por cima do conteudo e fecha no "x", no Esc ou ao navegar. Este e o
	 *    comportamento original e ele nao muda em nada.
	 *
	 *  - Telas largas (>= 860px): o painel e fixo, ocupa a coluna da direita e
	 *    o conteudo reserva o espaco dele (ver `global.css`). O mesmo botao
	 *    sanduiche vira um liga/desliga: recolhe e reexpande. A escolha fica
	 *    guardada em `localStorage` e e restaurada no `header.php`, antes da
	 *    primeira pintura.
	 */
	var botaoDaBarra = document.querySelector('[data-sidebar-toggle]');
	var fecharBotoes = document.querySelectorAll('[data-sidebar-close]');
	var painelDaBarra = document.querySelector('.sidebar');
	var raizDaBarra = document.documentElement;

	/*
	 * 860px precisa ser EXATAMENTE o mesmo valor do `@media` no `global.css`.
	 * Se um mudar sem o outro, o botao passa a comandar um layout que nao e o
	 * que esta na tela. A chave tem de ser a mesma do script do `header.php`.
	 */
	var consultaDesktop = window.matchMedia('(min-width: 860px)');
	var CHAVE_DA_BARRA = 'vc_sidebar_state';

	var ehDesktop = function () {
		return consultaDesktop.matches;
	};

	var guardarEstadoDaBarra = function (estado) {
		try {
			localStorage.setItem(CHAVE_DA_BARRA, estado);
		} catch (e) {
			/*
			 * Armazenamento indisponivel — janela anonima, cookies bloqueados.
			 * A escolha continua valendo nesta visita; ela so nao e lembrada na
			 * proxima.
			 */
		}
	};

	/*
	 * Reflete o estado na interface. `aria-expanded` responde a pergunta
	 * "o painel esta a vista?", que tem respostas diferentes nos dois modos:
	 * no desktop depende do atributo gravado, no mobile do drawer aberto.
	 */
	var anunciarBarra = function () {
		if (!botaoDaBarra) {
			return;
		}

		var aVista = ehDesktop()
			? raizDaBarra.getAttribute('data-sidebar') !== 'collapsed'
			: raizDaBarra.dataset.menuOpen === 'true';

		botaoDaBarra.setAttribute('aria-expanded', aVista ? 'true' : 'false');
		botaoDaBarra.setAttribute('aria-label', aVista ? 'fechar menu' : 'abrir menu');
	};

	/* ---- mobile: drawer sobreposto (comportamento original) ---------------- */

	var fecharMenu = function () {
		if (raizDaBarra.dataset.menuOpen === 'true') {
			delete raizDaBarra.dataset.menuOpen;
			anunciarBarra();
			if (botaoDaBarra) {
				botaoDaBarra.focus();
			}
		}
	};

	var abrirMenu = function () {
		raizDaBarra.dataset.menuOpen = 'true';
		anunciarBarra();
		var botaoFechar = document.querySelector('.sidebar__close');
		if (botaoFechar) {
			botaoFechar.focus();
		}
	};

	/* ---- desktop: liga/desliga com memoria --------------------------------- */

	var recolherBarra = function () {
		raizDaBarra.setAttribute('data-sidebar', 'collapsed');
		guardarEstadoDaBarra('collapsed');
		anunciarBarra();

		/*
		 * Se o foco estava dentro do painel, ele sumiria da tela junto com o
		 * elemento. Devolvemos o foco para o botao que o escondeu.
		 */
		if (
			botaoDaBarra &&
			painelDaBarra &&
			document.activeElement &&
			painelDaBarra.contains(document.activeElement)
		) {
			botaoDaBarra.focus();
		}
	};

	var expandirBarra = function () {
		raizDaBarra.removeAttribute('data-sidebar');
		guardarEstadoDaBarra('expanded');
		anunciarBarra();
	};

	if (botaoDaBarra) {
		botaoDaBarra.removeAttribute('hidden');

		botaoDaBarra.addEventListener('click', function () {
			if (ehDesktop()) {
				if (raizDaBarra.getAttribute('data-sidebar') === 'collapsed') {
					expandirBarra();
				} else {
					recolherBarra();
				}
				return;
			}

			if (raizDaBarra.dataset.menuOpen === 'true') {
				fecharMenu();
			} else {
				abrirMenu();
			}
		});
	}

	fecharBotoes.forEach(function (btn) {
		btn.addEventListener('click', function () {
			if (ehDesktop()) {
				recolherBarra();
			} else {
				fecharMenu();
			}
		});
	});

	/*
	 * O Esc so fecha o drawer do mobile. No desktop a sidebar e permanente,
	 * entao uma tecla acidental nao pode faze-la desaparecer.
	 */
	window.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && raizDaBarra.dataset.menuOpen === 'true') {
			fecharMenu();
		}
	});

	/*
	 * Ao cruzar o breakpoint os dois modos tem estados que nao fazem sentido no
	 * outro: um drawer "aberto" viraria uma sidebar fixa fantasma, e um
	 * `data-menu-open` preso deixaria o painel sobreposto no desktop. Zeramos o
	 * estado do drawer e reanunciamos o que ficou valendo.
	 *
	 * O `data-sidebar` continua intacto de proposito: quem recolheu no desktop
	 * reencontra o painel recolhido ao voltar para o desktop.
	 */
	var aoTrocarDeModo = function () {
		delete raizDaBarra.dataset.menuOpen;
		anunciarBarra();
	};

	if (consultaDesktop.addEventListener) {
		consultaDesktop.addEventListener('change', aoTrocarDeModo);
	} else if (consultaDesktop.addListener) {
		consultaDesktop.addListener(aoTrocarDeModo); /* Safari antigo */
	}

	anunciarBarra();

	/* ----------------------------------------------------- voltar ao topo */
	var botaoDoTopo = document.querySelector('[data-to-top]');

	if (botaoDoTopo) {
		/*
		 * O botão aparece depois de o leitor passar da primeira tela. O CSS o
		 * deixa escondido por padrão justamente para que ninguém veja, sem
		 * JavaScript, um controle que não funcionaria.
		 */
		var limite = function () {
			return window.innerHeight * 0.9;
		};

		var sincronizar = function () {
			if (window.scrollY > limite()) {
				botaoDoTopo.setAttribute('data-visible', '');
			} else {
				botaoDoTopo.removeAttribute('data-visible');
			}
		};

		sincronizar();
		window.addEventListener('scroll', sincronizar, { passive: true });
		window.addEventListener('resize', sincronizar, { passive: true });

		botaoDoTopo.addEventListener('click', function () {
			/*
			 * Rolar suavemente também é movimento. Quem pediu menos movimento ao
			 * sistema recebe o salto direto.
			 */
			var menosMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

			window.scrollTo({ top: 0, behavior: menosMovimento ? 'auto' : 'smooth' });
		});
	}
	/* --------------------------------------------------------- compartilhar */
	/*
	 * Três caminhos para mandar o texto para alguém: a folha de compartilhamento
	 * do sistema, o link do WhatsApp (que já vem pronto do PHP, sem depender
	 * daqui) e o copiar link.
	 *
	 * Os dois que dependem deste arquivo começam `hidden` no HTML e só aparecem
	 * agora: sem JavaScript, ninguém vê um botão que não faria nada.
	 *
	 * Há um único lugar onde o retorno aparece — a região `status`. Trocar o
	 * texto do próprio botão faria quem usa leitor de tela ouvir a confirmação
	 * duas vezes.
	 */
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
					/* Fechar a folha sem escolher nada é desistência, não falha. */
					if (erro && erro.name !== 'AbortError') {
						avisar('não foi possível compartilhar');
					}
				});
			});
		}

		if (botaoCopiar) {
			botaoCopiar.hidden = false;
			botaoCopiar.addEventListener('click', function () {
				/*
				 * A área de transferência só existe em contexto seguro (https ou
				 * localhost). Sem ela, o botão seria uma promessa falsa.
				 */
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

	/* ----------------------------------------------------------- curtir/gostar */
	var botoesCurtir = document.querySelectorAll('[data-post-like]');

	botoesCurtir.forEach(function (btn) {
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
				headers: {
					'Content-Type': 'application/json'
				}
			})
				.then(function (res) {
					return res.json();
				})
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
		});
	});

	/* ------------------------------------------- barra de progresso de leitura */
	var barraLeitura = document.getElementById('reading-progress-bar');
	if (barraLeitura) {
		var atualizarBarra = function () {
			var alturaTotal = document.documentElement.scrollHeight - window.innerHeight;
			if (alturaTotal > 120) {
				var porcentagem = (window.scrollY / alturaTotal) * 100;
				barraLeitura.style.width = Math.min(100, Math.max(0, porcentagem)) + '%';
			} else {
				barraLeitura.style.width = '0%';
			}
		};

		window.addEventListener('scroll', atualizarBarra, { passive: true });
		window.addEventListener('resize', atualizarBarra, { passive: true });
		atualizarBarra();
	}

	/* ----------------------- easter egg: chuva de girassóis em neurodivergência */
	function inicializarGirassois() {
		var containers = document.querySelectorAll('.prose');
		if (!containers.length) return;

		var regexNeuro = /\b(autismo|autista|autistas|audhd|tdah)\b/gi;

		containers.forEach(function (container) {
			var walker = document.createTreeWalker(
				container,
				NodeFilter.SHOW_TEXT,
				{
					acceptNode: function (node) {
						var parent = node.parentElement;
						if (!parent) return NodeFilter.FILTER_REJECT;
						var tag = parent.tagName.toLowerCase();
						if (tag === 'script' || tag === 'style' || tag === 'textarea' || parent.closest('.sunflower-rain-word')) {
							return NodeFilter.FILTER_REJECT;
						}
						return regexNeuro.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
					}
				},
				false
			);

			var nosParaTrocar = [];
			while (walker.nextNode()) {
				nosParaTrocar.push(walker.currentNode);
			}

			nosParaTrocar.forEach(function (node) {
				var fragmento = document.createDocumentFragment();
				var texto = node.nodeValue;
				var ultimoIndex = 0;
				regexNeuro.lastIndex = 0;
				var match;

				while ((match = regexNeuro.exec(texto)) !== null) {
					var antes = texto.substring(ultimoIndex, match.index);
					if (antes) {
						fragmento.appendChild(document.createTextNode(antes));
					}

					var span = document.createElement('span');
					span.className = 'sunflower-rain-word';
					span.textContent = match[0];

					var shower = document.createElement('span');
					shower.className = 'sunflower-rain-shower';
					shower.setAttribute('aria-hidden', 'true');
					shower.innerHTML = '<span class="sf-drop sf-d1">🌻</span>' +
						'<span class="sf-drop sf-d2">🌻</span>' +
						'<span class="sf-drop sf-d3">🌻</span>' +
						'<span class="sf-drop sf-d4">🌻</span>' +
						'<span class="sf-drop sf-d5">🌻</span>';

					span.appendChild(shower);
					fragmento.appendChild(span);
					ultimoIndex = regexNeuro.lastIndex;
				}

				var depois = texto.substring(ultimoIndex);
				if (depois) {
					fragmento.appendChild(document.createTextNode(depois));
				}

				if (node.parentNode) {
					node.parentNode.replaceChild(fragmento, node);
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', inicializarGirassois);
	} else {
		inicializarGirassois();
	}
})();


