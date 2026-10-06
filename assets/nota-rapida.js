/*
 * A publicacao rapida: barra de ferramentas, imagem e altura da caixa.
 *
 * O QUE ESTE SCRIPT FAZ, E O QUE ELE NAO FAZ. Ele nao salva nada, nao valida
 * nada e nao decide o que entra no banco: isso e do tratador, no servidor, onde
 * nao depende do navegador de quem escreve. Aqui so existe o que so faz sentido
 * com um dedo na tela.
 *
 * POR QUE `execCommand` E NAO `value =`. Atribuir direto no `value` apaga a pilha
 * de desfazer do navegador: quem tocar em "negrito" por engano perde o Ctrl+Z. O
 * `insertText` do `execCommand` entra na pilha como se tivesse sido digitado, e
 * continua funcionando em todos os navegadores que importam, apesar de marcado
 * como obsoleto. O caminho antigo fica como reserva.
 */
( function () {
	'use strict';

	/* Os quatro atalhos, e o que cada botao faz com a selecao. */
	var ACOES = {
		negrito: { abre: '**', fecha: '**', exemplo: 'negrito' },
		italico: { abre: '*', fecha: '*', exemplo: 'itálico' },
		riscado: { abre: '~~', fecha: '~~', exemplo: 'riscado' },
		codigo: { abre: '`', fecha: '`', exemplo: 'código' },
	};

	/* Troca o texto mantendo o desfazer. Devolve o que foi escrito. */
	function escrever( caixa, texto, selecionar ) {
		caixa.focus();

		var deixou = false;
		try {
			deixou = document.execCommand( 'insertText', false, texto );
		} catch ( e ) {
			deixou = false;
		}

		if ( ! deixou ) {
			var ini = caixa.selectionStart;
			var fim = caixa.selectionEnd;
			caixa.setRangeText( texto, ini, fim, 'end' );
		}

		if ( selecionar ) {
			var p = caixa.value.lastIndexOf( texto );
			if ( p > -1 ) {
				caixa.setSelectionRange( p, p + texto.length );
			}
		}
	}

	/* Envolve a selecao, ou abre as marcas para digitar dentro. */
	function envolver( caixa, acao ) {
		var ini = caixa.selectionStart;
		var fim = caixa.selectionEnd;
		var escolhido = caixa.value.slice( ini, fim );

		/* Com texto selecionado que ja esta formatado, o botao desfaz. */
		var marcas = ACOES[ acao ];
		var antes = caixa.value.slice( Math.max( 0, ini - marcas.abre.length ), ini );
		var depois = caixa.value.slice( fim, fim + marcas.fecha.length );

		if ( escolhido && antes === marcas.abre && depois === marcas.fecha ) {
			var alvo = caixa.value.slice( ini - marcas.abre.length, fim + marcas.fecha.length );
			caixa.setSelectionRange( ini - marcas.abre.length, fim + marcas.fecha.length );
			escrever( caixa, escolhido, false );
			return;
		}

		/*
		 * COM A CAIXA VAZIA, O BOTAO ABRE O PAR E PÕE O CURSOR NO MEIO.
		 *
		 * A PRIMEIRA VERSAO INSERIA A PALAVRA DE EXEMPLO, e a anterior a ela
		 * recusava o toque com um aviso. As duas erraram pelo mesmo motivo: tratar
		 * a caixa vazia como caso especial. No celular, tocar em negrito e DEPOIS
		 * escrever é a ordem natural — quem faz isso quer o par de marcas aberto,
		 * não um aviso nem uma palavra para apagar.
		 *
		 * O TESTE PEGOU ISSO: dois dos nove casos falharam por causa dessa decisão,
		 * e os dois eram exatamente o gesto mais comum no telefone.
		 */
		if ( escolhido ) {
			var montado = marcas.abre + escolhido + marcas.fecha;
			escrever( caixa, montado, false );

			/* deixa o texto escolhido marcado dentro das marcas */
			var p = caixa.value.lastIndexOf( montado );
			if ( p > -1 ) {
				caixa.setSelectionRange( p + marcas.abre.length, p + marcas.abre.length + escolhido.length );
			}
			return;
		}

		/* Sem seleção: abre o par e deixa o cursor entre as duas marcas. */
		var vazio = marcas.abre + marcas.fecha;
		escrever( caixa, vazio, false );

		var q = caixa.value.lastIndexOf( vazio );
		if ( q > -1 ) {
			var meio = q + marcas.abre.length;
			caixa.setSelectionRange( meio, meio );
		}
	}

	/* Link: com texto vira [texto](endereco); sem texto, [](endereco). */
	function link( caixa ) {
		var ini = caixa.selectionStart;
		var fim = caixa.selectionEnd;
		var escolhido = caixa.value.slice( ini, fim );
		var montado = '[' + escolhido + '](https://)';

		escrever( caixa, montado, false );

		/*
		 * SEM TEXTO, O CURSOR VAI PARA DENTRO DOS COLCHETES, e não para o
		 * endereço: quem tocou no botão com a caixa vazia ainda não tem o
		 * endereço, tem o rótulo do link na cabeça. Com texto selecionado, o
		 * contrário: o rótulo já está lá, e o que falta é o endereço.
		 */
		var p = caixa.value.lastIndexOf( montado );
		if ( p > -1 ) {
			if ( escolhido ) {
				var onde = p + montado.indexOf( '](' ) + 2;
				caixa.setSelectionRange( onde, onde + 'https://'.length );
			} else {
				caixa.setSelectionRange( p + 1, p + 1 );
			}
		}
	}

	/* Tira as marcas de formatacao da selecao, ou do texto todo. */
	function limpar( caixa ) {
		var ini = caixa.selectionStart;
		var fim = caixa.selectionEnd;
		var tem = fim > ini;
		var trecho = tem ? caixa.value.slice( ini, fim ) : caixa.value;
		var limpo = trecho
			.replace( /\*\*([^*\n]+?)\*\*/g, '$1' )
			/*
			 * SEM LOOKBEHIND, DE PROPOSITO. `(?<!\*)` é mais limpo, mas o Safari
			 * só entende lookbehind a partir do iOS 16.4: num iPhone anterior, a
			 * expressão lança erro de sintaxe ao ser COMPILADA, e o erro leva o
			 * arquivo inteiro junto — nem o botão de negrito funcionaria. O `[^*]`
			 * capturado e devolvido faz o mesmo trabalho sem esse risco.
			 */
			.replace( /([^*]|^)\*([^*\n]+?)\*(?!\*)/g, '$1$2' )
			.replace( /~~([^~\n]+?)~~/g, '$1' )
			.replace( /`([^`\n]+?)`/g, '$1' )
			.replace( /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '$1 $2' );

		if ( limpo === trecho ) {
			aviso( caixa, 'não havia formatação para tirar' );
			return;
		}

		if ( ! tem ) {
			caixa.select();
		}
		escrever( caixa, limpo, false );
		aviso( caixa, 'formatação removida' );
	}

	/* Uma mensagem curta na dica, que some sozinha. */
	function aviso( caixa, texto ) {
		var bloco = caixa.closest( '[data-vc-nr]' );
		var dica = bloco && bloco.querySelector( '[data-vc-nr-dica]' );

		if ( ! dica ) {
			return;
		}

		if ( ! dica.dataset.original ) {
			dica.dataset.original = dica.textContent;
		}

		dica.textContent = texto;
		dica.classList.add( 'vc-nr__dica--ativa' );

		clearTimeout( dica._timer );
		dica._timer = setTimeout( function () {
			dica.textContent = dica.dataset.original;
			dica.classList.remove( 'vc-nr__dica--ativa' );
		}, 2200 );
	}

	/* A caixa cresce com o texto, ate o limite do CSS. */
	function ajustarAltura( caixa ) {
		caixa.style.height = 'auto';
		caixa.style.height = caixa.scrollHeight + 'px';
	}

	/* Lembra a posicao do cursor para o botao agir sobre ela. */
	function ligar( bloco ) {
		var caixa = bloco.querySelector( '.vc-nr__caixa' );

		if ( ! caixa ) {
			return;
		}

		bloco.querySelectorAll( '[data-vc-nr-acao]' ).forEach( function ( botao ) {
			/*
			 * `preventDefault` no `mousedown` E O QUE FAZ O BOTAO FUNCIONAR NO
			 * CELULAR. Sem ele, tocar no botao tira o foco da caixa, a selecao se
			 * perde, e o `selectionStart` volta para o fim do texto: o negrito
			 * apareceria no lugar errado.
			 */
			botao.addEventListener( 'mousedown', function ( ev ) {
				ev.preventDefault();
			} );

			botao.addEventListener( 'click', function ( ev ) {
				ev.preventDefault();

				var acao = botao.getAttribute( 'data-vc-nr-acao' );

				/*
				 * A CAIXA VAZIA SO BARRACA O "LIMPAR". Nos outros cinco botoes ela
				 * e o caso normal: quem toca em negrito antes de escrever quer o par
				 * de marcas aberto. O "limpar" e o unico que precisa de texto, porque
				 * nao ha formatacao para tirar de um campo vazio.
				 */
				if ( ! caixa.value && 'limpar' === acao ) {
					caixa.focus();
					aviso( caixa, 'nao ha o que limpar' );
					return;
				}

				if ( 'link' === acao ) {
					link( caixa );
				} else if ( 'limpar' === acao ) {
					limpar( caixa );
				} else if ( ACOES[ acao ] ) {
					envolver( caixa, acao );
				}

				ajustarAltura( caixa );
				caixa.focus();
			} );
		} );

		caixa.addEventListener( 'input', function () {
			ajustarAltura( caixa );
		} );

		ajustarAltura( caixa );
	}

	/* O nome do arquivo escolhido, que e a unica confirmacao antes de publicar. */
	function ligarImagem() {
		var area = document.querySelector( '[data-vc-nr-imagem]' );

		if ( ! area ) {
			return;
		}

		var campo = area.querySelector( '.vc-nr__arquivo' );
		var nome = area.querySelector( '[data-vc-nr-nome]' );

		if ( ! campo || ! nome ) {
			return;
		}

		campo.addEventListener( 'change', function () {
			var arquivo = campo.files && campo.files[ 0 ];

			if ( ! arquivo ) {
				nome.textContent = '';
				area.classList.remove( 'vc-nr__imagem--tem' );
				return;
			}

			var kb = arquivo.size / 1024;
			var tamanho = kb > 1024 ? ( kb / 1024 ).toFixed( 1 ) + ' MB' : Math.round( kb ) + ' KB';

			nome.textContent = arquivo.name + ' (' + tamanho + ')';
			area.classList.add( 'vc-nr__imagem--tem' );
		} );
	}

	function iniciar() {
		document.querySelectorAll( '[data-vc-nr]' ).forEach( ligar );
		ligarImagem();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', iniciar );
	} else {
		iniciar();
	}
} )();
