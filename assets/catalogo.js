/*
 * O catálogo do acervo: filtro que se envia sozinho.
 *
 * O QUE ESTE ARQUIVO FAZ, E O QUE ELE NÃO FAZ. Ele não filtra nada. Quem filtra é
 * o servidor, a partir do endereço — o JavaScript só evita que a pessoa precise
 * apertar "filtrar" depois de escolher. É a diferença entre uma página que depende
 * de script para funcionar e uma que funciona melhor com ele.
 *
 * POR QUE NÃO FILTRAR NO CLIENTE. Seria mais rápido ao clicar e pior em tudo o
 * resto: os 218 itens iriam inteiros para o navegador em toda visita — a página
 * antiga pesa 220 KB por causa disso —, o resultado não teria endereço para
 * compartilhar, e o buscador veria uma lista só, sem os recortes. Combinar quatro
 * filtros escondendo linhas em CSS também não funciona: CSS não sabe cruzar duas
 * condições, e é exatamente isso que "nota 4 ou mais, em 2024" exige.
 *
 * O ENVIO É ADIADO EM 250ms. Sem isso, cada troca de campo viraria uma navegação,
 * e quem procura um autor digitaria seis letras e dispararia seis recarregamentos.
 * O atraso é curto o bastante para parecer imediato e longo o bastante para
 * agrupar a digitação.
 */
( function () {
	'use strict';

	var form = document.querySelector( '[data-filtros]' );

	if ( ! form ) {
		return;
	}

	/* Marca que há script. É o que esconde o botão de aplicar, pelo CSS. */
	form.setAttribute( 'data-js', '1' );

	var relogio = null;

	function enviar() {
		/* A rolagem volta para a barra de filtros, e não para o topo da página:
		   é onde a pessoa está olhando quando troca um filtro. */
		form.submit();
	}

	function agendar( imediato ) {
		clearTimeout( relogio );

		if ( imediato ) {
			enviar();
			return;
		}

		relogio = setTimeout( enviar, 250 );
	}

	Array.prototype.forEach.call( form.querySelectorAll( 'select' ), function ( campo ) {
		campo.addEventListener( 'change', function () {
			agendar( true );
		} );
	} );

	Array.prototype.forEach.call( form.querySelectorAll( 'input[type="search"]' ), function ( campo ) {
		campo.addEventListener( 'input', function () {
			/* Apagar o que estava escrito volta à lista inteira, e isso não pode
			   esperar o atraso: é a ação de quem desistiu da busca. */
			agendar( '' === campo.value );
		} );

		/* `Enter` no campo de busca envia na hora, sem esperar o atraso. */
		campo.addEventListener( 'keydown', function ( ev ) {
			if ( 'Enter' === ev.key ) {
				ev.preventDefault();
				clearTimeout( relogio );
				enviar();
			}
		} );
	} );
} )();
