/*
 * O botão de copiar da página de feeds.
 *
 * ELE NASCE ESCONDIDO, E QUEM O MOSTRA É ESTE SCRIPT.
 *
 * Sem JavaScript o botão não faria nada, e um botão que não faz nada é pior do
 * que botão nenhum: quem clicasse não teria nem a resposta do clique nem a
 * informação de que ali não havia nada. Por isso o HTML traz `hidden` e é aqui
 * que ele sai — assim o botão só existe para quem tem como usá-lo, e o endereço
 * escrito na linha ao lado continua servindo a todo mundo.
 *
 * `navigator.clipboard` só existe em contexto seguro (HTTPS, ou localhost). No
 * site publicado isso vale sempre; em qualquer lugar onde não valha, o script
 * para na primeira linha e o botão fica escondido, que é o comportamento certo.
 */
( function () {
	var botoes = document.querySelectorAll( '.feed-copiar' );

	if ( ! botoes.length || ! navigator.clipboard || ! navigator.clipboard.writeText ) {
		return;
	}

	Array.prototype.forEach.call( botoes, function ( botao ) {
		var texto = botao.querySelector( '.feed-copiar__texto' );
		var rotulo = texto ? texto.textContent : '';
		var voltar = null;

		botao.hidden = false;

		/*
		 * Uma função só para mexer no rótulo e voltar atrás. O `clearTimeout`
		 * importa quando a pessoa clica duas vezes seguidas: sem ele, o relógio
		 * do primeiro clique apagaria a confirmação do segundo no meio.
		 */
		function avisar( mensagem ) {
			if ( texto ) {
				texto.textContent = mensagem;
			}

			botao.classList.add( 'feed-copiar--feito' );

			window.clearTimeout( voltar );

			voltar = window.setTimeout( function () {
				botao.classList.remove( 'feed-copiar--feito' );

				if ( texto ) {
					texto.textContent = rotulo;
				}
			}, 2000 );
		}

		botao.addEventListener( 'click', function () {
			navigator.clipboard.writeText( botao.getAttribute( 'data-endereco' ) || '' ).then(
				function () {
					avisar( 'copiado' );
				},
				function () {
					/*
					 * A cópia pode ser recusada (permissão negada, documento sem
					 * foco). O endereço continua escrito ao lado, então o aviso é
					 * curto e honesto em vez de silencioso.
					 */
					avisar( 'copie à mão' );
				}
			);
		} );
	} );
}() );
