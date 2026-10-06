/**
 * O painel sem agendamento — o lado do navegador.
 *
 * A trava de verdade está no `functions.php` (o filtro `wp_insert_post_data`),
 * porque é lá que TODA gravação passa: edição rápida, edição em massa, API REST
 * e qualquer script futuro. Este arquivo cuida do que o servidor não alcança: a
 * TELA. Sem ele, o editor continuaria trocando o rótulo do botão de "Publicar"
 * para "Agendar" assim que alguém digitasse uma data à frente — um convite para
 * uma coisa que não acontece mais.
 *
 * SEM JQUERY de propósito. O painel carrega o jQuery, mas nada aqui precisa
 * dele, e o tema não usa jQuery em lugar nenhum do site.
 */
( function () {
	'use strict';

	var wp = window.wp;

	/**
	 * O agora, no formato que o editor de blocos usa na data: hora local.
	 *
	 * É o mesmo formato gravado no campo, `AAAA-MM-DDTHH:MM:SS`, e é por isso
	 * que as comparações deste arquivo podem ser feitas como texto: nesse
	 * formato, ordem alfabética e ordem cronológica são a mesma coisa.
	 *
	 * @return {string} O instante atual, sem fuso.
	 */
	function agoraLocal() {
		var d = new Date();
		var dois = function ( n ) {
			return ( n < 10 ? '0' : '' ) + n;
		};

		return (
			d.getFullYear() +
			'-' + dois( d.getMonth() + 1 ) +
			'-' + dois( d.getDate() ) +
			'T' + dois( d.getHours() ) +
			':' + dois( d.getMinutes() ) +
			':' + dois( d.getSeconds() )
		);
	}

	/**
	 * Devolve para agora qualquer data que apareça no futuro.
	 *
	 * A CORREÇÃO É NO ESTADO DO EDITOR, E NÃO NO CAMPO. O campo de data é
	 * controlado pelo React: escrever nele por fora mudaria o que se vê sem
	 * mudar o que se grava — o rótulo continuaria dizendo "Agendar" e o clique
	 * continuaria pedindo agendamento. `editPost` muda o estado, e aí a tela
	 * inteira concorda, inclusive o rótulo do botão.
	 */
	function vigiarData() {
		if ( ! wp || ! wp.data || ! wp.data.subscribe || ! wp.data.dispatch ) {
			return;
		}

		var aplicando = false;
		var avisado = false;

		wp.data.subscribe( function () {
			if ( aplicando ) {
				return;
			}

			var editor = wp.data.select( 'core/editor' );

			if ( ! editor || ! editor.getEditedPostAttribute ) {
				return;
			}

			var data = editor.getEditedPostAttribute( 'date' );

			/*
			 * Sem data, o post é "publicar agora" — o WordPress carimba o
			 * instante do clique. Não há nada para corrigir, e é o caminho
			 * normal de quem só escreve e publica.
			 */
			if ( ! data || typeof data !== 'string' ) {
				return;
			}

			var status = editor.getEditedPostAttribute( 'status' );

			if ( data <= agoraLocal() && 'future' !== status ) {
				return;
			}

			/*
			 * Volta ao status que o post já tinha salvo: um texto publicado
			 * volta a publicado, um rascunho volta a rascunho. Sem isto, abrir
			 * um texto publicado e mexer na data o rebaixaria a rascunho.
			 */
			var salvo = editor.getCurrentPost ? editor.getCurrentPost().status : 'draft';
			var destino = 'publish' === salvo ? 'publish' : 'draft';

			aplicando = true;
			wp.data.dispatch( 'core/editor' ).editPost( {
				date: agoraLocal(),
				status: destino,
			} );
			aplicando = false;

			/*
			 * Explica UMA vez. A data voltar sozinha sem uma palavra parece
			 * defeito; com a frase, vira a regra do site. E a segunda metade da
			 * frase importa tanto quanto a primeira: quem quiser datar um texto
			 * no passado continua podendo.
			 */
			if ( ! avisado && wp.data.dispatch( 'core/notices' ) ) {
				avisado = true;
				wp.data.dispatch( 'core/notices' ).createNotice(
					'info',
					'O agendamento está desligado neste site: a data voltou para agora. Para datar o texto no passado, escolha uma data anterior a hoje.',
					{ id: 'vc-sem-agendamento', isDismissible: true }
				);
			}
		} );
	}

	/**
	 * Tira a opção "Agendado" dos seletores de status.
	 *
	 * São dois, na mesma tela: o da edição rápida e o da edição em massa. Ambos
	 * aparecem com o nome `_status`.
	 */
	function limparSeletores() {
		var seletores = document.querySelectorAll( 'select[name="_status"]' );

		for ( var i = 0; i < seletores.length; i++ ) {
			var opcao = seletores[ i ].querySelector( 'option[value="future"]' );

			if ( opcao && opcao.parentNode ) {
				opcao.parentNode.removeChild( opcao );
			}
		}
	}

	function iniciar() {
		vigiarData();
		limparSeletores();

		/*
		 * A edição rápida monta o formulário DEPOIS do clique, então o seletor
		 * dela ainda não existe quando a página carrega. O `setTimeout` de zero
		 * é o que coloca a limpeza depois de o WordPress copiar o formulário
		 * para a linha — no mesmo tick, ele ainda não existe.
		 */
		document.addEventListener( 'click', function ( evento ) {
			var alvo = evento.target;

			if ( alvo && alvo.closest && alvo.closest( '.editinline' ) ) {
				window.setTimeout( limparSeletores, 0 );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', iniciar );
	} else {
		iniciar();
	}
} )();
