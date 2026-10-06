/*
  A CAIXA DE LUZ DAS POLAROIDES.

  POR QUE UM `<dialog>` E NÃO UMA CAIXA DESENHADA À MÃO. O elemento nativo já
  entrega as três coisas difíceis de acertar: o `Esc` fecha, o foco fica preso
  dentro enquanto ela está aberta, e ao fechar o foco VOLTA para o link que a
  abriu. Refazer isso em `div` seria escrever um tratador de teclado, um de foco
  e um de pilha de foco, e errar um dos três é o normal.

  O LINK CONTINUA SENDO UM LINK. Sem JavaScript, ou num navegador sem `showModal`,
  o `<a href>` da polaroide abre a foto em outra aba como antes: o script melhora
  o que já funcionava, e não é ele que faz funcionar.

  A FOTO SÓ ENTRA NO `<img>` NA HORA DO CLIQUE, e sai quando a caixa fecha. Com o
  endereço fixo no HTML, as seis fotos grandes de `/viagens/` seriam baixadas por
  quem nunca clicasse em nenhuma.

  CLICAR FORA DA FOTO FECHA, e é a área toda que vale: o fundo escuro, a moldura
  branca e a legenda. Só a foto não fecha, porque clicar nela é olhar mais de
  perto.
*/
( function () {
	'use strict';

	var caixa = document.getElementById( 'tempo-caixa' );

	/*
	 * Sem o `<dialog>` não há o que fazer, e o `return` é o caminho: o link da
	 * polaroide continua no lugar, abrindo a foto em outra aba.
	 */
	if ( ! caixa || typeof caixa.showModal !== 'function' ) {
		return;
	}

	var foto    = caixa.querySelector( '.tempo__caixa-foto' );
	var legenda = caixa.querySelector( '.tempo__caixa-legenda' );

	document.addEventListener( 'click', function ( evento ) {
		/*
		 * O closest é conferido antes de ser chamado, e não é paranoia: o alvo de um
		 * clique pode ser o próprio document, que não é elemento e não tem esse método.
		 */
		var alvo = evento.target;
		var gatilho = ( alvo && alvo.closest ) ? alvo.closest( '[data-vc-foto]' ) : null;

		if ( ! gatilho ) {
			return;
		}

		evento.preventDefault();

		var endereco = gatilho.getAttribute( 'data-vc-foto' );
		var texto    = gatilho.getAttribute( 'data-vc-legenda' ) || '';

		foto.setAttribute( 'src', endereco );
		foto.setAttribute( 'alt', texto );
		legenda.textContent = texto;
		legenda.hidden      = ( '' === texto );

		caixa.showModal();
	} );

	caixa.addEventListener( 'click', function ( evento ) {
		if ( evento.target === foto ) {
			return;
		}

		caixa.close();
	} );

	caixa.addEventListener( 'close', function () {
		foto.removeAttribute( 'src' );
		foto.setAttribute( 'alt', '' );
		legenda.textContent = '';
	} );
}() );
